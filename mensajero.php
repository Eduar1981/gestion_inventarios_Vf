<?php
// Archivo: mensajero.php
// Este archivo muestra mensajes de error/alerta al usuario
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensaje - Gestión PINK</title>
    <link rel="stylesheet" href="style/css/index.css">
    <style>
        .mensaje-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .mensaje-container h1 {
            color: #e91e63; /* Color rosa para mantener la temática PINK */
            margin-bottom: 20px;
        }
        
        .mensaje-container p {
            font-size: 18px;
            color: #333;
            margin-bottom: 30px;
        }
        
        .btn-volver {
            display: inline-block;
            padding: 10px 20px;
            background-color: #e91e63;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .btn-volver:hover {
            background-color: #c2185b;
        }
    </style>
</head>
<body>
    <div class="mensaje-container">
        <?php if (isset($titulo) && isset($parrafo)): ?>
            <h1><?php echo htmlspecialchars($titulo); ?></h1>
            <p><?php echo htmlspecialchars($parrafo); ?></p>
            
            <?php if (isset($ubicacion)): ?>
                <a href="<?php echo htmlspecialchars($ubicacion); ?>" class="btn-volver">Volver</a>
            <?php else: ?>
                <a href="index.php" class="btn-volver">Volver al inicio</a>
            <?php endif; ?>
        <?php else: ?>
            <h1>Error</h1>
            <p>Ha ocurrido un error inesperado.</p>
            <a href="index.php" class="btn-volver">Volver al inicio</a>
        <?php endif; ?>
    </div>
</body>
</html>