<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="Cache-Control" content="no-cache">
    <title>Gestion Inventario | Web App</title>
</head>
<body>
<?php
    // Verificar si ya hay una sesión activa
    if(isset($_SESSION["user_id"])) {
        header('Location: home.php');
        exit;
    }
    else {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            // Incluir conexión a la base de datos
            require 'pdo.php';
        }

        // Sanitizar los datos recibidos del formulario
        $documento = filter_var(trim($_POST["documento"]), FILTER_SANITIZE_ENCODED, FILTER_SANITIZE_SPECIAL_CHARS);
        $contra = trim($_POST["contra"]);

        if($documento == '') {
            $titulo = "ERROR / 103";
            $parrafo = "Se detectó un intento de acceso no autorizado.";
            $ubicacion = "./";
            
            include 'mensajero.php';
            exit;
        } else {
            try {
                // Buscar usuario por documento
                $stmt = $pdo->prepare("SELECT `contador_usuarios`, `nombre`, `documento`, `rol`, `contra` FROM `usuarios` WHERE documento = ?");
                $stmt->execute([$documento]);
                $user = $stmt->fetch(PDO::FETCH_OBJ);

                // Verificar si existe el usuario y la contraseña es correcta
                if (!$user || !password_verify($contra, $user->contra)) {
                    $titulo = "ERROR / 104";
                    $parrafo = "Documento o contraseña incorrectos.";
                    $ubicacion = "./";
                    $raiz = "./";
                    
                    include 'mensajero.php';
                    exit;
                } else {
                    // Crear sesión con los datos del usuario
                    $_SESSION['user_id'] = $user->contador_usuarios;
                    $_SESSION['user_name'] = $user->nombre;
                    $_SESSION['documento'] = $user->documento;
                    $_SESSION['rol'] = $user->rol;
                    
                    // Actualizar fecha de última sesión
                    $sesion_server = date('Y-m-d H:i:s');
                    setlocale(LC_TIME, "es_ES");
                    date_default_timezone_set('America/Bogota');
                    
                    // Actualizar la fecha de última sesión en la base de datos
                    $update_stmt = $pdo->prepare("UPDATE `usuarios` SET `fec_ult_sesion` = ? WHERE `contador_usuarios` = ?");
                    $update_stmt->execute([$sesion_server, $user->contador_usuarios]);
                    
                    // Redirigir al home
                    header('Location: home.php');
                    exit;
                }
            } catch (PDOException $e) {
                error_log("Error en el login: " . $e->getMessage());
                $titulo = "ERROR";
                $parrafo = "Error al procesar la solicitud. Intenta más tarde.";
                $ubicacion = "./";
                
                include 'mensajero.php';
                exit;
            }
        }
    }
?>
</body>
</html>