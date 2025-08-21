<?php
require 'pdo.php'; // Conexión a la base de datos

session_start(); // 🔹 Iniciar sesión para obtener `documento_operador`

header('Content-Type: application/json');


error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

/* file_put_contents("debug_venta.json", json_encode($data, JSON_PRETTY_PRINT));
echo json_encode(['success' => '✅ Datos recibidos correctamente (debug)']);
exit;
 */


$documento_cliente = $data['documento_cliente'] ?? null;

if (!$documento_cliente) {
    echo json_encode(['error' => 'Debes escribir el documento del cliente']);
    exit;
}


if (!$data) {
    echo json_encode(['error' => 'Error al recibir JSON']);
    exit;
}

if (!isset($data['productos']) || !isset($data['metodo_pago'])) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

// 🔹 Obtener `documento_operador` desde la sesión
if (!isset($_SESSION['documento'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// 🔹 Validar que el rol sea 'administrador' o 'vendedor'
$rolesPermitidos = ['administrador', 'vendedor', 'superadmin'];
if (!in_array($_SESSION['rol'], $rolesPermitidos)) {
    echo json_encode(['error' => 'Acceso denegado. Solo administradores y vendedores pueden registrar ventas.']);
    exit;
}

try {
    
    $pdo->beginTransaction();

    $documento_cliente = $data['documento_cliente'] ?? null;
    $nombre_cliente = $data['nombre_cliente'] ?? null;
    $apellido_cliente = $data['apellido_cliente'] ?? null;
    $correo_cliente = $data['correo_cliente'] ?? null;

    // Verificar si el cliente ya existe (evita duplicados)
    $stmtVerificar = $pdo->prepare("SELECT 1 FROM clientes WHERE documento = ?");
    $stmtVerificar->execute([$documento_cliente]);

    if ($stmtVerificar->rowCount() === 0 && $documento_cliente !== '22222222222') {
        if ($nombre_cliente && $apellido_cliente && $correo_cliente) {
            // Insertar cliente nuevo
            $documento_operador = $_SESSION['documento']; // ya lo tienes desde la sesión
            $estado = 'activo';

            $stmtInsertCliente = $pdo->prepare("INSERT INTO clientes (
                documento, nombre, apellido, correo, tiempo_registro, documento_operador, estado
            ) VALUES (
                ?, ?, ?, ?, NOW(), ?, ?
            )");

            $stmtInsertCliente->execute([
                $documento_cliente,
                $nombre_cliente,
                $apellido_cliente,
                $correo_cliente,
                $documento_operador,
                $estado
            ]);

        } else {
            echo json_encode(['error' => 'Faltan datos para registrar el nuevo cliente.']);
            exit;
        }
    }

    function generarNumCredito($pdo) {
        $stmt = $pdo->query("SELECT MAX(num_credito) AS ultimo FROM creditos");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($resultado && $resultado['ultimo']) {
            $ultimo = intval(substr($resultado['ultimo'], 2)); // CR001 → 1
            $nuevo = $ultimo + 1;
        } else {
            $nuevo = 1;
        }
    
        return 'CR' . str_pad($nuevo, 3, '0', STR_PAD_LEFT);
    }
    

    $productos = $data['productos'];
    $metodo_pago = $data['metodo_pago'];
    $documento_operador = $_SESSION['documento']; // ✅ Tomar documento del usuario en sesión
    $recibido = isset($data['recibido']) ? $data['recibido'] : null;
    $cambio = isset($data['cambio']) ? $data['cambio'] : null;
    $estado_venta = 'activo'; // ✅ Enviar estado = 'activo'

     // 🔹 Calcular total sin descuento
     $total_sin_descuento = array_reduce($productos, function ($total, $p) {
        return $total + ($p['cantidad'] * $p['precio']);
    }, 0);

    // 🔹 Aplicar descuento si existe
    $descuento_valor = isset($data['descuento_en_pesos']) ? floatval($data['descuento_en_pesos']) : 0; // mal nombre pero usémoslo igual
    $total_venta = $total_sin_descuento - $descuento_valor;


    // 🔹 Insertar en la tabla `ventas`
    $stmt = $pdo->prepare("INSERT INTO ventas (descripcion, total_venta, metodo_pago, documento, tiempo_registro, documento_operador, estado, recibido, cambio) 
                            VALUES ('Venta de productos', ?, ?, ?, NOW(), ?, ?, ?, ?)");
    $stmt->execute([$total_venta, $metodo_pago, $documento_cliente, $documento_operador, $estado_venta, $recibido, $cambio]);
    $cont_venta = $pdo->lastInsertId();

    // 🔹 Insertar productos en `detalle_venta`
    $stmtDetalle = $pdo->prepare("INSERT INTO detalle_venta (cont_venta, cont_producto, descripcion, cantidad_productos, sub_total, precio_unitario, descuento_en_pesos, tiempo_registro, documento_operador, estado) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, 'activo')");

    foreach ($productos as $producto) {
        if (!isset($producto['cont_producto'])) {
            throw new Exception("Falta `cont_producto` en producto: " . json_encode($producto));
        }
        
        $stmtDetalle->execute([
            $cont_venta,
            $producto['cont_producto'],
            $producto['nombre'], // ✅ Guardar 'nombre' como descripción
            $producto['cantidad'],
            $producto['cantidad'] * $producto['precio'], // ✅ subtotal
            $producto['precio'],
            $data['descuento_en_pesos'],
            $documento_operador
        ]);

        // 🔹 Actualizar stock en la tabla `productos`
        $stmtUpdateStock = $pdo->prepare("UPDATE productos SET cantidad = cantidad - ? WHERE cont_producto = ?");
        $stmtUpdateStock->execute([$producto['cantidad'], $producto['cont_producto']]);
    }

    if (
        $metodo_pago === 'credito' &&
        isset($data['plazo_credito']) &&
        isset($data['observaciones']) &&
        $documento_cliente !== '22222222222'
    ) {
        $num_credito = generarNumCredito($pdo);
        $plazo = intval($data['plazo_credito']);
        $obs = trim($data['observaciones']);
        $valor_credito = $total_venta;
        $saldo = $valor_credito;
        $documento = $documento_cliente;
        $estado = 'activo';
    
        // Obtener último cont_detalle_venta de esta venta
        $stmtDet = $pdo->prepare("SELECT cont_detalle_venta FROM detalle_venta WHERE cont_venta = ? ORDER BY cont_detalle_venta DESC LIMIT 1");
        $stmtDet->execute([$cont_venta]);
        $cont_detalle_venta = $stmtDet->fetchColumn();
    
        $stmtCredito = $pdo->prepare("INSERT INTO creditos (
            num_credito, cont_detalle_venta, documento, valor_credito, abonos, saldo,
            plazo_credito, observaciones, tiempo_registro, documento_operador, estado
        ) VALUES (
            :num_credito, :cont_detalle_venta, :documento, :valor_credito, 0, :saldo,
            :plazo, :observaciones, NOW(), :documento_operador, :estado
        )");
    
        $stmtCredito->execute([
            ':num_credito' => $num_credito,
            ':cont_detalle_venta' => $cont_detalle_venta,
            ':documento' => $documento,
            ':valor_credito' => $valor_credito,
            ':saldo' => $saldo,
            ':plazo' => $plazo,
            ':observaciones' => $obs,
            ':documento_operador' => $documento_operador,
            ':estado' => $estado
        ]);
    }
    

    $pdo->commit();
    
    echo json_encode(['success' => 'Venta registrada con éxito', 'cont_venta' => $cont_venta]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Error en la transacción: ' . $e->getMessage()]);
}
?>
