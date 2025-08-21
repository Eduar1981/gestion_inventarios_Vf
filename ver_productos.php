<?php
require 'pdo.php';
require 'libs/fpdf/fpdf.php';

session_start();

require 'funciones.php';

if (!isset($_SESSION['documento'])) {
    header('Location: index.php');
    exit();
}

$es_admin = ($_SESSION['rol'] === 'administrador');

$estado = 'activo';
$stmt = $pdo->prepare('
     SELECT 
        `cont_producto`, 
        `referencia`, 
        `nombre`, 
        `precio_venta`
    FROM `productos` 
    WHERE `estado` = :activo
    ORDER BY `cont_producto` DESC
    LIMIT 15;
');
$stmt->bindParam(':activo', $estado);
$stmt->execute();

$productos = $stmt->fetchAll(PDO::FETCH_OBJ);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.lineicons.com/5.0/lineicons.css" rel="stylesheet">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet">
    <script src="js/menu.js"></script>
    <link rel="stylesheet" href="style/css/ver_productos.css">
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
                <a id="" href="ver_compras_proveedores.php" class="tooltip" data-tooltip="Compras">
                    <div>
                        <i class='bx bxs-package'></i>
                        <span class="option">Compras Porveedores</span>
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
                <h4>LISTADO DE PRODUCTOS</h4>
            </div>  
            <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador') : ?>
                <a id="add_producto" href="registro_varios_productos.php">
                    <i class='bx bx-plus-circle'></i>
                    <p>Nuevo Producto</p>
                </a>
            <?php endif; ?>
        </section>

        <section id="opciones">
            <div id="descargar_prod">
                 <!-- Botón de descarga PDF -->
                <a href="generar_pdf.php" class="descargar-pdf">
                    <button>Descargar PDF</button>
                </a>
            </div>
            <div id="buscar_prod">
                <form id="busca_producto" action="buscador_producto.php" method="GET" >
                    <div class="campos" id="buscador_prod">
                        <i class='bx bx-search-alt-2' ></i>
                        <input type="text" name="producto" id="producto" placeholder="Buscar por nombre o codigo" autocomplete="off">
                        <!-- Contenedor para mostrar las sugerencias -->
                        <div id="sugerencias"></div>
                        <button type="submit" id="btn_buscador"><i class='bx bx-search-alt-2'></i></button>
                    </div>
                </form>
            </div>
            
        </section>
        
        <section id="datos">
            <!-- mensaje de producto registrado con exito -->
            <?php mostrar_mensaje(); ?>

            <?php if ($productos): ?>
                <table id="datos_productos">
                    <thead>
                        <tr>
                            <th>Referencia</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                            <th>Info</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?= htmlspecialchars($producto->referencia) ?></td>
                                <td><?= htmlspecialchars($producto->nombre) ?></td>
                                <td>$<?= number_format($producto->precio_venta, 0, '', '.') ?></td>


                                <td id="icono_edit">
                                <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador'): ?>
                                    <a href="editar_producto.php?cont_producto=<?= $producto->cont_producto ?>">
                                        <i class="lni lni-pencil"></i>
                                    </a>
                                <?php else: ?>
                                    <i class="lni lni-lock" title="No tienes permisos para editar"></i>
                                <?php endif; ?>
                                </td>
                                <td id="icono_elim">
                                <?php if ($_SESSION['rol'] === 'superadmin' || $_SESSION['rol'] === 'administrador'): ?>
                                    <a href="eliminar_producto.php?cont_producto=<?= htmlspecialchars($producto->cont_producto) ?>" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?');">
                                        <i class="lni lni-trash-can"></i>
                                    </a>
                                <?php else: ?>
                                    <i class="lni lni-lock" title="No tienes permisos para editar"></i>
                                <?php endif; ?>
                                </td>
                                <td id="icono_edit">
                                    <a href="#" class="verMasProducto" data-id="<?= $producto->cont_producto ?>">
                                        <i class='bx bx-plus-circle'></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Aún no hay productos registrados para ver</p>
            <?php endif; ?>
        </section>

        <!-- Modal para mostrar detalles del producto -->
        <div id="modalProducto" style="display:none;">
            <div id="contenidoModal">
                <span id="cerrarModal">&times;</span>
                <h3>Detalles del producto</h3>
                <p>Codigo: <span id="codigo"></span></p>
                <p>Nombre: <span id="nombre"></span></p>
                <p>Descripción: <span id="descripcion"></span></p>
                <p>Categoria: <span id="categoria"></span></p>
                <p>Precio de compra: <span id="precio_compra"></span></p>
                <p>IVA: <span id="iva"></span></p>
                <p>Porcentaje de Ganancia: <span id="porcentaje_ganancia"></span></p>
                <p>Precio de venta: <span id="precio_venta"></span></p>
                <p>Cantidad actual:<span id="cantidad"></span></p>
                <p>Cantidad mínima: <span id="cantidad_minima"></span></p>
            </div>
        </div>

    </main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // GUARDA el rol del usuario en sessionStorage desde PHP
        <?php if (isset($_SESSION['rol'])): ?>
            sessionStorage.setItem('rol', "<?= $_SESSION['rol'] ?>");
        <?php endif; ?>
    });
