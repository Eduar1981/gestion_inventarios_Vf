<?php
require 'pdo.php';
require 'vendor/autoload.php'; // Librería PHPMailer y FPDF

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use FPDF\FPDF;

function enviarFactura($cont_venta, $correoCliente) {
    global $pdo;

    // Obtener datos de la venta
    $stmt = $pdo->prepare("SELECT * FROM ventas WHERE cont_venta = ?");
    $stmt->execute([$cont_venta]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        throw new Exception("Venta no encontrada.");
    }

    // Crear PDF de la factura
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(190, 10, 'Factura de Venta', 1, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(190, 10, 'Numero de Factura: ' . $venta['cont_venta'], 1, 1);
    $pdf->Cell(190, 10, 'Total Venta: $' . $venta['total_venta'], 1, 1);
    $pdf->Output('F', 'factura.pdf');

    // Enviar por correo (se necesita PHPMailer)
    $mail = new PHPMailer(true);
    $mail->setFrom('tuemail@empresa.com', 'Tu Empresa');
    $mail->addAddress($correoCliente);
    $mail->Subject = "Factura de su compra";
    $mail->Body = "Adjunto encontrará la factura de su compra.";
    $mail->addAttachment('factura.pdf');
    $mail->send();
}
?>
