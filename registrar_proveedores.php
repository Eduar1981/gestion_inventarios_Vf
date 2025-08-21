<?php
// Incluir el archivo de conexión PDO
require 'pdo.php';

session_start();

date_default_timezone_set('America/Bogota');

// Verificar si el usuario ha iniciado sesión y si tiene rol de administrador
if (!isset($_SESSION['documento']) || 
    !in_array($_SESSION['rol'], ['administrador', 'superadmin'])) {
    header('Location: index.php');
    exit();
}


// Verificar si el formulario ha sido enviado mediante POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar y validar los datos del formulario
    $nom_comercial = !empty($_POST['nom_comercial']) ? trim($_POST['nom_comercial']) : null;
    $tipo_persona = !empty($_POST['tipo_persona']) ? trim($_POST['tipo_persona']) : null;
    $tipo_documento = !empty($_POST['tipo_documento']) ? trim($_POST['tipo_documento']) : null;
    $doc_proveedor = !empty($_POST['documento']) ? trim($_POST['documento']) : null;
    $nom_representante = !empty($_POST['nom_representante']) ? trim($_POST['nom_representante']) : null;
    $ape_representante = !empty($_POST['ape_representante']) ? trim($_POST['ape_representante']) : null;
    $celular = !empty($_POST['celular']) ? trim($_POST['celular']) : null;
    $tel_fijo = !empty($_POST['tel_fijo']) ? trim($_POST['tel_fijo']) : null;
    $correo = !empty($_POST['correo']) ? trim($_POST['correo']) : null;
    $direccion = !empty($_POST['direccion']) ? trim($_POST['direccion']) : null;
    $ciudad = !empty($_POST['ciudad']) ? trim($_POST['ciudad']) : null;

    // Capturar el documento del operador desde la sesión
    $documento_operador = $_SESSION['documento'];

    // El estado siempre será 'activo'
    $estado = 'activo';

    // Capturar el tiempo de registro (fecha y hora actual)
    $tiempo_registro = date('Y-m-d H:i:s');

    // Validación adicional
    if ($nom_comercial && $tipo_persona && $tipo_documento && $doc_proveedor && $nom_representante && $ape_representante && $celular && $correo && $direccion && $ciudad) {
        try {
            //Verificar si el doc_proveedor
            $checkSql = "SELECT COUNT(*) FROM proveedores WHERE doc_proveedor = :doc_proveedor";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->bindParam(':doc_proveedor', $doc_proveedor);
            $checkStmt->execute();
            $exists = $checkStmt->fetchColumn();

            // Si el proveedor ya existe
            if ($exists > 0) {
                $error_message = "Ya existe un proveedor con ese número de doc_proveedor";
            } else {
                // Preparar la consulta SQL para insertar los datos
                $sql = "INSERT INTO proveedores 
                (nom_comercial, tipo_persona, tipo_documento, doc_proveedor, nom_representante, ape_representante, celular, tel_fijo, correo, direccion, ciudad, tiempo_registro, documento_operador, estado) 
                VALUES 
                (:nom_comercial, :tipo_persona, :tipo_documento, :doc_proveedor, :nom_representante, :ape_representante, :celular,  :tel_fijo, :correo, :direccion, :ciudad, :tiempo_registro, :documento_operador, :estado)";

                // Preparar la sentencia
                $stmt = $pdo->prepare($sql);

                // Asignar los valores
                $stmt->bindParam(':nom_comercial', $nom_comercial);
                $stmt->bindParam(':tipo_persona', $tipo_persona);
                $stmt->bindParam(':tipo_documento', $tipo_documento);
                $stmt->bindParam(':doc_proveedor', $doc_proveedor);
                $stmt->bindParam(':nom_representante', $nom_representante);
                $stmt->bindParam(':ape_representante', $ape_representante);
                $stmt->bindParam(':celular', $celular);
                $stmt->bindParam(':tel_fijo', $tel_fijo);
                $stmt->bindParam(':correo', $correo);
                $stmt->bindParam(':direccion', $direccion);
                $stmt->bindParam(':ciudad', $ciudad);
                $stmt->bindParam(':tiempo_registro', $tiempo_registro);
                $stmt->bindParam(':documento_operador', $documento_operador);
                $stmt->bindParam(':estado', $estado);

                // Ejecutar la sentencia
                if ($stmt->execute()) {
                    // Redirigir a la página de éxito o mostrar un mensaje de éxito
                    header('Location: ver_proveedores.php');
                    echo "Proveedor registrado correctamente.";
                } else {
                    // Mostrar mensaje de error en caso de fallo
                    $error_message = "Error al registrar el proveedor. Intenta de nuevo.";
                }
            }
            
        } catch (PDOException $e) {
            // Capturar errores en la base de datos
            $error_message = "Error en la base de datos: " . $e->getMessage();
        }
    } else {
        // Si faltan campos requeridos, mostrar un mensaje de error
        $error_message = "Por favor, completa todos los campos.";
    }
}
?>