</script>


<!------ Script para mostrar sugerencia y autocompletado para buscar en el formulario ----------->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputProducto = document.getElementById('producto');
    const tablaProductos = document.querySelector('#datos_productos tbody');

    // Evitar recargar la página con el formulario
    document.getElementById('busca_producto').addEventListener('submit', function (event) {
        event.preventDefault();
    });

    // Función para actualizar la tabla
    function actualizarEventosVerMas() {
        const verMasProductoLinks = document.querySelectorAll('.verMasProducto');

        verMasProductoLinks.forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                const productoId = this.getAttribute('data-id');

                fetch('detalle_producto.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `cont_producto=${productoId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('No se pudo cargar la información del producto.');
                        return;
                    }

                    // Llenar el modal con los datos obtenidos
                    document.getElementById('codigo').innerText = data.codigo_producto;
                    document.getElementById('nombre').innerText = data.nombre;
                    document.getElementById('descripcion').innerText = data.descripcion;
                    document.getElementById('categoria').innerText = data.categoria;

                    // Formatear valores monetarios
                    document.getElementById('precio_compra').innerText = parseFloat(data.precio_compra).toLocaleString('es-CO', { style: 'currency', currency: 'COP' });
                    document.getElementById('iva').innerText = data.con_iva === '1' ? 'Sí' : 'No';
                    document.getElementById('porcentaje_ganancia').innerText = `${data.porcentaje_ganancia}%`;
                    document.getElementById('precio_venta').innerText = parseFloat(data.precio_venta).toLocaleString('es-CO', { style: 'currency', currency: 'COP' });
                    document.getElementById('cantidad').innerText = data.cantidad;
                    document.getElementById('cantidad_minima').innerText = data.cantidad_minima;

                    // Mostrar el modal
                    document.getElementById('modalProducto').style.display = 'block';
                })
                .catch(error => console.error('Error:', error));
            });
        });
    }

    // Evento de entrada para buscar sugerencias y actualizar la tabla
    inputProducto.addEventListener('input', function () {
        const query = inputProducto.value.trim();

        if (query.length > 0) {
            fetch(`buscador_producto.php?autocomplete=${query}`)
                .then(response => response.json())
                .then(data => {
                    if (data.productos && data.productos.length > 0) {
                        tablaProductos.innerHTML = '';

                        data.productos.forEach(producto => {
                            let fila = `
                                <tr>
                                    <td>${producto.referencia}</td>
                                    <td>${producto.nombre}</td>
                                    <td>$${parseFloat(producto.precio_venta).toFixed(2)}</td>
                                    
                                    <td id="icono_edit">
                                        ${sessionStorage.getItem('rol') === 'administrador' ? 
                                            `<a href="editar_producto.php?cont_producto=${producto.cont_producto}">
                                                <i class="lni lni-pencil"></i>
                                            </a>` :
                                            `<i class="lni lni-lock" title="No tienes permisos para editar"></i>`
                                        }
                                    </td>

                                    <td id="icono_elim">
                                        ${sessionStorage.getItem('rol') === 'administrador' ? 
                                            `<a href="eliminar_producto.php?cont_producto=${producto.cont_producto}" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?');">
                                                <i class="lni lni-trash-can"></i>
                                            </a>` :
                                            `<i class="lni lni-lock" title="No tienes permisos para eliminar"></i>`
                                        }
                                    </td>

                                    <td id="icono_edit">
                                        <a href="#" class="verMasProducto" data-id="${producto.cont_producto}">
                                            <i class='bx bx-plus-circle'></i>
                                        </a>
                                    </td>
                                </tr>
                            `;
                            tablaProductos.innerHTML += fila;
                        });

                        // Llamar a la función para reactivar los eventos del modal
                        actualizarEventosVerMas();
                    } else {
                        tablaProductos.innerHTML = '<tr><td colspan="6">No se encontraron productos.</td></tr>';
                    }
                })
                .catch(error => console.error('Error al buscar productos:', error));
        } else {
            tablaProductos.innerHTML = '<tr><td colspan="6">Introduce un nombre o código.</td></tr>';
        }
    });

    // Asignar eventos cuando la página carga por primera vez
    actualizarEventosVerMas();
});
</script>


