<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // evita que los warnings salgan en la respuesta
ini_set('log_errors', 1);     // mándalos al log
header('Content-Type: application/json');


require 'pdo.php';
require_once __DIR__ . '/includes/factus_service.php';

session_start();

// -------- Validaciones básicas --------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']); exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) { echo json_encode(['error' => 'Error al recibir JSON']); exit; }

$documento_cliente = $data['documento_cliente'] ?? null;
if (!$documento_cliente) { echo json_encode(['error' => 'Debes escribir el documento del cliente']); exit; }

if (!isset($data['productos']) || !is_array($data['productos']) || count($data['productos']) === 0) {
    echo json_encode(['error' => 'No hay productos en la venta']); exit;
}

if (!isset($data['metodo_pago'])) {
    echo json_encode(['error' => 'Debe indicar el método de pago']); exit;
}

if (!isset($_SESSION['documento'])) {
    echo json_encode(['error' => 'Usuario no autenticado']); exit;
}

$rolesPermitidos = ['administrador', 'vendedor', 'superadmin'];
if (!in_array($_SESSION['rol'], $rolesPermitidos)) {
    echo json_encode(['error' => 'Acceso denegado.']); exit;
}

try {
    $pdo->beginTransaction();

    // =====================================================
    // CLIENTE
    // =====================================================
    if ($documento_cliente === '22222222222') {
        $stmtCF = $pdo->prepare("SELECT 1 FROM clientes WHERE documento = ? LIMIT 1");
        $stmtCF->execute(['22222222222']);
        if (!$stmtCF->fetchColumn()) {
            $placeholderCelular   = '0000000000';
            $placeholderCorreo    = 'consumidorfinal@local';
            $placeholderCiudad    = 'N/A';
            $placeholderDireccion = 'N/A';
            $placeholderTributo   = '22';
            $placeholderDepto     = 'N/A';

            $stmtInsCF = $pdo->prepare(
                "INSERT INTO clientes
                (tipo_persona, tipo_documento, documento, nombre, apellido, fecha_nacimiento,
                 celular, telefono, correo, departamento, ciudad, direccion, nom_comercial,
                 tributo_id, tiempo_registro, documento_operador, estado)
                 VALUES
                ('natural', 'cedula de ciudadania', '22222222222', 'Consumidor', 'Final', NULL,
                 :celular, NULL, :correo, :departamento, :ciudad, :direccion, NULL,
                 :tributo_id, NOW(), :documento_operador, 'activo')"
            );
            $stmtInsCF->execute([
                ':celular'            => $placeholderCelular,
                ':correo'             => $placeholderCorreo,
                ':departamento'       => $placeholderDepto,
                ':ciudad'             => $placeholderCiudad,
                ':direccion'          => $placeholderDireccion,
                ':tributo_id'         => $placeholderTributo,
                ':documento_operador' => $_SESSION['documento'],
            ]);
        }
    } else {
        $stmtVer = $pdo->prepare("SELECT 1 FROM clientes WHERE documento = :d LIMIT 1");
        $stmtVer->execute([':d' => $documento_cliente]);
        $existe = (bool)$stmtVer->fetchColumn();

        if (!$existe) {
            $cli = [
                'tipo_persona'     => $data['tipo_persona_cliente']     ?? null,
                'tipo_documento'   => $data['tipo_documento_cliente']   ?? null,
                'documento'        => $documento_cliente,
                'nombre'           => $data['nombre_cliente']           ?? null,
                'apellido'         => $data['apellido_cliente']         ?? null,
                'fecha_nacimiento' => $data['fecha_nacimiento_cliente'] ?? null,
                'celular'          => $data['celular_cliente']          ?? null,
                'telefono'         => $data['telefono_cliente']         ?? null,
                'correo'           => $data['correo_cliente']           ?? null,
                'departamento'     => $data['departamento_cliente']     ?? null,
                'ciudad'           => $data['ciudad_cliente']           ?? null,
                'direccion'        => $data['direccion_cliente']        ?? null,
                'nom_comercial'    => $data['nom_comercial_cliente']    ?? null,
                'tributo_id'       => $data['tributo_id_cliente']       ?? null
            ];
            if (($cli['tipo_persona'] ?? '') === 'juridica') {
                $cli['fecha_nacimiento'] = null;
            }

            $faltantes = [];
            foreach ([
                'tipo_persona','tipo_documento','documento','nombre','apellido',
                'celular','correo','departamento','ciudad','direccion','tributo_id'
            ] as $k) {
                if (empty($cli[$k])) $faltantes[] = $k;
            }
            if ($faltantes) {
                echo json_encode(['error' => 'Faltan campos para crear el cliente: ' . implode(', ', $faltantes)]);
                $pdo->rollBack(); exit;
            }

            $sqlIns = "INSERT INTO clientes
              (tipo_persona, tipo_documento, documento, nombre, apellido, fecha_nacimiento, celular, telefono, correo, departamento, ciudad, direccion, nom_comercial, tributo_id, tiempo_registro, documento_operador, estado)
              VALUES
              (:tipo_persona, :tipo_documento, :documento, :nombre, :apellido, :fecha_nacimiento, :celular, :telefono, :correo, :departamento, :ciudad, :direccion, :nom_comercial, :tributo_id, :tiempo_registro, :documento_operador, 'activo')";
            $stmtIns = $pdo->prepare($sqlIns);
            $stmtIns->execute([
                ':tipo_persona'      => $cli['tipo_persona'],
                ':tipo_documento'    => $cli['tipo_documento'],
                ':documento'         => $cli['documento'],
                ':nombre'            => $cli['nombre'],
                ':apellido'          => $cli['apellido'],
                ':fecha_nacimiento'  => $cli['fecha_nacimiento'] ?: null,
                ':celular'           => $cli['celular'],
                ':telefono'          => $cli['telefono'] ?: null,
                ':correo'            => $cli['correo'],
                ':departamento'      => $cli['departamento'],
                ':ciudad'            => $cli['ciudad'],
                ':direccion'         => $cli['direccion'],
                ':nom_comercial'     => $cli['nom_comercial'] ?: null,
                ':tributo_id'        => $cli['tributo_id'],
                ':tiempo_registro'   => date('Y-m-d H:i:s'),
                ':documento_operador'=> $_SESSION['documento'],
            ]);
        }
    }

    // =====================================================
    // PAGOS (opcionalmente mixtos)
    // =====================================================
    $pagos = $data['pagos'] ?? [];
    $usaPagosMixtos = is_array($pagos) && count($pagos) > 0;

    $totalPagos      = 0.0;
    $totalEfectivo   = 0.0;
    $pagosNormalizados = [];

    if ($usaPagosMixtos) {
        foreach ($pagos as $pago) {
            $metodo = isset($pago['metodo']) ? trim($pago['metodo']) : '';
            $monto  = isset($pago['monto']) ? (float)$pago['monto'] : 0;
            $ref    = isset($pago['referencia']) ? trim($pago['referencia']) : null;
            if ($metodo === '' || $monto <= 0) continue;

            $pagosNormalizados[] = ['metodo' => $metodo, 'monto' => $monto, 'referencia' => $ref];
            $totalPagos += $monto;
            if (strtolower($metodo) === 'efectivo') $totalEfectivo += $monto;
        }
    }

    $metodo_pago_front   = $data['metodo_pago']; // 'efectivo', 'nequi', 'tarjeta', 'credito', etc.
    $metodo_pago_guardar = $usaPagosMixtos ? 'mixto' : $metodo_pago_front;

    // =====================================================
    // TOTALES Y DESCUENTO
    // =====================================================
    $productos          = $data['productos'];
    $documento_operador = $_SESSION['documento'];
    $estado_venta       = 'activo';

    $total_sin_descuento = array_reduce($productos, function ($t, $p) {
        return $t + ((int)round($p['cantidad'])) * ((int)round($p['precio']));
    }, 0);

    $descuento_solicitado = isset($data['descuento_en_pesos']) ? (int)round($data['descuento_en_pesos']) : 0;
    if ($descuento_solicitado < 0) $descuento_solicitado = 0;
    if ($descuento_solicitado > $total_sin_descuento) $descuento_solicitado = $total_sin_descuento;

    $ids = array_values(array_unique(array_map(fn($p) => (int)$p['cont_producto'], $productos)));
    $costos = [];
    if ($ids) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmtCost = $pdo->prepare("SELECT cont_producto, precio_compra FROM productos WHERE cont_producto IN ($in)");
        $stmtCost->execute($ids);
        while ($row = $stmtCost->fetch(PDO::FETCH_ASSOC)) {
            $costos[(int)$row['cont_producto']] = (float)$row['precio_compra'];
        }
    }

    $margenes = [];
    $margen_total = 0;
    foreach ($productos as $idx => $p) {
        $cant  = (int)round($p['cantidad']);
        $pv    = (int)round($p['precio']);
        $costo = (int)round($costos[(int)$p['cont_producto']] ?? 0);
        $mu    = max(0, $pv - $costo);
        $mi    = $mu * $cant;
        $margenes[$idx] = $mi;
        $margen_total  += $mi;
    }

    $descuento_aplicado = min($descuento_solicitado, $margen_total);
    $total_venta        = $total_sin_descuento - $descuento_aplicado;

    if ($usaPagosMixtos) {
        if ($totalPagos + 0.00001 < $total_venta) {
            throw new Exception("Los pagos no cubren el total de la venta. Total: $total_venta, Pagos: $totalPagos");
        }
        $pagosNoEfectivo = $totalPagos - $totalEfectivo;
        $faltanteCubiertoConEfectivo = max(0, $total_venta - $pagosNoEfectivo);
        $recibido = (int)round($totalEfectivo);
        $cambio   = 0;
        if ($totalEfectivo > $faltanteCubiertoConEfectivo) {
            $cambio = (int)round($totalEfectivo - $faltanteCubiertoConEfectivo);
        }
    } else {
        $recibido = isset($data['recibido']) ? (int)round($data['recibido']) : null;
        $cambio   = isset($data['cambio'])   ? (int)round($data['cambio'])   : null;
    }

    // =====================================================
    // INSERTAR VENTA
    // =====================================================
    $stmt = $pdo->prepare("INSERT INTO ventas
        (descripcion, total_venta, metodo_pago, documento, tiempo_registro, documento_operador, estado, recibido, cambio)
        VALUES
        ('Venta de productos', ?, ?, ?, NOW(), ?, ?, ?, ?)");
    $stmt->execute([$total_venta, $metodo_pago_guardar, $documento_cliente, $documento_operador, $estado_venta, $recibido, $cambio]);
    $cont_venta = $pdo->lastInsertId();

    // =====================================================
    // DETALLE
    // =====================================================
    $stmtDetalle = $pdo->prepare("INSERT INTO detalle_venta
      (cont_venta, cont_producto, descripcion, cantidad_productos, sub_total, precio_unitario,
       descuento_en_pesos, porcentaje_descuento, tiempo_registro, documento_operador, estado)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 'activo')");

    $restante     = $descuento_aplicado;
    $ultimoIndice = array_key_last($productos);

    foreach ($productos as $idx => $p) {
        if (!isset($p['cont_producto'])) {
            throw new Exception("Falta `cont_producto` en producto: " . json_encode($p));
        }

        $cant   = (int)round($p['cantidad']);
        $pv     = (int)round($p['precio']);
        $sub    = $cant * $pv;

        $mi = (int)($margenes[$idx] ?? 0);

        if ($margen_total > 0 && $descuento_aplicado > 0) {
            if ($idx !== $ultimoIndice) {
                $itemDesc = (int) round($descuento_aplicado * ($mi / $margen_total));
                if ($itemDesc > $mi)       $itemDesc = $mi;
                if ($itemDesc > $restante) $itemDesc = $restante;
                $restante -= $itemDesc;
            } else {
                $itemDesc = min($restante, $mi);
                $restante -= $itemDesc;
            }
        } else {
            $itemDesc = 0;
        }

        $porcDesc = 0.000;
        if ($sub > 0) {
            $porcDesc = round(($itemDesc / $sub) * 100, 3);
        }

        $stmtDetalle->execute([
            $cont_venta,
            $p['cont_producto'],
            $p['nombre'],
            $cant,
            $sub,
            $pv,
            $itemDesc,
            $porcDesc,
            $documento_operador
        ]);

        $stmtUpdateStock = $pdo->prepare("UPDATE productos SET cantidad = cantidad - ? WHERE cont_producto = ?");
        $stmtUpdateStock->execute([$cant, $p['cont_producto']]);
    }

    // =====================================================
    // PAGOS_VENTA (mixto)
    // =====================================================
    if ($usaPagosMixtos) {
        $stmtPago = $pdo->prepare("INSERT INTO pagos_venta
            (cont_venta, metodo, monto, referencia, tiempo_registro, documento_operador, estado)
            VALUES (:cont_venta, :metodo, :monto, :referencia, NOW(), :documento_operador, 'activo')");
        foreach ($pagosNormalizados as $p) {
            $stmtPago->execute([
                ':cont_venta'        => $cont_venta,
                ':metodo'            => $p['metodo'],
                ':monto'             => $p['monto'],
                ':referencia'        => $p['referencia'],
                ':documento_operador'=> $documento_operador
            ]);
        }
    }

    // =====================================================
    // CRÉDITO (venta 100% a crédito)
    // =====================================================
    if (
        $metodo_pago_guardar === 'credito' &&
        isset($data['plazo_credito']) &&
        isset($data['observaciones']) &&
        $documento_cliente !== '22222222222'
    ) {
        $num_credito = generarNumCredito($pdo);
        $plazo       = (int)$data['plazo_credito'];
        $obs         = trim($data['observaciones']);
        $valor_credito = $total_venta;
        $saldo         = $valor_credito;
        $estado        = 'activo';

        $stmtDet = $pdo->prepare("SELECT cont_detalle_venta FROM detalle_venta WHERE cont_venta = ? ORDER BY cont_detalle_venta DESC LIMIT 1");
        $stmtDet->execute([$cont_venta]);
        $cont_detalle_venta = $stmtDet->fetchColumn();

        $stmtCredito = $pdo->prepare("INSERT INTO creditos
            (num_credito, cont_detalle_venta, documento, valor_credito, abonos, saldo, plazo_credito, observaciones, tiempo_registro, documento_operador, estado)
            VALUES (:num_credito, :cont_detalle_venta, :documento, :valor_credito, 0, :saldo, :plazo, :observaciones, NOW(), :documento_operador, :estado)");
        $stmtCredito->execute([
            ':num_credito'        => $num_credito,
            ':cont_detalle_venta' => $cont_detalle_venta,
            ':documento'          => $documento_cliente,
            ':valor_credito'      => $valor_credito,
            ':saldo'              => $saldo,
            ':plazo'              => $plazo,
            ':observaciones'      => $obs,
            ':documento_operador' => $documento_operador,
            ':estado'             => $estado
        ]);
    }

    // ✅ Confirmamos la venta
    $pdo->commit();
    echo json_encode(['success' => true, 'cont_venta' => (int)$cont_venta]);
    exit;

   


} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error en la transacción: ' . $e->getMessage()]);
    exit;
}

// -------- Helpers --------
function generarNumCredito($pdo) {
    $stmt = $pdo->query("SELECT MAX(num_credito) AS ultimo FROM creditos");
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($res && $res['ultimo']) {
        $ultimo = intval(substr($res['ultimo'], 2)); // CR001 -> 1
        $nuevo = $ultimo + 1;
    } else {
        $nuevo = 1;
    }
    return 'CR' . str_pad($nuevo, 3, '0', STR_PAD_LEFT);
}
