<?php
//Parametros de conexion con la DB por medio del pdo
require 'pdo.php';

session_start();

// Verifica que el contador_usuarios del usuario esté presente en la URL
if (isset($_GET['contador_usuarios'])) {
    $contador_usuarios = $_GET['contador_usuarios'];

// Verifica si el usuario tiene permisos de administrador
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'superadmin' && $_SESSION['rol'] !== 'administrador')) {
    // Puedes redirigir al usuario o mostrar un mensaje de error
    // Por ejemplo, lo redirigimos a la página principal con un mensaje
    $_SESSION['error'] = "No tienes permisos para editar usuarios.";
    header('Location: ver_usuarios.php');
    exit();
}

    // Se hace la consulta para obtener los datos del usuario
    $stmt = $pdo->prepare("SELECT `contador_usuarios`, `tipo_doc`, `documento`, `nombre`, `apellido`, `fecha_nacimiento`, `celular`, `ciudad`, `direccion`, `rol`, `correo`  FROM `usuarios` WHERE contador_usuarios = :contador_usuarios AND estado = 'activo'");
    $stmt->bindParam(':contador_usuarios', $contador_usuarios, PDO::PARAM_INT);
    $stmt->execute();

    // Almacenar los datos del usuario
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die('No se encontró el usuario.');
    }
} else {
    echo "No se ha especificado el usuario a actualizar.";
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar usuario</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style/css/editar_usuario.css">
    <script src="js/menu.js"></script>
    <link rel="stylesheet" href="style/css/menu.css">
</head>
<body>
<aside class="aside active" id="aside">
        <div class="head">
            <div class="profile">
                <img src="style/images/logo_gestion.png" alt="Gestión de Inventario - Logo">
                <p id="logo-name">Pilidev</p>   
            </div>
            <i class='bx bx-menu' id="menu"></i>
        </div>
        <div class="options">
            <a id="" href="ver_ventas.php">
                <div>
                    <i class='bx bx-receipt'></i>
                    <span class="option">Ventas</span>
                </div>
            </a>

            <a id="" href="ver_productos.php">
                <div>
                    <i class='bx bx-package'></i>
                    <span class="option">Productos</span>
                </div>
            </a>

            <a id="" href="ver_categorias.php">
                <div>
                    <i class='bx bx-category-alt'></i>
                    <span class="option">Categorias</span>
                </div>
            </a>

            <a id="" href="ver_clientes.php">
                <div>
                    <i class='bx bx-group'></i>
                    <span class="option">Clientes</span>
                </div>
            </a>
            
            <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador') : ?>
                <a id="" href="ver_usuarios.php">
                    <div>
                        <i class='bx bx-user'></i>
                        <span class="option">Usuarios</span>
                    </div>
                </a>
            <?php endif; ?>

            <a class="links" href="logout.php">
                <div>
                    <i class='bx bx-log-out'></i>
                    <span class="option">Cerrar sesión</span>
                </div>
            </a>
        </div>     
    </aside>

    <main id="inicio">
        <section id="barra">
            <div id="atras">
                <a href="ver_usuarios.php">
                    <i class="lni lni-arrow-left"></i>
                </a>
                <h4>ACTUALIZAR DATOS DEL USUARIO</h4>
            </div>
        </section>
        <section id="datos">
            <form action="actualizar_usuario.php" method="POST" id="info_registro">
                
                <input type="hidden" name="codigo" value="<?= htmlspecialchars($usuario['contador_usuarios']) ?>">

                <div id="section_1" class="contenedores">
                    <div class="dato_user">
                        <div id="nombre" class="campos">
                            <p class="letras">Nombre</p>
                            <i class="lni lni-user"></i>
                            <input type="text" name="nombre" maxlength="25" minlength="2"
                            pattern="[a-zA-Z ]{2,25}" value="<?= htmlspecialchars($usuario['nombre']) ?>" autofocus="" tabindex="1">
                        </div>
                        <div id="apellido" class="campos">
                            <p class="letras">Apellido</p>
                            <input type="text" name="apellido" maxlength="25" minlength="2"
                            pattern="[a-zA-Z ]{2,25}" value="<?= htmlspecialchars($usuario['apellido']) ?>" tabindex="2">
                        </div>
                    </div>

                    <div class="dato_user">
                        <div id="num_doc" class="campos">
                            <label for="tipo_documento" class="letras">Tipo de documento</label>
                            <select name="tipo_documento" id="tipo_documento" class="campo_select" tabindex="3">
                                <option value="cedula de ciudadania" <?php if($usuario['tipo_doc'] == 'cedula de ciudadania') echo 'selected'; ?>>Cedula de ciudadania</option>
                                <option value="cedula de extranjeria" <?php if ($usuario['tipo_doc'] == 'cedula de extranjeria') echo 'selected'; ?>>Cedula de extranjeria</option>
                                <option value="pasaporte" <?php if ($usuario['tipo_doc'] == 'pasaporte') echo 'selected'; ?>>Pasaporte</option>
                                <option value="estatus de proteccion temporal (PPT)" <?php if ($usuario['tipo_doc'] == 'estatus de proteccion temporal (PPT)') echo 'selected'; ?>>Estatus de Proteccion Temporal (PPT)</option>
                            </select>
                        </div>
                        
                        <div id="num_doc" class="campos">
                            <p class="letras">Número de documento</p>
                            <i class="lni lni-postcard"></i>
                            <input type="text" name="documento" pattern="[0-9]{6,12}" maxlength="12" minlength="6" autocomplete="off" value="<?= htmlspecialchars($usuario['documento']) ?>" tabindex="3">
                        </div>
                    </div>

                    <div class="dato_user">
                        <div id="" class="campos">
                            <p class="letras">Ciudad</p>
                            <input type="text" name="ciudad" maxlength="50" minlength="3" pattern="[a-zA-Z ]{3,50}" value="<?= htmlspecialchars($usuario['ciudad']) ?>" >
                        </div>
                        <div id="" class="campos">
                            <p class="letras">Dirección</p>
                            <i class='bx bxs-map'></i>
                            <input type="text" name="direccion" maxlength="50" minlength="7" pattern="[a-zA-Z0-9#°\- ]{7,50}" value="<?= htmlspecialchars($usuario['direccion']) ?>" >
                        </div>
                    
                    </div>
                    <div class="dato_user">
                        <div id="rol" class="campos">
                            <p class="letras">Rol</p>
                            <i class="lni lni-users"></i>
                            <select name="rol" id="" tabindex="6">
                                <option value="">Selecciona tu rol</option>
                                <option value="administrador" <?php if ($usuario['rol'] == 'administrador') echo 'selected'; ?>>Administrador</option>
                                <option value="almacenista" <?php if ($usuario['rol'] == 'almacenista') echo 'selected'; ?>>Almacenista</option>
                                <option value="vendedor" <?php if ($usuario['rol'] == 'vendedor') echo 'selected'; ?>>Vendedor</option>
                            </select>
                        </div>
                        
                        <div id="fecha_nac" class="campos">
                            <p class="letras">Fecha de nacimiento</p>
                            <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($usuario['fecha_nacimiento']) ?>" tabindex="3">
                        </div>
                    </div>

                    <div class="dato_user">

                        <div id="num_ficha" class="campos">
                            <p class="letras">Celular</p>
                            <i class='bx bx-phone'></i>
                            <input type="tel" id="campo_ficha" name="celular" maxlength="10" minlength="10" pattern="[0-9]{10}" value="<?= htmlspecialchars($usuario['celular']) ?>" tabindex="5">
                        </div>
                        
                        <div id="" class="campos">
                            <p class="letras">Correo</p>
                            <i class="lni lni-envelope"></i>
                            <input type="email" name="correo" inputmode="email" maxlength="56" value="<?= htmlspecialchars($usuario['correo']) ?>" tabindex="4">
                        </div>

                    </div>
            
                        <div id="section_3">
                            <button type="submit" class="btn_actualizar">Actualizar</button>
                        </div>
                            
                </div>
                    
                    
            </form>

        </section>
        
    </main>
</body>
</html>