<!-- Script para ver mas detalles del producto -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const verMasProductoLinks = document.querySelectorAll('.verMasProducto');

        verMasProductoLinks.forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                const productoId = this.getAttribute('data-id'); // Obtén el cont_producto del producto

                // Realiza una solicitud AJAX para obtener los detalles reales del producto
                fetch('detalle_producto.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `cont_producto=${productoId}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la red');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error('Error del servidor:', data.error);
                        alert('No se pudo cargar la información del producto.');
                        return;
                    }
                    

                    // Asigna los datos obtenidos al modal
                    document.getElementById('codigo').innerText = data.codigo_producto;
                    document.getElementById('nombre').innerText = data.nombre;
                    document.getElementById('descripcion').innerText = data.descripcion;
                    document.getElementById('categoria').innerText = data.categoria;

                       // Formato para los valores monetarios
                    const precioCompraFormat = parseFloat(data.precio_compra).toLocaleString('es-CO', { style: 'currency', currency: 'COP' });
                    const precioVentaFormat = parseFloat(data.precio_venta).toLocaleString('es-CO', { style: 'currency', currency: 'COP' });

                    document.getElementById('precio_compra').innerText = precioCompraFormat;
                    document.getElementById('iva').innerText = (data.con_iva === '1' || data.con_iva === 1) ? 'Sí' : 'No';
                    document.getElementById('porcentaje_ganancia').innerText = `${data.porcentaje_ganancia}%`;
                    document.getElementById('precio_venta').innerText = precioVentaFormat;
                    document.getElementById('cantidad').innerText = data.cantidad;
                    document.getElementById('cantidad_minima').innerText = data.cantidad_minima;

                    if (usuarioRol === 'vendedor') {
                        document.getElementById('precio_compra').parentElement.style.display = 'none';
                        document.getElementById('iva').parentElement.style.display = 'none';
                        document.getElementById('porcentaje_ganancia').parentElement.style.display = 'none';
                    } else {
                        document.getElementById('precio_compra').parentElement.style.display = 'block';
                        document.getElementById('iva').parentElement.style.display = 'block';
                        document.getElementById('porcentaje_ganancia').parentElement.style.display = 'block';
                    }
                    // Muestra el modal
                    document.getElementById('modalProducto').style.display = 'block';
                })
                .catch(error => console.error('Error:', error));

            });
        });

        // Agrega un evento de clic para cerrar el modal
        document.getElementById('cerrarModal').addEventListener('click', function() {
            document.getElementById('modalProducto').style.display = 'none';
        });

        // Cerrar el modal si se hace clic fuera de él
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('modalProducto');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
</script>

<script>
    const usuarioRol = "<?php echo $_SESSION['rol']; ?>"
</script>
</body>
</html>
