<?php
//Parametros de conexion con la DB por medio del pdo
require 'pdo.php';

session_start();

// Verifica que el contador_clientes del cliente esté presente en la URL
if (isset($_GET['contador_clientes'])) {
    $contador_clientes = $_GET['contador_clientes'];

// Verifica si el usuario tiene permisos de administrador
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'superadmin' && $_SESSION['rol'] !== 'administrador')) {
    // Puedes redirigir al usuario o mostrar un mensaje de error
    // Por ejemplo, lo redirigimos a la página principal con un mensaje
    $_SESSION['error'] = "No tienes permisos para editar clientes.";
    header('Location: ver_clientes.php');
    exit();
}

    // Se hace la consulta para obtener los datos del cliente
    $stmt = $pdo->prepare("SELECT `contador_clientes`, `tipo_persona`, `tipo_documento`, `documento`, `nombre`, `apellido`, `fecha_nacimiento`, `celular`, `correo`, `ciudad`, `direccion`, `nom_comercial` FROM `clientes` WHERE contador_clientes = :contador_clientes AND estado = 'activo'");
    $stmt->bindParam(':contador_clientes', $contador_clientes, PDO::PARAM_INT);
    $stmt->execute();

    // Almacenar los datos del usuario
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        die('No se encontró el cliente.');
    }
    
} else {
    echo "No se ha especificado el cliente a actualizar.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style/css/editar_cliente.css">
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
            <a id="" href="ver_ventas.php" class="tooltip" data-tooltip="Ventas">
                <div>
                    <i class='bx bx-receipt'></i>
                    <span class="option">Ventas</span>
                </div>
            </a>

            <a id="" href="ver_productos.php" class="tooltip" data-tooltip="Productos">
                <div>
                    <i class='bx bx-package'></i>
                    <span class="option">Productos</span>
                </div>
            </a>

            <a id="" href="ver_categorias.php" class="tooltip" data-tooltip="Categorias">
                <div>
                    <i class='bx bx-category-alt'></i>
                    <span class="option">Categorias</span>
                </div>
            </a>

            <a id="" href="ver_clientes.php" class="tooltip" data-tooltip="Clientes">
                <div>
                    <i class='bx bx-group'></i>
                    <span class="option">Clientes</span>
                </div>
            </a>
            
            <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador') : ?>
                <a id="" href="ver_proveedores.php" class="tooltip" data-tooltip="Proveedores">
                    <div>
                        <i class='bx bxs-truck'></i>
                        <span class="option">Proveedores</span>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador') : ?>
                <a id="" href="ver_compras_proveedores.php" class="tooltip" data-tooltip="Compras">
                    <div>
                        <i class='bx bxs-package'></i>
                        <span class="option">Compras Porveedores</span>
                    </div>
                </a>
            <?php endif; ?>
            
            <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador') : ?>
                <a id="" href="ver_usuarios.php" class="tooltip" data-tooltip="Usuarios">
                    <div>
                        <i class='bx bx-user'></i>
                        <span class="option">Usuarios</span>
                    </div>
                </a>
            <?php endif; ?>

            <a class="links tooltip" href="logout.php"  data-tooltip="Cerrar sesión">
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
                <a href="ver_clientes.php">
                    <i class="lni lni-arrow-left"></i>
                </a>
                <h4>ACTUALIZAR DATOS DEL CLIENTE</h4>
            </div>
        </section>

     
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mensaje error">
                <i class="lni lni-warning"></i> <?= $_SESSION['error']; ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <section id="datos">   
            <form action="actualizar_cliente.php" method="POST" id="info_registro">
                
                <input type="hidden" name="contador_clientes" value="<?= htmlspecialchars($cliente['contador_clientes']) ?>">
                

                <div id="section_1" class="contenedores">
                    <div class="dato_user">
                        <div id="" class="campos">
                            <p class="letras">Tipo de Persona</p>
                            <select name="tipo_persona" id="" tabindex="1" class="campo_select">
                                <option value="">Tipo de persona</option>
                                <option value="Juridica" <?php if ($cliente['tipo_persona'] == 'Juridica') echo 'selected'; ?>>Juridica</option>
                                <option value="Natural" <?php if ($cliente['tipo_persona'] == 'Natural') echo 'selected'; ?>>Natural</option>
                            </select>
                        </div>

                        <div id="" class="campos">
                        <p class="letras">Tipo docuemnto</p>
                            <select name="tipo_documento" id="tipo_documento" class="campo_select" tabindex="2">
                                <option value="">Tipo documento</option>
                                <option value="nit" <?php if($cliente['tipo_documento'] == 'nit') echo 'selected'; ?>>NIT</option>
                                <option value="cedula de ciudadania" <?php if($cliente['tipo_documento'] == 'cedula de ciudadania') echo 'selected'; ?>>Cedula de ciudadania</option>
                                <option value="cedula de extranjeria" <?php if ($cliente['tipo_documento'] == 'cedula de extranjeria') echo 'selected'; ?>>Cedula de extranjeria</option>
                                <option value="pasaporte" <?php if ($cliente['tipo_documento'] == 'pasaporte') echo 'selected'; ?>>Pasaporte</option>
                                <option value="estatus de proteccion temporal (PPT)" <?php if ($cliente['tipo_documento'] == 'estatus de proteccion temporal (PPT)') echo 'selected'; ?>>Estatus de Proteccion Temporal (PPT)</option>
                            </select>
                        </div>
                    </div>

                    <div class="dato_user">
                        <div id="" class="campos">
                            <p class="letras">Nombre Comercial</p>
                            <i class="lni lni-user"></i>
                            <input type="text" name="nom_comercial" maxlength="25" minlength="2"
                            pattern="[a-zA-Z ]{2,25}" value="<?= htmlspecialchars($cliente['nom_comercial']) ?>" autofocus="" tabindex="1">
                        </div>

                        <div id="" class="campos">
                            <p class="letras">Número de documento</p>
                            <i class="lni lni-postcard"></i>
                            <input type="text" name="documento" value="<?= htmlspecialchars($cliente['documento']) ?>" readonly disabled>
                        </div>

                    </div>
                    <div class="dato_user">
                        <div id="nombre" class="campos">
                            <p class="letras">Nombre</p>
                            <i class="lni lni-user"></i>
                            <input type="text" name="nombre" maxlength="25" minlength="2"
                            pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,25}" value="<?= htmlspecialchars($cliente['nombre']) ?>" autofocus="" tabindex="4">
                        </div>
                        
                        <div id="apellido" class="campos">
                            <p class="letras">Apellido</p>
                            <input type="text" name="apellido" maxlength="25" minlength="2"
                            pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,25}" value="<?= htmlspecialchars($cliente['apellido']) ?>" tabindex="5">
                        </div>
                    </div>

                    <div class="dato_user">
                        <div id="num_ficha" class="campos">
                            <p class="letras">Celular</p>
                            <i class='bx bx-hash'></i>
                            <input type="tel" id="" name="celular" maxlength="10" minlength="10" pattern="[0-9]{10}" value="<?= htmlspecialchars($cliente['celular']) ?>" tabindex="5">
                        </div>
                        
                        <div id="" class="campos">
                            <p class="letras">Correo</p>
                            <i class="lni lni-envelope"></i>
                            <input type="email" name="correo" inputmode="email" maxlength="56" value="<?= htmlspecialchars($cliente['correo']) ?>" tabindex="4">
                        </div>
                    </div>

                    <div class="dato_user">


                        <div id="fecha_nacimiento" class="campos">
                            <p class="letras">Fecha de Nacimiento</p>
                            <input type="date" name="fecha_nacimiento" 
                            value="<?= htmlspecialchars($cliente['fecha_nacimiento']) ?>" tabindex="6">
                        </div>
                    </div>
                    

                    <div class="dato_user">
                        <div id="" class="campos">
                            <p class="letras">Ciudad</p>
                            <i class="lni lni-certificate"></i>
                            <input type="text" name="ciudad" maxlength="50" minlength="3" pattern="[a-zA-Z ]{3,50}" value="<?= htmlspecialchars($cliente['ciudad']) ?>" >
                        </div>
                        <div id="" class="campos">
                            <p class="letras">Dirección</p>
                            <i class="lni lni-certificate"></i>
                            <input type="text" name="direccion" maxlength="50" minlength="7" pattern="[a-zA-Z0-9#°\- ]{7,50}" value="<?= htmlspecialchars($cliente['direccion']) ?>" >
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