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
    SELECT `contador_creditos`, `num_credito`, `tiempo_registro` FROM `creditos` WHERE  `estado` = :activo 
    ORDER BY `contador_creditos` DESC
    LIMIT 15' );
$stmt->bindParam(':activo', $estado);
$stmt->execute();

$creditos = $stmt->fetchAll(PDO::FETCH_OBJ);
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
    <link href="https://cdn.lineicons.com/5.0/lineicons.css" rel="stylesheet">
    <script src="js/menu.js"></script>
    <link rel="stylesheet" href="style/css/menu.css">
    <link rel="stylesheet" href="style/css/ver_clientes.css">
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
                <a id="" href="ver_creditos.php" class="tooltip" data-tooltip="Creditos">
                    <div>
                        <i class='bx bx-wallet'></i>
                        <span class="option">Creditos</span>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador') : ?>
                <a id="" href="ver_abonos.php" class="tooltip" data-tooltip="Abonos">
                    <div>
                        <i class="lni lni-dollar-circle"></i>
                        <span class="option">Abonos</span>
                    </div>
                </a>
            <?php endif; ?>
            
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
                <h4>Listado de Creditos</h4>
            </div>  

            <a id="add_cliente" href="carrito.html">
                <i class='bx bx-plus-circle'></i>
                <p>Nuevo Credito</p>
            </a>
        </section>

        <section id="opciones">
            <div id="buscar_credito" class="campos">                
                <i class='bx bx-search-alt-2' ></i>
                <input type="text" id="buscarCredito" placeholder="Busca documento o factura..." autocomplete="off">
            </div>
        </section>
       
        
        <section id="datos">

            <!-- Mostrar el mensaje si existe -->
            <?php if (isset($_SESSION['mensaje'])): ?>
                <p><?= $_SESSION['mensaje']; ?></p>
                <?php unset($_SESSION['mensaje']); // Borrar mensaje ?>
            <?php endif; ?>

           

            <table id="datos_cliente">
                <thead>
                    <tr>
                        <th>Credito Número</th>
                        <th>Fecha/Credito</th>
                        <th>Editar</th>
                        <th>Eliminar</th>
                        <th>Info</th>
                        <th>Abonar</th>
                    </tr>
                </thead>
                <tbody id="tablaClientes">
                    <?php if ($creditos): ?>
                        <?php foreach ($creditos as $credito): ?>
                            <tr>
                                <td><?= htmlspecialchars($credito->num_credito) ?></td>
                                <td><?= htmlspecialchars($credito->tiempo_registro) ?></td>
                                <!-- <td>$<?= number_format($credito->valor_credito, 0, ',', '.') ?></td> -->

                                <td>
                                    <!-- Mostrar botones solo si el usuario tiene rol de administrador -->
                                    <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador'): ?>
                                        <!-- Botón para editar -->
                                        <a href="editar_credito.php?contador_creditos=<?= $credito->contador_creditos ?>">
                                            <i class="lni lni-pencil"></i>
                                        </a>
                                    <?php else: ?>
                                        <i class="lni lni-lock" title="No tienes permisos para editar"></i>
                                    <?php endif; ?>
                                    <td>
                                    <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador'): ?>
                                        <!-- Botón para eliminar -->
                                        <a href="eliminar_credito.php?contador_creditos=<?= htmlspecialchars($credito->contador_creditos) ?>" 
                                        title="Eliminar" 
                                        onclick="return confirm('¿Estás seguro de que deseas eliminar este credito?');">
                                            <i class="lni lni-trash-can"></i>
                                        </a>
                                    <?php else: ?>
                                        <i class="lni lni-lock" title="No tienes permisos para editar"></i>
                                    <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <a href="#" class="verMasCredito" data-id="<?= $credito->contador_creditos ?>">
                                            <i class='bx bx-plus-circle'></i>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="abonar_credito.php?contador_creditos=<?= $credito->contador_creditos ?>" class="abonarCredito">
                                            <i class="lni lni-dollar-circle"></i>
                                        </a>
                                    </td>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">Aun no hay creditos para mostrar</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- Modal para mostrar detalles del credito -->
        <div id="modalCredito" style="display:none;">
            <div id="contenidoModal">
                <span id="cerrarModal">&times;</span>
                <h3>Detalles del Credito</h3>
                <p><strong>Nombre Cliente:</strong> <span id="nombre"></span></p>
                <p><strong>Tipo de Persona:</strong> <span id="tipo_persona"></span></p>
                <p><strong>Tipo de Documento:</strong> <span id="tipo_documento"></span></p>
                <p><strong>Documento:</strong> <span id="documento"></span></p>
                <p><strong>Nombre Comercial:</strong> <span id="nom_comercial"></span></p>
                <p><strong>Apellido:</strong> <span id="apellido"></span></p>
                <p><strong>Correo:</strong> <span id="correo"></span></p>
                <p><strong>Ciudad:</strong> <span id="ciudad"></span></p>
                <p><strong>Dirección:</strong> <span id="direccion"></span></p>
                <p><strong>Plazo:</strong> <span id="plazo_credito"></span></p>
                <p><strong>Valor/Credito:</strong> <span id="valor_credito"></span></p>
                <p><strong>Abonos</strong> <span id="abonos"></span></p>
                <p><strong>Saldo:</strong> <span id="saldo"></span></p>
                <p><strong>Observaciones:</strong> <span id="observaciones"></span></p>
            </div>
        </div>
    </main>

<script src="js/funciones_ver_clientes.js"></script>
</body>
</html>