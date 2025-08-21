<?php
// Incluir el archivo de conexión PDO
require 'pdo.php';

session_start();

date_default_timezone_set('America/Bogota');

// Verificar si el usuario ha iniciado sesión y si tiene rol de administrador
if (
    empty($_SESSION['documento']) || 
    !in_array($_SESSION['rol'], ['administrador', 'superadmin'])
) {
    header('Location: index.php');
    exit();
}

// Verificar si el formulario ha sido enviado mediante POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar y validar los datos del formulario
    $codigo = !empty($_POST['codigo']) ? trim($_POST['codigo']) : null;
    /* $referencia = !empty($_POST['referencia']) ? trim($_POST['referencia']) : null; */
    $nombre = !empty($_POST['nombre']) ? trim($_POST['nombre']) : null;
    

    $documento_operador = $_SESSION['documento'];
    $estado = 'activo';
    $tiempo_registro = date('Y-m-d H:i:s');

    if ($codigo &&  $nombre) {
        try {
            // Verificar si el código o la referencia ya existen
            $checkSql = "SELECT COUNT(*) FROM categorias WHERE codigo = :codigo";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->bindParam(':codigo', $codigo);
            $checkStmt->execute();
            $exists = $checkStmt->fetchColumn();

            // Si el producto ya existe
            if ($exists > 0) {
                $error_message = "Ya existe una categoria  con el mismo código.";
            } else {
                // Si no existe, proceder con la inserción
                $sql = "INSERT INTO `categorias`(`codigo`, `nombre`, `tiempo_registro`, `documento_operador`, `estado`) 
                        VALUES (:codigo, :nombre, :tiempo_registro, :documento_operador, :estado)";

                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':codigo', $codigo);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':tiempo_registro', $tiempo_registro);
                $stmt->bindParam(':documento_operador', $documento_operador);
                $stmt->bindParam(':estado', $estado);

                if ($stmt->execute()) {
                    header('Location: ver_categorias.php');
                    exit();
                } else {
                    $error_message = "Error al registrar la categoria. Intenta de nuevo.";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error en la base de datos: " . $e->getMessage();
        }
    } else {
        $error_message = "Por favor, completa todos los campos.";
    }
}
?>