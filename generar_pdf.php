<?php
require 'pdo.php';
require 'libs/fpdf/fpdf.php';

session_start();

if (!isset($_SESSION['documento'])) {
    header('Location: index.php');
    exit();
}

$estado = 'activo';
$stmt = $pdo->prepare('
    SELECT `referencia`, `nombre`, `precio_venta`
    FROM `productos`
    WHERE `estado` = :activo');
$stmt->bindParam(':activo', $estado);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_OBJ);

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Listado de Productos', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

$pdf->Cell(50, 10, 'Referencia', 1);
$pdf->Cell(70, 10, 'Nombre', 1);
$pdf->Cell(40, 10, 'Precio Venta', 1);
$pdf->Ln();

foreach ($productos as $producto) {
    $pdf->Cell(50, 10, $producto->referencia, 1);
    $pdf->Cell(70, 10, $producto->nombre, 1);
    $pdf->Cell(40, 10, '$' . number_format($producto->precio_venta, 2), 1);
    $pdf->Ln();
}

$pdf->Output('D', 'Listado_Productos.pdf');
?>