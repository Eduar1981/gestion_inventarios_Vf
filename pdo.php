<?php
// Conexión a la base de datos en ProFreeHost
$host = "localhost";  // Servidor de MySQL
$dbname = "gestion_inventario";  // Nombre de tu base de datos
$username = "root";  // Usuario MySQL
$password = "";  // Usa la contraseña de tu vPanel (CPanel)

$options = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'",
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION // Para mostrar errores de conexión
);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}
?>

