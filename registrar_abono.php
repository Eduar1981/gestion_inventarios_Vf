<?php
require 'pdo.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensaje'] = '❌ Método no permitido';
    $_SESSION['tipo_mensaje'] = 'error';
    header("Location: ver_abonos.php");
    exit;
}


// Validar campos
$contador_creditos = $_POST['contador_creditos'] ?? null;
$fecha_abono = $_POST['fecha_abono'] ?? null;
$metodo_pago = $_POST['metodo_pago'] ?? null;
$valor_abono = $_POST['valor_abono'] ?? null;
$observaciones = $_POST['observaciones'] ?? null; // Puede ser null
$referencia_pago = $_POST['referencia_pago'] ?? null; // Debería venir siempre porque es required
$documento_operador = $_SESSION['documento'] ?? null;
$estado = 'activo';

if (!$contador_creditos || !$fecha_abono || !$metodo_pago || !$valor_abono || !$documento_operador) {
    echo json_encode(['error' => 'Faltan datos obligatorios']);
    exit;
}

try {
    // 1. Insertar el abono
    $stmt = $pdo->prepare("
        INSERT INTO abonos (
            contador_creditos,
            fecha_abono,
            metodo_pago,
            valor_abono,
            referencia_pago,
            observaciones,
            tiempo_registro,
            documento_operador,
            estado
        ) VALUES (
            :contador_creditos,
            :fecha_abono,
            :metodo_pago,
            :valor_abono,
            :referencia_pago,
            :observaciones,
            NOW(),
            :documento_operador,
            :estado
        )
    ");

    $stmt->execute([
        ':contador_creditos' => $contador_creditos,
        ':fecha_abono' => $fecha_abono,
        ':metodo_pago' => $metodo_pago,
        ':valor_abono' => $valor_abono,
        ':referencia_pago' => $referencia_pago,
        ':observaciones' => $observaciones,
        ':documento_operador' => $documento_operador,
        ':estado' => $estado
    ]);

    // 2. Actualizar el crédito (sumar abonos y recalcular saldo)
    $stmtCredito = $pdo->prepare("SELECT abonos, valor_credito FROM creditos WHERE contador_creditos = :id");
    $stmtCredito->execute([':id' => $contador_creditos]);
    $credito = $stmtCredito->fetch(PDO::FETCH_ASSOC);

    if ($credito) {
        $nuevoAbono = $credito['abonos'] + $valor_abono;
        $nuevoSaldo = $credito['valor_credito'] - $nuevoAbono;

        $updateCredito = $pdo->prepare("
            UPDATE creditos 
            SET abonos = :nuevo_abono, saldo = :nuevo_saldo 
            WHERE contador_creditos = :id
        ");

        $updateCredito->execute([
            ':nuevo_abono' => $nuevoAbono,
            ':nuevo_saldo' => $nuevoSaldo,
            ':id' => $contador_creditos
        ]);
    }

    $_SESSION['mensaje'] = "✅ Abono registrado correctamente";
    header("Location: ver_abonos.php");
    $_SESSION['tipo_mensaje'] = "exito";
exit;
} catch (PDOException $e) {
    $_SESSION['mensaje'] = "❌ Error: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: abonar_credito.php?contador_creditos=$contador_creditos");
    exit;
}
