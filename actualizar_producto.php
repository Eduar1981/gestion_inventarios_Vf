<?php
require 'pdo.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cont_producto = filter_input(INPUT_POST, 'cont_producto', FILTER_VALIDATE_INT);
    $codigo_producto = filter_input(INPUT_POST, 'codigo_producto', FILTER_SANITIZE_STRING);
    $referencia = filter_input(INPUT_POST, 'referencia', FILTER_SANITIZE_STRING);
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
    $categoria = filter_input(INPUT_POST, 'categoria', FILTER_VALIDATE_INT);
    $cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
    $cantidad_minima = filter_input(INPUT_POST, 'cantidad_minima', FILTER_VALIDATE_INT);

    // ✅ Limpieza de datos numéricos (eliminar caracteres no válidos)
    $precio_compra = isset($_POST['precio_compra']) ? str_replace(",", ".", preg_replace("/[^0-9,.]/", "", $_POST['precio_compra'])) : null;
    $con_iva = $_POST['con_iva'] ?? null;
    $precio_venta = isset($_POST['precio_venta']) ? str_replace(",", ".", preg_replace("/[^0-9,.]/", "", $_POST['precio_venta'])) : null;
    $porcentaje_ganancia = isset($_POST['porcentaje_ganancia']) ? $_POST['porcentaje_ganancia'] : null;

    // ✅ Evitar actualizar con valores vacíos, usar los valores actuales si el usuario no modificó algo
    $sql = "SELECT * FROM productos WHERE cont_producto = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cont_producto]);
    $producto_actual = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto_actual) {
        $_SESSION['mensaje'] = "❌ Producto no encontrado.";
        $_SESSION['tipo_mensaje'] = "danger";
        header("Location: ver_productos.php");
        exit();
    }

    // ✅ Mantener valores actuales si el usuario no modificó algún campo
    $codigo_producto = $codigo_producto ?: $producto_actual['codigo_producto'];
    $referencia = $referencia ?: $producto_actual['referencia'];
    $nombre = $nombre ?: $producto_actual['nombre'];
    $descripcion = $descripcion ?: $producto_actual['descripcion'];
    $categoria = $categoria ?? $producto_actual['categoria'];
    $cantidad = $cantidad ?? $producto_actual['cantidad'];
    $cantidad_minima = $cantidad_minima ?? $producto_actual['cantidad_minima'];
    $precio_compra = $precio_compra !== null ? $precio_compra : $producto_actual['precio_compra'];
    $con_iva = $con_iva !== null ? $con_iva : $producto_actual['con_iva'];
    $precio_venta = $precio_venta !== null ? $precio_venta : $producto_actual['precio_venta'];
    $porcentaje_ganancia = $porcentaje_ganancia !== null ? $porcentaje_ganancia : $producto_actual['porcentaje_ganancia'];

    // ✅ Construir dinámicamente la consulta SQL solo con los campos modificados
    $updates = [];
    $params = [];

    if ($codigo_producto !== $producto_actual['codigo_producto']) {
        $updates[] = "codigo_producto = ?";
        $params[] = $codigo_producto;
    }
    if ($referencia !== $producto_actual['referencia']) {
        $updates[] = "referencia = ?";
        $params[] = $referencia;
    }
    if ($nombre !== $producto_actual['nombre']) {
        $updates[] = "nombre = ?";
        $params[] = $nombre;
    }
    if ($descripcion !== $producto_actual['descripcion']) {
        $updates[] = "descripcion = ?";
        $params[] = $descripcion;
    }
    if ($categoria !== $producto_actual['categoria']) {
        $updates[] = "categoria = ?";
        $params[] = $categoria;
    }
    if ($precio_compra !== $producto_actual['precio_compra']) {
        $updates[] = "precio_compra = ?";
        $params[] = $precio_compra;
    }
    if ($con_iva !== $producto_actual['con_iva']) {
        $updates[] = "con_iva = ?";
        $params[] = $con_iva;
    }
    if ($precio_venta !== $producto_actual['precio_venta']) {
        $updates[] = "precio_venta = ?";
        $params[] = $precio_venta;
    }
    if ($porcentaje_ganancia !== $producto_actual['porcentaje_ganancia']) {
        $updates[] = "porcentaje_ganancia = ?";
        $params[] = $porcentaje_ganancia;
    }
    if ($cantidad !== $producto_actual['cantidad']) {
        $updates[] = "cantidad = ?";
        $params[] = $cantidad;
    }
    if ($cantidad_minima !== $producto_actual['cantidad_minima']) {
        $updates[] = "cantidad_minima = ?";
        $params[] = $cantidad_minima;
    }

    // ✅ Solo actualizar si hay cambios
    if (!empty($updates)) {
        $sql_update = "UPDATE productos SET " . implode(", ", $updates) . " WHERE cont_producto = ?";
        $params[] = $cont_producto;
        $stmt = $pdo->prepare($sql_update);
        $stmt->execute($params);
    }

    $_SESSION['mensaje'] = "✅ Producto actualizado con éxito.";
    header("Location: ver_productos.php");
    exit();
}
?>

