<?php
// Conexión con la DB
require 'pdo.php';

session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['documento'])) {
    header('Location: index.php');
    exit();
}

// Obtener el rol del usuario autenticado
$rol_usuario = $_SESSION['rol'];

$estado = 'activo'; // Solo mostrar usuarios activos
$usuarios = []; // Inicializamos la variable para evitar errores

// Si el usuario es "superadmin", puede ver todos los usuarios
if ($rol_usuario === 'superadmin') {
    $stmt = $pdo->prepare("SELECT contador_usuarios, CONCAT(nombre, ' ', apellido) AS nombre, rol FROM usuarios WHERE estado = :activo");
} 
// Si es "administrador", solo puede ver "administrador", "vendedor" y "almacenista"
else if ($rol_usuario === 'administrador') {
    $stmt = $pdo->prepare("SELECT contador_usuarios, nombre, apellido, rol 
                           FROM usuarios 
                           WHERE estado = :activo 
                           AND rol IN ('administrador', 'vendedor', 'almacenista')");
} 

// Solo ejecutar la consulta si $stmt fue definido
if (isset($stmt)) {
    $stmt->bindParam(':activo', $estado);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_OBJ);
}


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
    <link rel="stylesheet" href="style/css/ver_usuarios.css">
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
                <h4>LISTADO DE USUARIOS</h4>
            </div>  
        </section>

        <section id="opciones">
            <div id="buscar_user" class="campos">                
                <i class='bx bx-search-alt-2' ></i>
                <input type="text" id="buscarUsuario" placeholder="Busca documento o nombre..." autocomplete="off">
            </div>
        </section>

        <section id="datos">
            <table id="datos_usuario">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        
                        <th>Rol</th>
                        <th>Editar</th>
                        <th>Eliminar</th>
                        <th>Info</th>
                    </tr>
                </thead>
                <tbody id="tablaUsuarios">
                    <?php if ($usuarios): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario->nombre) ?></td>
                                
                                <td><?= htmlspecialchars($usuario->rol) ?></td>
                                
                                
                                <td>
                                    <!-- Mostrar botones solo si el usuario tiene rol de administrador -->
                                    <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador'): ?>
                                        <!-- Botón para editar -->
                                        <a href="editar_usuario.php?contador_usuarios=<?= $usuario->contador_usuarios ?>">
                                            <i class="lni lni-pencil"></i>
                                        </a>
                                    <?php else: ?>
                                        <i class="lni lni-lock" title="No tienes permisos para editar"></i>
                                    <?php endif; ?>
                                    <td>
                                    <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador'): ?>
                                        <!-- Botón para eliminar -->
                                        <a href="eliminar_usuario.php?contador_usuarios=<?= htmlspecialchars($usuario->contador_usuarios) ?>" 
                                        title="Eliminar" 
                                        onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                            <i class="lni lni-trash-can"></i>
                                        </a>
                                    <?php else: ?>
                                        <i class="lni lni-lock" title="No tienes permisos para editar"></i>
                                    <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <a href="#" class="verMasUsuario" data-id="<?= $usuario->contador_usuarios ?>">
                                            <i class='bx bx-plus-circle'></i>
                                        </a>
                                    </td>

                                        
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No hay usuarios para mostrar</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

         <!-- Modal para mostrar detalles del usuario -->
         <div id="modalUsuario" style="display:none;">
            <div id="contenidoModal">
                <span id="cerrarModal">&times;</span>
                <h3>Detalles del Usuario</h3>
                <p>Tipo de Documento: <span id="tipo_doc"></span></p>
                <p>Documento: <span id="documento"></span></p>
                <p>Nombre: <span id="nombre"></span></p>
                <p>Apellido: <span id="apellido"></span></p>
                <p>Fecha de Nacimiento: <span id="fecha_nacimiento"></span></p>
                <p>Correo: <span id="correo"></span></p>
                <p>Celular: <span id="celular"></span></p>
                <p>Dirección: <span id="direccion"></span></p>
                <p>Ciudad: <span id="ciudad"></span></p>
            </div>
        </div>
    </main>

<script>
  

</script>
<script src="js/funciones_ver_usuarios.js"></script>
</body>
</html>