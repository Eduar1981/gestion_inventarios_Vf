<?php
require 'pdo.php';

session_start();

// Verifica si se ha recibido el contador del proveedor
if (isset($_POST['contador_clientes'])) {
    $contador_clientes = $_POST['contador_clientes'];
    
    // Consulta para obtener los detalles del proveedor específico
    $statement = $pdo->prepare("
        SELECT 
            `contador_clientes`, 
            `tipo_persona`, 
            `tipo_documento`, 
            `documento`, 
            `nombre`, 
            `apellido`, 
            `correo`, 
            `fecha_nacimiento`, 
            `ciudad`,
            `direccion`, 
            `nom_comercial`
        FROM 
            `clientes` 
        WHERE 
            `contador_clientes` = :contador_clientes 
            AND `estado` = 'activo'
    ");
    $statement->bindParam(':contador_clientes', $contador_clientes, PDO::PARAM_INT);
    $statement->execute();
    
    $result = $statement->fetch(PDO::FETCH_OBJ);
    
    // Enviar los datos en formato JSON
    echo json_encode($result);
}
?>