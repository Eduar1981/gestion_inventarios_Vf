<?php
require 'pdo.php';
session_start();


date_default_timezone_set('America/Bogota');

// Si quieres permitir vendedor, usa la línea de abajo; si no, deja tu validación actual.
if (!isset($_SESSION['documento']) || !in_array($_SESSION['rol'], ['superadmin','administrador','vendedor'])) {
    header('Location: index.php'); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ---- CAPTURA ----
    $tipo_persona     = !empty($_POST['tipo_persona']) ? trim($_POST['tipo_persona']) : null;
    $tipo_documento   = !empty($_POST['tipo_documento']) ? trim($_POST['tipo_documento']) : null;
    $documento        = !empty($_POST['documento']) ? trim($_POST['documento']) : null;
    $nombre           = !empty($_POST['nombre']) ? trim($_POST['nombre']) : null;
    $apellido         = !empty($_POST['apellido']) ? trim($_POST['apellido']) : null;
    $fecha_nacimiento = !empty($_POST['fecha_nacimiento']) ? trim($_POST['fecha_nacimiento']) : null;
    $celular          = !empty($_POST['celular']) ? trim($_POST['celular']) : null;
    $telefono         = !empty($_POST['telefono']) ? trim($_POST['telefono']) : null; // opcional
    $correo           = !empty($_POST['correo']) ? trim($_POST['correo']) : null;
    $departamento     = !empty($_POST['departamento']) ? trim($_POST['departamento']) : null;
    $ciudad           = !empty($_POST['ciudad']) ? trim($_POST['ciudad']) : null;
    $direccion        = !empty($_POST['direccion']) ? trim($_POST['direccion']) : null;
    $nom_comercial    = !empty($_POST['nom_comercial']) ? trim($_POST['nom_comercial']) : null;
    $tributo_id       = !empty($_POST['tributo_id']) ? trim($_POST['tributo_id']) : null;

    // ---- VALIDACIONES COMUNES ----
    if (!$tipo_persona || !$tipo_documento || !$documento || !$correo || !$departamento || !$ciudad || !$direccion || !$celular || !$tributo_id) {
        $_SESSION['error'] = "Por favor, completa los campos obligatorios.";
        header('Location: registrar_cliente.php'); exit();
    }

    // ---- REGLAS POR TIPO DE PERSONA ----
    if ($tipo_persona === 'natural') {
        if (!$nombre || !$apellido) {
            $_SESSION['error'] = "Nombre y apellido son obligatorios para personas naturales.";
            header('Location: registrar_cliente.php'); exit();
        }
        // Si exiges fecha_nacimiento para natural, valida edad:
        if ($fecha_nacimiento) {
            try {
                $fn = new DateTime($fecha_nacimiento);
                $hoy = new DateTime();
                $edad = $hoy->diff($fn)->y;
                if ($edad < 18) {
                    $_SESSION['error'] = "Debe ser mayor o igual a 18 años para registrarse.";
                    header('Location: registrar_cliente.php'); exit();
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Fecha de nacimiento inválida.";
                header('Location: registrar_cliente.php'); exit();
            }
        }
        // Si quieres hacer obligatoria la fecha, descomenta:
        // else { $_SESSION['error'] = "La fecha de nacimiento es obligatoria para persona natural."; header('Location: registrar_cliente.php'); exit(); }
    } else { // juridica
        // Para empresas, nombre/apellido pueden no aplicar
        $fecha_nacimiento = null; // no aplica
        // Si quieres exigir nom_comercial, descomenta:
        // if (!$nom_comercial) { $_SESSION['error'] = "Nombre comercial es obligatorio para persona jurídica."; header('Location: registrar_cliente.php'); exit(); }
    }

    try {
        // ÚNICO POR DOCUMENTO
        $checkSql = "SELECT COUNT(*) FROM clientes WHERE documento = :documento";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindParam(':documento', $documento);
        $checkStmt->execute();
        if ($checkStmt->fetchColumn() > 0) {
            $_SESSION['error'] = "Ya existe un cliente con ese número de documento.";
            header('Location: registrar_cliente.php'); exit();
        }

        $estado = 'activo';
        $tiempo_registro = date('Y-m-d H:i:s');
        $documento_operador = $_SESSION['documento'];

        // INSERT COMPLETO
        $sql = "INSERT INTO clientes 
        (tipo_persona, tipo_documento, documento, nombre, apellido, fecha_nacimiento, celular, telefono, correo, departamento, ciudad, direccion, nom_comercial, tributo_id, tiempo_registro, documento_operador, estado) 
        VALUES 
        (:tipo_persona, :tipo_documento, :documento, :nombre, :apellido, :fecha_nacimiento, :celular, :telefono, :correo, :departamento, :ciudad, :direccion, :nom_comercial, :tributo_id, :tiempo_registro, :documento_operador, :estado)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tipo_persona', $tipo_persona);
        $stmt->bindParam(':tipo_documento', $tipo_documento);
        $stmt->bindParam(':documento', $documento);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        // si viene vacío, guarda NULL
        if ($fecha_nacimiento) { $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento); }
        else { $stmt->bindValue(':fecha_nacimiento', null, PDO::PARAM_NULL); }
        $stmt->bindParam(':celular', $celular);
        if ($telefono) { $stmt->bindParam(':telefono', $telefono); }
        else { $stmt->bindValue(':telefono', null, PDO::PARAM_NULL); }
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':departamento', $departamento);
        $stmt->bindParam(':ciudad', $ciudad);
        $stmt->bindParam(':direccion', $direccion);
        if ($nom_comercial) { $stmt->bindParam(':nom_comercial', $nom_comercial); }
        else { $stmt->bindValue(':nom_comercial', null, PDO::PARAM_NULL); }
        $stmt->bindParam(':tributo_id', $tributo_id);
        $stmt->bindParam(':tiempo_registro', $tiempo_registro);
        $stmt->bindParam(':documento_operador', $documento_operador);
        $stmt->bindParam(':estado', $estado);

        if ($stmt->execute()) {
            header('Location: ver_clientes.php'); exit();
        } else {
            $_SESSION['error'] = "Error al registrar el cliente. Intenta de nuevo.";
            header('Location: registrar_cliente.php'); exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
        header('Location: registrar_cliente.php'); exit();
    }
}
?>