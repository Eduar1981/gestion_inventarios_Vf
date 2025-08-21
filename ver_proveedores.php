<?php
require 'pdo.php';
session_start();

if (!isset($_SESSION['documento'])) {
    header('Location: index.php');
    exit();
}

// Obtener el rol del usuario autenticado
$rol_usuario = $_SESSION['rol'];

$estado = 'activo';
$stmt = $pdo->prepare('
    SELECT `cont_provee`, CONCAT(`nom_representante`," ", `ape_representante`) AS nombre, `celular`, `correo` FROM `proveedores` WHERE `estado` = :activo 
    ORDER BY `cont_provee` DESC
    LIMIT 15' );
$stmt->bindParam(':activo', $estado);
$stmt->execute();

$proveedores = $stmt->fetchAll(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="js/menu.js"></script>
    <link rel="stylesheet" href="style/css/menu.css">
    <link rel="stylesheet" href="style/css/ver_proveedores.css">
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
                <a href="home.php">
                    <i class='bx bx-home-heart'></i>
                </a>
                <h4>Listado de Proveedores</h4>
            </div>  
            
             <a id="add_proveedor" href="registrar_proveedor.php">
                <p>Registrar Proveedor</p>
            </a>
        </section>
        
        <section id="datos">

            <!-- Mostrar el mensaje si existe -->
            <?php if (isset($_SESSION['mensaje'])): ?>
                <p><?= $_SESSION['mensaje']; ?></p>
                <?php unset($_SESSION['mensaje']); // Borrar mensaje ?>
            <?php endif; ?>

            <input type="text" id="buscarProveedor" placeholder="Busca documento o nombre..." autocomplete="off">

            <table id="datos_proveedor">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Celular</th>
                        <th>Correo</th>
                        <th>Editar</th>
                        <th>Eliminar</th>
                        <th>Info</th>
                    </tr>
                </thead>
                <tbody id="tablaProveedores">
                    <?php if ($proveedores): ?>
                        <?php foreach ($proveedores as $proveedor): ?>
                            <tr>
                                <td><?= htmlspecialchars($proveedor->nombre) ?></td>
                                <td><?= htmlspecialchars($proveedor->celular) ?></td>
                                <td><?= htmlspecialchars($proveedor->correo) ?></td>
                                
                                
                                <td>
                                    <!-- Mostrar botones solo si el usuario tiene rol de administrador -->
                                    <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador'): ?>
                                        <!-- Botón para editar -->
                                        <a href="editar_proveedor.php?cont_provee=<?= $proveedor->cont_provee ?>">
                                            <i class="lni lni-pencil"></i>
                                        </a>
                                    <?php else: ?>
                                        <i class="lni lni-lock" title="No tienes permisos para editar"></i>
                                    <?php endif; ?>
                                    <td>
                                    <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador'): ?>
                                        <!-- Botón para eliminar -->
                                        <a href="eliminar_proveedor.php?cont_provee=<?= htmlspecialchars($proveedor->cont_provee) ?>" 
                                        title="Eliminar" 
                                        onclick="return confirm('¿Estás seguro de que deseas eliminar este proveedor?');">
                                            <i class="lni lni-trash-can"></i>
                                        </a>
                                    <?php else: ?>
                                        <i class="lni lni-lock" title="No tienes permisos para editar"></i>
                                    <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <a href="#" class="verMasProveedor" data-id="<?= $proveedor->cont_provee ?>">
                                            <i class='bx bx-plus-circle'></i>
                                        </a>
                                    </td>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Aun no hay proveedores para mostrar</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- Modal para mostrar detalles del proveedor -->
        <div id="modalProveedor" style="display:none;">
            <div id="contenidoModal">
                <span id="cerrarModal">&times;</span>
                <h3>Detalles del Proveedor</h3>
                <p><strong>Nombre Comercial:</strong> <span id="nom_comercial"></span></p>
                <p><strong>Tipo de Persona:</strong> <span id="tipo_persona"></span></p>
                <p><strong>Tipo de Documento:</strong> <span id="tipo_documento"></span></p>
                <p><strong>Documento:</strong> <span id="doc_proveedor"></span></p>
                <p><strong>Telefono Fijo:</strong> <span id="tel_fijo"></span></p>
                <p><strong>Ciudad:</strong> <span id="ciudad"></span></p>
                <p><strong>Dirección:</strong> <span id="direccion"></span></p>
            </div>
        </div>
    </main>

<script src="js/funciones_ver_proveedores.js"></script>
</body>
</html>