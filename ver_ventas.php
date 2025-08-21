<?php
require 'pdo.php';
require 'libs/fpdf/fpdf.php';

session_start();

require 'funciones.php';

// Verificar si el usuario ha iniciado sesión y si tiene rol permitido
$rolesPermitidos = ['administrador', 'vendedor', 'superadmin']; 

if (!isset($_SESSION['documento']) || !in_array($_SESSION['rol'], $rolesPermitidos)) {
    // Prevenir el almacenamiento en caché
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');

    // Redirigir al login
    header('Location: index.php');
    exit();
}


$estado = 'activo';
$stmt = $pdo->prepare('
     SELECT `cont_venta`, `total_venta`, `tiempo_registro` FROM `ventas` WHERE `estado` = :activo ORDER BY `cont_venta` DESC LIMIT 15;
');
$stmt->bindParam(':activo', $estado);
$stmt->execute();

$ventas = $stmt->fetchAll(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de productos</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet">
    <link rel="stylesheet" href="style/css/ver_ventas.css">
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
                <a href="home.php">
                    <i class='bx bx-home-heart'></i>
                </a>
                <h4>LISTADO DE VENTAS</h4>
            </div>  
            
            <a id="add_venta" href="carrito.html">
                <i class='bx bx-plus-circle'></i>
                <p>Nueva Venta</p>
            </a>

        </section>
        
        <section id="datos">
            <!-- Botón de descarga PDF -->
            <a href="generar_pdf.php" class="descargar-pdf">
                <button>Descargar PDF</button>
            </a>
            <!-- Mostrar el mensaje si existe -->
            <?php mostrar_mensaje(); ?>

            <?php if ($ventas): ?>
                <table id="datos_ventas">
                    <thead>
                        <tr>
                            <th>Recibo</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Info</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td><?= "RDV-" .htmlspecialchars($venta->cont_venta) ?></td> <!--  Aca se concatena el prefijo RDV de Factura de Venta, o el que se necesite -->
                                <td><?= htmlspecialchars($venta->tiempo_registro) ?></td>
                                <td>$<?= number_format($venta->total_venta, 0, ',', '.') ?></td>

                                <td id="icono_edit">
                                <?php if ($_SESSION['rol'] === 'administrador'): ?>
                                    <a href="editar_venta.php?cont_venta=<?= $venta->cont_venta ?>">
                                        <i class="lni lni-pencil"></i>
                                    </a>
                                <?php else: ?>
                                    <i class="lni lni-lock" title="No tienes permisos para editar"></i>
                                <?php endif; ?>
                                </td>
                               
                                <td id="icono_info">
                                    <a href="#" class="verMasVenta" data-id="<?= $venta->cont_venta ?>">
                                        <i class='bx bx-plus-circle'></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Aún no hay ventas registradas para ver.</p>
            <?php endif; ?>
        </section>

      <!-- Modal para mostrar detalles de la venta -->
       <div id="modalVenta">
            <div id="contenidoModal">
                <div class="modal-header">
                    <h3>Detalles de la Venta</h3>
                    <span id="cerrarModal">&times;</span>
                </div>
                
                <div class="venta-info">
                    <div class="info-grid">
                        <div class="info-group">
                            <label>Número de recibo</label>
                            <span id="cont_venta"></span>
                        </div>
                        
                        <div class="info-group">
                            <label>Método de Pago</label>
                            <span id="metodo_pago"></span>
                        </div>
                        
                        <div class="info-group">
                            <label>Cantidad Items</label>
                            <span id="total_cantidad_productos"></span>
                        </div>
                        
                        <div class="info-group">
                            <label>Fecha Venta</label>
                            <span id="detalle_tiempo_registro"></span>
                        </div>
                        
                        <div class="info-group">
                            <label>Vendedor</label>
                            <span id="documento_operador"></span>
                        </div>
                    </div>
                </div>

                <div class="productos-section">
                    <h4>Productos Vendidos</h4>
                    <div id="detalleVenta"></div>
                </div>

                <button id="btnSolicitarFactura" class="btn-submit">Enviar recibo</button>
            </div>
        </div>

        <!-- Modal para registrar los datos del cliente -->
        <div id="modalFactura">
            <div id="contenidoModalFactura">
                <div class="modal-header">
                    <h3>Datos del Cliente</h3>
                    <span id="cerrarModalFactura">&times;</span>
                </div>
                <form id="formFactura">
                    <input type="hidden" id="factura_cont_venta" name="cont_venta">
                    
                    <div class="form-group">
                        <label for="tipo_persona">Tipo de Persona</label>
                        <select name="tipo_persona" id="tipo_persona" required>
                            <option value="">Elige una opción</option>
                            <option value="natural">Natural</option>
                            <option value="juridica">Jurídica</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tipo_documento">Tipo de Documento</label>
                        <select name="tipo_documento" id="tipo_documento" required>
                            <option value="">Elige una opción</option>
                            <option value="NIT">NIT</option>
                            <option value="Cedula de Ciudadania">Cédula</option>
                            <option value="Cedula de Extranjeria">Cédula de extranjería</option>
                            <option value="Pasaporte">Pasaporte</option>
                            <option value="Estatus de Proteccion Temporal">Estatus de Proteccion Temporal</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="documento">Documento</label>
                        <input type="text" name="documento" id="documento" required>
                    </div>

                    <div class="form-group">
                        <label for="correo">Correo</label>
                        <input type="email" name="correo" id="correo" required>
                    </div>

                    <div class="form-group">
                        <label for="nom_comercial">Nombre Comercial <span class="optional">(Opcional)</span></label>
                        <input type="text" name="nom_comercial" id="nom_comercial">
                    </div>

                    <button type="submit" class="btn-submit">Enviar recibo</button>
                </form>
            </div>
        </div>
    </main>

<script src="js/ver_ventas.js"></script>
</body>
</html>