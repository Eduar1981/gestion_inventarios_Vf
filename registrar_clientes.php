<?php
// Incluir el archivo de conexión PDO
require 'pdo.php';

session_start();
date_default_timezone_set('America/Bogota');

// Verificar si el usuario ha iniciado sesión y tiene rol de administrador
if (!isset($_SESSION['documento']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: index.php');
    exit();
}

$error_message = ""; // Variable para mensajes de error

// Verificar si el formulario ha sido enviado mediante POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar y validar los datos del formulario
    $tipo_persona = !empty($_POST['tipo_persona']) ? trim($_POST['tipo_persona']) : null;
    $tipo_documento = !empty($_POST['tipo_documento']) ? trim($_POST['tipo_documento']) : null;
    $documento = !empty($_POST['documento']) ? trim($_POST['documento']) : null;
    $nombre = !empty($_POST['nombre']) ? trim($_POST['nombre']) : null;
    $apellido = !empty($_POST['apellido']) ? trim($_POST['apellido']) : null;
    $fecha_nacimiento = !empty($_POST['fecha_nacimiento']) ? trim($_POST['fecha_nacimiento']) : null;
    $celular = !empty($_POST['celular']) ? trim($_POST['celular']) : null;
    $correo = !empty($_POST['correo']) ? trim($_POST['correo']) : null;
    $ciudad = !empty($_POST['ciudad']) ? trim($_POST['ciudad']) : null;
    $direccion = !empty($_POST['direccion']) ? trim($_POST['direccion']) : null;
    $nom_comercial = !empty($_POST['nom_comercial']) ? trim($_POST['nom_comercial']) : null;

    // Verificar que todos los campos requeridos estén completos
    if (!$tipo_persona || !$tipo_documento || !$documento || !$nombre || !$apellido || !$fecha_nacimiento || !$celular || !$correo || !$ciudad || !$direccion) {
        $_SESSION['error'] = "Por favor, completa todos los campos.";
        header('Location: registrar_cliente.php');
        exit();
    } else {
        // Calcular la edad a partir de la fecha de nacimiento
        $fecha_nacimiento_obj = new DateTime($fecha_nacimiento);
        $fecha_actual = new DateTime();
        $edad = $fecha_actual->diff($fecha_nacimiento_obj)->y;

        // Verificar que el cliente sea mayor o igual a 18 años
        if ($edad < 18) {
            $_SESSION['error'] = "Debe ser mayor o igual a 18 años para registrarse.";
            header('Location: registrar_cliente.php');
            exit();
        } else {
            try {
                // Verificar si el cliente ya está registrado
                $checkSql = "SELECT COUNT(*) FROM clientes WHERE documento = :documento";
                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->bindParam(':documento', $documento);
                $checkStmt->execute();
                $exists = $checkStmt->fetchColumn();

                if ($exists > 0) {
                    $_SESSION['error'] = "Ya existe un cliente con ese número de documento.";
                    header('Location: registrar_cliente.php');
                    exit();
                } else {
                    // Configuración para registrar el nuevo cliente
                    $estado = 'activo';
                    $tiempo_registro = date('Y-m-d H:i:s');
                    $documento_operador = $_SESSION['documento'];

                    // Consulta SQL para insertar los datos
                    $sql = "INSERT INTO clientes 
                            (tipo_persona, tipo_documento, documento, nombre, apellido, fecha_nacimiento, celular, correo, ciudad, direccion, nom_comercial, tiempo_registro, documento_operador, estado) 
                            VALUES 
                            (:tipo_persona, :tipo_documento, :documento, :nombre, :apellido, :fecha_nacimiento, :celular, :correo, :ciudad, :direccion, :nom_comercial, :tiempo_registro, :documento_operador, :estado)";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':tipo_persona', $tipo_persona);
                    $stmt->bindParam(':tipo_documento', $tipo_documento);
                    $stmt->bindParam(':documento', $documento);
                    $stmt->bindParam(':nombre', $nombre);
                    $stmt->bindParam(':apellido', $apellido);
                    $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
                    $stmt->bindParam(':celular', $celular);
                    $stmt->bindParam(':correo', $correo);
                    $stmt->bindParam(':ciudad', $ciudad);
                    $stmt->bindParam(':direccion', $direccion);
                    $stmt->bindParam(':nom_comercial', $nom_comercial);
                    $stmt->bindParam(':tiempo_registro', $tiempo_registro);
                    $stmt->bindParam(':documento_operador', $documento_operador);
                    $stmt->bindParam(':estado', $estado);

                    // Ejecutar la consulta
                    if ($stmt->execute()) {
                        // Redirigir a la página de éxito o mostrar un mensaje de éxito
                        header('Location: ver_clientes.php');
                        exit();
                    } else {
                        $_SESSION['error'] = "Error al registrar el cliente. Intenta de nuevo.";
                        header('Location: registrar_cliente.php');
                        exit();
                    }
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
                header('Location: registrar_cliente.php');
                exit();
            }
        }
    }
}
?>