<?php
// Incluir el archivo de conexión PDO
require 'pdo.php';

session_start();

// Verificar si el contador_categoria está presente en la URL
if (isset($_GET['contador_categoria'])) {
    $contador_categoria = $_GET['contador_categoria'];

    // Verificar si el usuario tiene permisos de administrador
    if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'superadmin' && $_SESSION['rol'] !== 'administrador')) {
        $_SESSION['error'] = "No tienes permisos para editar categorías.";
        header('Location: ver_categorias.php');
        exit();
    }

    // Hacer la consulta para obtener los datos de la categoría
    $stmt = $pdo->prepare("SELECT `contador_categoria`, `codigo`, `nombre` FROM `categorias` WHERE contador_categoria = :contador_categoria AND estado = 'activo'");
    $stmt->bindParam(':contador_categoria', $contador_categoria, PDO::PARAM_INT);
    $stmt->execute();

    // Almacenar los datos de la categoría
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$categoria) {
        die('No se encontró la categoría.');
    }
    
} else {
    echo "No se ha especificado la categoría a actualizar.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Categoría</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="js/menu.js"></script>
    <link rel="stylesheet" href="style/css/menu.css">
    <link rel="stylesheet" href="style/css/editar_categoria.css">
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
                    <i class='bx bxs-user-account'></i>
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
                <a href="ver_categorias.php">
                    <i class='bx bx-left-arrow-alt'></i>
                </a>
                <h4>EDITAR CATEGORIA</h4>
            </div>
        </section>
        
        <form action="actualizar_categoria.php" method="POST" id="editar_cat">
            <!-- Campo oculto para el contador de la categoría -->
            <input type="hidden" name="contador_categoria" value="<?= htmlspecialchars($categoria['contador_categoria']) ?>">

            <div id="section_1" class="contenedores">

                <!-- Campo para el código de la categoría -->
                <div id="" class="campos">
                    <p class="letras">Código</p>
                    <i class="lni lni-user"></i>
                    <input type="text" name="codigo" maxlength="25" minlength="2"
                           pattern="[a-zA-Z0-9#\- ]{2,25}" value="<?= htmlspecialchars($categoria['codigo']) ?>">
                </div>

                <!-- Campo para el nombre de la categoría -->
                <div id="nombre" class="campos">
                    <p class="letras">Nombre Categoría</p>
                    <i class="lni lni-tag"></i>
                    <input type="text" name="nombre" maxlength="25" minlength="2"
                           pattern="[a-zA-Z ]{2,25}" value="<?= htmlspecialchars($categoria['nombre']) ?>">
                </div>

                <!-- Botón para enviar el formulario -->
                <button id="editar"  type="submit" class="btn_editar">Actualizar</button>
            </div>
        </form>
    </main>
</body>
</html>
