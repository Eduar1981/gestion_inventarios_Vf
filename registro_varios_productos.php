<?php
require 'pdo.php';
session_start();
date_default_timezone_set('America/Bogota');

// Seguridad
if (!isset($_SESSION['documento']) || !in_array($_SESSION['rol'], ['superadmin', 'administrador'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productos = count($_POST['codigo_producto']);
    $doc_proveedor = $_POST['doc_proveedor'][0];
    $num_fact_comp = $_POST['num_fact_comp'];
    $fecha_compra = $_POST['fecha_factura'] ?? date('Y-m-d');
    $fecha_pago_fact_comp = $_POST['fecha_pago_fact_comp'] ?? date('Y-m-d');
    $documento_operador = $_SESSION['documento'];
    $estado = 'activo';
    $tiempo_registro = date('Y-m-d H:i:s');

    // Verificar si ya existe la factura
    $verificarFactura = $pdo->prepare("SELECT COUNT(*) FROM factura_compra_proveedores WHERE num_fact_comp = ? AND doc_proveedor = ?");
    $verificarFactura->execute([$num_fact_comp, $doc_proveedor]);
    $existeFactura = $verificarFactura->fetchColumn();

    if ($existeFactura > 0) {
        $_SESSION['mensaje'] = "<p style='color: red;'>Ya existe una factura registrada con ese número y proveedor.</p>";
        header('Location: registro_varios_productos.php');
        exit();
    }

    // Calcular el precio_compra_total sumando todos los productos
    $precio_compra_total = 0;

    for ($i = 0; $i < $productos; $i++) {
        $precio_unitario = floatval($_POST['precio_compra'][$i]); // Precio de compra del producto
        $cantidad = intval($_POST['cantidad'][$i]); // Cantidad del producto
        $subtotal = $precio_unitario * $cantidad; // Subtotal del producto
        $precio_compra_total += $subtotal; // Sumar al total
    }

    /* echo '<pre>';
    print_r($_POST);
    echo 'fecha_pago_fact_comp: ';
    var_dump($_POST['fecha_pago_fact_comp']);
    exit(); */


    try {
        // Insertar la factura primero
        $insertFactura = $pdo->prepare("INSERT INTO factura_compra_proveedores (
            num_fact_comp, fecha_compra, fecha_pago_fact_comp, doc_proveedor, precio_compra_total, tiempo_registro, documento_operador, estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $insertFactura->execute([
            $num_fact_comp, $fecha_compra, 
            $fecha_pago_fact_comp,
            $doc_proveedor,
            $precio_compra_total, 
            $tiempo_registro, 
            $documento_operador, 
            $estado
        ]);

        // Insertar productos asociados a esa factura
        for ($i = 0; $i < $productos; $i++) {
            $codigo_producto = $_POST['codigo_producto'][$i];
            $referencia = $_POST['referencia'][$i];
            $nombre = $_POST['nombre'][$i];
            $descripcion = $_POST['descripcion'][$i];
            $categoria = $_POST['contador_categoria'][$i];
            $precio_compra = $_POST['precio_compra'][$i];
            $con_iva = isset($_POST['con_iva'][$i]) ? 1 : 0;
            $porcentaje_ganancia = $_POST['porcentaje_ganancia'][$i];
            $precio_venta = isset($_POST['precio_venta'][$i]) ? intval(str_replace(["$", ",", " "], "", $_POST['precio_venta'][$i])) : 0;
            $cantidad = $_POST['cantidad'][$i];
            $cantidad_minima = $_POST['cantidad_minima'][$i];

            // Validar duplicados de producto
            $checkProducto = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE codigo_producto = ? OR referencia = ?");
            $checkProducto->execute([$codigo_producto, $referencia]);
            if ($checkProducto->fetchColumn() > 0) {
                $_SESSION['mensaje'] = "<p style='color: red;'>Ya existe un producto con el código o referencia: $codigo_producto o $referencia.</p>";
                header('Location: registro_varios_productos.php');
                exit();
            }

            $insertProducto = $pdo->prepare("INSERT INTO productos (
                codigo_producto, num_fact_comp, referencia, nombre, descripcion, categoria,
                precio_compra, con_iva, porcentaje_ganancia, precio_venta, cantidad,
                cantidad_minima, tiempo_registro, documento_operador, estado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $insertProducto->execute([
                $codigo_producto, $num_fact_comp, $referencia, $nombre, $descripcion, $categoria,
                $precio_compra, $con_iva, $porcentaje_ganancia, $precio_venta, $cantidad,
                $cantidad_minima, $tiempo_registro, $documento_operador, $estado
            ]);
        }

        $_SESSION['mensaje'] = "Productos y factura registrados exitosamente.";
        header('Location: ver_productos.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['mensaje'] = "<p style='color: red;'>Error al registrar: " . $e->getMessage() . "</p>";
        header('Location: registro_varios_productos.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="js/menu.js"></script>
    <link rel="stylesheet" href="style/css/registro_varios_productos.css">
    <link rel="stylesheet" href="style/css/menu.css">
</head>
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
            <a id="" href="ver_ventas.php" class="my-tooltip" data-tooltip="Ventas">
                <div>
                    <i class='bx bx-receipt'></i>
                    <span class="option">Ventas</span>
                </div>
            </a>

            <a id="" href="ver_productos.php" class="my-tooltip" data-tooltip="Productos">
                <div>
                    <i class='bx bx-package'></i>
                    <span class="option">Productos</span>
                </div>
            </a>

            <a id="" href="ver_categorias.php" class="my-tooltip" data-tooltip="Categorias">
                <div>
                    <i class='bx bx-category-alt'></i>
                    <span class="option">Categorias</span>
                </div>
            </a>

            <a id="" href="ver_clientes.php" class="my-tooltip" data-tooltip="Clientes">
                <div>
                    <i class='bx bx-group'></i>
                    <span class="option">Clientes</span>
                </div>
            </a>

            <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador') : ?>
                <a id="" href="ver_compras_proveedores.php" class="my-tooltip" data-tooltip="Compras">
                    <div>
                        <i class='bx bxs-package'></i>
                        <span class="option">Compras Porveedores</span>
                    </div>
                </a>
            <?php endif; ?>

            <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador') : ?>
                <a id="" href="ver_proveedores.php" class="my-tooltip" data-tooltip="Proveedores">
                    <div>
                        <i class='bx bxs-truck'></i>
                        <span class="option">Proveedores</span>
                    </div>
                </a>
            <?php endif; ?>
            
            <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador') : ?>
                <a id="" href="ver_usuarios.php" class="my-tooltip" data-tooltip="Usuarios">
                    <div>
                        <i class='bx bx-user'></i>
                        <span class="option">Usuarios</span>
                    </div>
                </a>
            <?php endif; ?>

            <a class="links my-tooltip" href="logout.php"  data-tooltip="Cerrar sesión">
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
                <a href="ver_productos.php">
                    <i class='bx bx-left-arrow-alt'></i>
                </a>
                <h4>REGISTRAR PRODUCTOS</h4>
            </div>  
        </section>

        <section id="datos">
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div id="mensaje-alerta">
                <?= $_SESSION['mensaje']; ?>
                <?php unset($_SESSION['mensaje']); // Borrar el mensaje después de mostrarlo ?>
            </div>
        <?php endif; ?>

        <form id="registro_prod" action="registro_varios_productos.php" method="POST">
            <input type="hidden" name="accion" id="accion" value="registrar_producto">

                <div id="productos">

                <div class="producto" id="producto_1">
                    <h3 id="num_producto">Producto 1</h3>

                    <div class="campos">
                        <label for="fecha_factura">Fecha de la Factura</label>
                        <input type="date" name="fecha_factura" id="fecha_factura" required>
                    </div>

                    <div class="campos">
                        <label for="fecha_pago_fact_comp">Fecha pago de Factura</label>
                        <input type="date" name="fecha_pago_fact_comp" id="fecha_pago_fact_comp" required>
                    </div>

                    <div class="campos">
                        <input 
                            type="text" 
                            name="num_fact_comp" 
                            name="num_fact_comp[]"
                            id="num_fact_comp" 
                            placeholder="Número Factura Compra" 
                            required>
                    </div>

                    <div class="campos">
                        <input 
                            type="text" 
                            name="proveedor_global" 
                            id="proveedor_global" 
                            placeholder="Buscar proveedor..." 
                            required 
                            oninput="buscarProveedores('proveedor_global', 'sugerencias_proveedor_global')"
                        />
                        <input type="hidden" id="proveedor_global-hidden" name="doc_proveedor[]" />
                        <div id="sugerencias_proveedor_global" class="sugerencias_proveedor"></div>

                            <button type="button" id="new_proveedor" data-bs-toggle="modal" data-bs-target="#nuevoProveedorModal"> Nuevo Proveedor</button>
                    </div>

                    <div class="campos">
                        <input type="text" name="codigo_producto[]" required placeholder="Código">

                        <input type="text" name="referencia[]" required placeholder="Referencia">
                    </div>

                    <div class="campos">
                        <input type="text" name="nombre[]" required placeholder="Nombre">

                        <textarea name="descripcion[]" placeholder="Descripción"></textarea>
                    </div>

                    <div class="campos" id="campo_categoria">
                        <input type="text" 
                            name="categoria[]" 
                            class="campo_categoria" 
                            id="categoria_1"
                            placeholder="Busca la categoría..."
                            required
                            oninput="buscarCategorias('categoria_1', 'sugerencias_categoria_1')">

                            <input type="hidden" id="categoria_1-hidden" name="contador_categoria[]" value="" />

                        <div id="sugerencias_categoria_1" class="sugerencias"></div>
                            <button type="button" id="new_categoria" data-bs-toggle="modal" data-bs-target="#nuevaCategoriaModal"> Nueva Categoría</button>
                    </div>

                    <div class="campos">
                        <input type="text" step="0.01" name="precio_compra[]" placeholder="$ compra" class="precio_compra" oninput="formatearPrecio(this)" />

                        <div id="campo_iva">
                            <label for="con_iva[]">IVA:</label>
                            <input type="checkbox" name="con_iva[]" class="con_iva">
                        </div>
            
                        <!-- <label for="porcentaje_ganancia[]">Ganancia:</label> -->
                        <input type="number" step="0.01" name="porcentaje_ganancia[]" class="porcentaje_ganancia" placeholder="% ganancia">
                        
                    </div>
                    
                    <div class="campos">
                        <input type="text" step="0.01" name="precio_venta[]" class="precio_venta" required placeholder="Precio sugerido">
                    </div>

                    <div class="campos">
                        <input type="number" name="cantidad[]" required placeholder="Cantidad">

                        <input type="number" name="cantidad_minima[]" required placeholder="Cantidad mínima">
                    </div>
                </div>

                <div id="botones_html">
                    <button id="agregar_producto"><i class='bx bx-plus-circle'></i>Nuevo producto</button>
                    <button type="submit" id="registrar_productos">Registrar</button>
                </div>

            </form>
            
        </section>
    </main>

    <!-- Modal para Registrar Nueva Categoría -->
    <div class="modal fade" id="nuevaCategoriaModal" tabindex="-1" aria-labelledby="nuevaCategoriaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nuevaCategoriaModalLabel">Registrar Nueva Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form action="registrar_nueva_categoria.php" method="POST">
                        <div class="mb-3">
                            <label for="codigoCategoria" class="form-label">Código</label>
                            <input type="text" name="codigo" class="form-control" maxlength="25" minlength="2"  placeholder="Código" required>
                        </div>
                        <div class="mb-3">
                            <label for="nombreCategoria" class="form-label">Nombre Categoría</label>
                            <input type="text" name="nombre" class="form-control" maxlength="25" minlength="2"  placeholder="Nombre" required>
                        </div>
                        <button type="submit" class="btn btn-primary" id="registrar_categoria_ajax">Registrar Categoría</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal para Registrar Nuevo Proveedor -->
    <div class="modal fade" id="nuevoProveedorModal" tabindex="-1" aria-labelledby="nuevoProveedorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nuevoProveedorModalLabel">Registrar Nuevo Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">

                    <form action="registrar_nuevo_proveedor.php" method="POST">

                        <div class="mb-3">
                            <i class="lni lni-user"></i>
                            <input type="text" name="nom_comercial" maxlength="50" minlength="2"
                            pattern="[a-zA-Z ]{2,25}" placeholder="Nombre Comercial" autofocus="" tabindex="1" required>
                        </div>

                        <div class="mb-3">
                            <label for="tipo_persona">Tipo de persona</label>
                            <select name="tipo_persona" id="tipo_persona" class="campo_select" tabindex="2">
                                <option value="">Tipo Persona</option>
                                <option value="Natural">Natural</option>
                                <option value="Juridica">Juridica</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <i class="lni lni-user"></i>
                            <input type="text" name="nom_representante" maxlength="25" minlength="2"
                            pattern="[a-zA-Z ]{2,25}" placeholder="Nombre (s)"  tabindex="3" required>
                        </div>

                        <div class="mb-3">
                            <!-- <p>Apellido</p> -->
                            <input type="text" name="ape_representante" maxlength="25" minlength="2"
                            pattern="[a-zA-Z ]{2,25}" placeholder="Apellido (s)" tabindex="4" required>
                        </div>

                        <div class="mb-3">
                            <select name="tipo_documento" id="tipo_documento" class="campo_select" tabindex="5">
                                <option value="">Tipo de documento</option>
                                <option value="NIT">NIT</option>
                                <option value="Cedula de Ciudadania">Cédula de ciudadanía</option>
                                <option value="Cedula de Extranjeria">Cédula de extranjería</option>
                                <option value="Pasaporte">Pasaporte</option>
                                <option value="Estatus de Proteccion Temporal">Estatus de protección temporal (PPT)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <i class="lni lni-postcard"></i>
                            <input type="text" name="documento" pattern="[0-9]{6,12}" maxlength="12" minlength="6" autocomplete="off" placeholder="Documento" tabindex="6" required>
                        </div>

                        <div class="mb-3">
                            <!-- <p>ciudad</p> -->
                            <input type="text" name="ciudad" maxlength="25" minlength="2"
                            pattern="[a-zA-Z ]{2,56}" placeholder="Ciudad" tabindex="7" required>
                        </div>

                        <div class="mb-3">
                            <input type="text" name="direccion" maxlength="60" minlength="10"
                            pattern="[a-zA-Z0-9#\- ]{10,60}" placeholder="Direccion" tabindex="8" required>
                        </div>

                        <div class="mb-3">
                            <i class="lni lni-phone"></i>
                            <input type="tel" name="celular"  maxlength="10" minlength="10"  autocomplete="off" placeholder="Celular" tabindex="9" required>
                        </div>

                        <div class="mb-3">
                            <i class='bx bx-phone'></i>
                            <input type="tel" name="tel_fijo"  maxlength="10" placeholder="Telefono Fijo" tabindex="10">
                        </div>

                        <div class="mb-3">
                            <i class="lni lni-envelope"></i>
                            <input type="email" name="correo" inputmode="email" maxlength="56" placeholder="Correo" tabindex="11" required>
                        </div>

                        <button type="submit" class="btn btn-primary" id="registrar_proveedor_ajax">Registrar Proveedor</button>

                    </form>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/regis_varios_produs.js"></script>

</body>
</html>
