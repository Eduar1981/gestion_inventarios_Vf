<?php
require 'pdo.php'; // Conexión a la base de datos

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();
date_default_timezone_set('America/Bogota');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // ✅ Capturar los datos del cliente desde el formulario
        $tipo_persona = $_POST['tipo_persona'];
        $tipo_documento = $_POST['tipo_documento'];
        $documento = $_POST['documento'];
        $correo = $_POST['correo'];
        $nom_comercial = $_POST['nom_comercial'] ?? null;
        $cont_venta = $_POST['cont_venta'];
        $documento_operador = $_SESSION['documento'];
        $tiempo_registro = date('Y-m-d H:i:s');
        $estado = 'activo';

        // ✅ Insertar el cliente en la base de datos
        $stmtCliente = $pdo->prepare("INSERT INTO clientes (tipo_persona, tipo_documento, documento, correo, nom_comercial, cont_venta, tiempo_registro, documento_operador, estado) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtCliente->execute([$tipo_persona, $tipo_documento, $documento, $correo, $nom_comercial, $cont_venta, $tiempo_registro, $documento_operador, $estado]);

        // ✅ Obtener los detalles de la venta con el nombre del producto
        $stmtVenta = $pdo->prepare("SELECT * FROM ventas WHERE cont_venta = ?");
        $stmtVenta->execute([$cont_venta]);
        $venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);

        $stmtDetalles = $pdo->prepare("
            SELECT d.cont_producto, p.nombre AS nombre_producto, d.cantidad_productos, d.precio_unitario, d.sub_total 
            FROM detalle_venta d
            INNER JOIN productos p ON d.cont_producto = p.cont_producto
            WHERE d.cont_venta = ?
        ");
        $stmtDetalles->execute([$cont_venta]);
        $detalles_venta = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

        // ✅ Convertir la fecha y hora al formato deseado
        $fechaOriginal = date_create($venta['tiempo_registro']);
        $fechaFormateada = date_format($fechaOriginal, 'd/m/Y'); 
        $horaFormateada = date_format($fechaOriginal, 'g:i:s A'); 

        $pdo->commit();

        // ✅ Calcular el total de la factura sumando los sub_totales
        $total_factura = array_sum(array_column($detalles_venta, 'sub_total'));

        // ✅ Formato de moneda en pesos colombianos
        function formatoCOP($valor) {
            return number_format($valor, 0, ',', '.');
        }

        // ✅ Construir el cuerpo del correo con UTF-8
        $mensaje = "
        <html>
        <head>
            <meta charset='UTF-8'>
        <style>
        body {
            font-family: Arial, sans-serif; 
            color: #333; 
        }
        </style>
        </head>
        <body>
            <div class='header'>
                <div>
                    <h2>VIN</h2>
                    <p>📍 Dirección: Calle 8 #7e-04, Gudalajara de Buga</p>
                    <p>📞 Teléfono: +57 3186941522</p>
                    <p>📧 Email: edunico180@gmail.com </p>
                </div>
            </div>
            <hr>
            <p><strong>Método de Pago:</strong> {$venta['metodo_pago']}</p>
            <p><strong>Total Venta:</strong> $" . formatoCOP($venta['total_venta']) . "</p>
            <p><strong>Fecha:</strong> $fechaFormateada $horaFormateada</p>
            <h3>Detalles de los Productos</h3>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($detalles_venta as $detalle) {
            $mensaje .= "<tr>
                            <td>{$detalle['nombre_producto']}</td>
                            <td>{$detalle['cantidad_productos']}</td>
                            <td>$" . formatoCOP($detalle['precio_unitario']) . "</td>
                            <td>$" . formatoCOP($detalle['sub_total']) . "</td>
                        </tr>";
        }

        $mensaje .= "</tbody>
            <tfoot>
                <tr>
                    <td colspan='3'><strong>Total Factura:</strong></td>
                    <td><strong>$" . formatoCOP($total_factura) . "</strong></td>
                </tr>
            </tfoot>
            </table>
        </body>
        </html>";

        // ✅ Configurar cabeceras del correo
        $para = $correo;
    
        // ...existing code...
        $asunto = 'Recibo #' . ' RDV-' . $venta['cont_venta'];
        // ...existing code...
        
        // Cabeceras para correo HTML
        $cabeceras  = 'MIME-Version: 1.0' . "\r\n";
        $cabeceras .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $cabeceras .= 'From: MUNDO PINK <contacto@edunico180.com>' . "\r\n";
        
        // Enviar correo
        if(mail($para, $asunto, $mensaje, $cabeceras)) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Error al enviar el correo"]);
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}
?>


