<?php
require 'pdo.php';
session_start();

// Verifica si se ha recibido el contador del proveedor
if (isset($_POST['contador_usuarios'])) {
    $contador_usuarios = $_POST['contador_usuarios'];
    
    // Consulta para obtener los detalles del proveedor específico
    $statement = $pdo->prepare("
        SELECT 
            `contador_usuarios`, 
            `tipo_doc`, 
            `documento`, 
            `nombre`, 
            `apellido`, 
            `fecha_nacimiento`, 
            `correo`, 
            `celular`,
            `direccion`, 
            `ciudad` 
        FROM 
            `usuarios` 
        WHERE 
            `contador_usuarios` = :contador_usuarios
            AND `estado` = 'activo'
    ");
    $statement->bindParam(':contador_usuarios', $contador_usuarios, PDO::PARAM_INT);
    $statement->execute();
    
    $result = $statement->fetch(PDO::FETCH_OBJ);
    
    // Enviar los datos en formato JSON
    echo json_encode($result);
}
?>
