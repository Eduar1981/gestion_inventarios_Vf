<?php
//Parametros de conexion con la DB por medio del pdo
require 'pdo.php';

session_start();

// Verifica que el cont_producto esté presente en la URL
if (isset($_GET['cont_producto'])) {
    $cont_producto = $_GET['cont_producto'];

// Verifica si el usuario tiene permisos de administrador
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'superadmin' && $_SESSION['rol'] !== 'administrador')) {
    // Puedes redirigir al usuario o mostrar un mensaje de error
    // Por ejemplo, lo redirigimos a la página principal con un mensaje
    $_SESSION['error'] = "No tienes permisos para editar productos.";
    header('Location: ver_productos.php');
    exit();
}

    // Se hace la consulta para obtener los datos del producto
    $stmt = $pdo->prepare("
        SELECT 
            p.`cont_producto`, 
            p.`codigo_producto`, 
            p.`referencia`, 
            p.`nombre`, 
            p.`descripcion`, 
            p.`categoria`, 
            c.`nombre` AS nombre_categoria,
            p.`precio_compra`, 
            p.`con_iva`, 
            p.`precio_venta`, 
            p.`porcentaje_ganancia`, 
            p.`cantidad`, 
            p.`cantidad_minima` 
        FROM 
            `productos` p
        LEFT JOIN 
            `categorias` c ON p.categoria = c.contador_categoria
        WHERE 
            p.cont_producto = :cont_producto 
            AND p.estado = 'activo'
    ");
    $stmt->bindParam(':cont_producto', $cont_producto, PDO::PARAM_INT);
    $stmt->execute();

    // Almacenar los datos del usuario
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        die('No se encontró el producto.');
    }
    
} else {
    echo "No se ha especificado el producto a actualizar.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet">
    <script src="js/menu.js"></script>
    <link rel="stylesheet" href="style/css/editar_producto.css">
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
                <a href="ver_productos.php">
                    <i class='bx bx-left-arrow-alt'></i>
                </a>
                <h4>ACTUALIZAR PRODUCTO</h4>
            </div>  
        </section>
        
        <!-- Mostrar el mensaje dentro de la sección principal -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <form action="actualizar_producto.php" method="post" id="actualizar_prod">
            
            <!-- Campo oculto para el contador de la categoría -->
            <input type="hidden" name="cont_producto" value="<?= htmlspecialchars($producto['cont_producto']) ?>">

            <div id="section_1" class="contenedores"> 

                <div class="dato_prod">
                    <div id="" class="campos">
                        <label class="letras" for="codigo_producto">Codigó Producto</label>
                        <input type="text" name="codigo_producto" maxlength="25" minlength="2"
                        pattern="[a-zA-Z0-9#\- _ñÑ ]{2,25}" autofocus="" tabindex="1" value="<?= htmlspecialchars($producto['codigo_producto']) ?>">
                    </div>

                    <div id="apellido" class="campos">
                        <label class="letras" for="referencia">Referencia</label>
                        <input type="text" name="referencia" maxlength="25" minlength="2"
                        pattern="[a-zA-Z0-9#\- _ñÑ ]{2,25}" tabindex="2" value="<?= htmlspecialchars($producto['referencia']) ?>">
                    </div>
                </div> 

                <div class="dato_prod">
                    <div id="nombre" class="campos">
                        <label class="letras"  for="nombre">Nombre del Producto</label>
                        <input type="text" name="nombre"
                        placeholder="Nombre Producto" autofocus="" tabindex="3" value="<?= htmlspecialchars($producto['nombre']) ?>">
                    </div>

                    <div class="campos">
                        <label class="letras" for="descripcion">Descripción</label>
                        <textarea name="descripcion" id="" tabindex="4"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
                    </div>
                </div>

                <div class="dato_prod">
                    <div class="campos">
                        <label class="letras" for="categoria">Categoría</label>
                        <select name="categoria" id="categoria" class="campo_select">
                            <?php
                            // Obtener todas las categorías
                            $categorias = $pdo->query("SELECT contador_categoria, nombre FROM categorias")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($categorias as $categoria) {
                                $selected = ($categoria['contador_categoria'] == $producto['categoria']) ? 'selected' : '';
                                echo "<option value='{$categoria['contador_categoria']}' $selected>{$categoria['nombre']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="dato_prod">
                    <div id="ciudad" class="campos icono_precio">
                        <label class="letras" for="precio_compra">Precio compra</label>
                        <div style="display: flex; align-items: center;">
                            <span>$</span>
                            <input type="text" class="precios" name="precio_compra" id="precio_compra"
                            tabindex="7" value="<?= htmlspecialchars($producto['precio_compra']) ?>">
                        </div>
                    </div>

                    <div class="campos">
                        <label class="letras" for="con_iva">¿Con IVA?</label>
                        <select name="con_iva" id="con_iva_select" tabindex="8" class="campo_select">
                            <option value="1" <?= ($producto['con_iva'] == 1) ? 'selected' : '' ?>>Sí</option>
                            <option value="0" <?= ($producto['con_iva'] == 0) ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                </div>

                <div class="dato_prod">
                    <div class="campos">
                        <label class="letras" for="porcentaje_ganancia">Porcentaje de Ganancia</label>
                        <input type="number" name="porcentaje_ganancia" id="porcentaje_ganancia"
                        tabindex="7" value="<?= htmlspecialchars($producto['porcentaje_ganancia']) ?>">
                    </div>

                    <div class="campos icono_precio">
                        <label class="letras" for="precio_venta">Precio Venta</label>
                        <div style="display: flex; align-items: center;">
                        <span>$</span>
                        <input class="precios" type="text" name="precio_venta" id="precio_venta"
                        tabindex="7" value="<?= htmlspecialchars($producto['precio_venta']) ?>">
                        </div>
                    </div>
                </div>
            
                <div class="dato_prod">

                    <div id="num_movil" class="campos">
                        <label class="letras" for="cantidad">Cantidad</label>
                        <input type="number" name="cantidad" tabindex="10" value="<?= htmlspecialchars($producto['cantidad']) ?>">
                    </div>

                    <div id="correo" class="campos"> 
                        <label class="letras" for="cantidad_minima">Cantidad minima</label>
                        <input type="number" name="cantidad_minima"  maxlength="1" tabindex="11" value="<?= htmlspecialchars($producto['cantidad_minima']) ?>">
                    </div>

                </div>

                <div id="section_3" class="contenedores">
                    <button type="submit" class="btn_actualizar">Actualizar</button>      
                </div>
            </form>
                
    </main>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const precioCompraInput = document.getElementById("precio_compra");
    const conIVASelect = document.getElementById("con_iva_select");
    const porcentajeGananciaInput = document.getElementById("porcentaje_ganancia");
    const precioVentaInput = document.getElementById("precio_venta");
    let usuarioEditaPrecioVenta = true; // Cambiado a true inicialmente

    function formatNumber(value) {
        let number = parseInt(value.replace(/[^0-9]/g, ""), 10);
        if (isNaN(number)) return "";
        return number.toLocaleString("es-CO"); // Solo miles, sin decimales
    }

    function unformatNumber(value) {
        if (!value) return "0";
        return value.replace(/\./g, ""); // Eliminar puntos para convertirlo en número limpio
    }

    function calcularPrecioVenta() {
        if (usuarioEditaPrecioVenta) return;

        const precioCompra = parseInt(unformatNumber(precioCompraInput.value)) || 0;
        const porcentajeGanancia = parseFloat(unformatNumber(porcentajeGananciaInput.value)) || 0;
        const conIVA = conIVASelect.value === "1" ? 1.19 : 1;

        if (precioCompra === 0) return;

        let precioSugerido;
        if (porcentajeGanancia === 0) {
            precioSugerido = Math.round(precioCompra * conIVA); // Redondea a entero
        } else {
            precioSugerido = Math.round((precioCompra * conIVA) / ((100 - porcentajeGanancia) / 100));
        }

        precioVentaInput.value = formatNumber(precioSugerido.toString()); // Aplicar formato con separador de miles
    }

    function manejarEntrada(inputElement, callback, permitirDecimales = true) {
        inputElement.addEventListener("input", function (event) {
            let valorOriginal = event.target.value;

            let valorNumerico = permitirDecimales 
                ? valorOriginal.replace(/[^0-9]/g, "") // Permite solo números sin decimales
                : valorOriginal.replace(/[^0-9]/g, ""); 

            event.target.value = formatNumber(valorNumerico); // Aplica formato mientras escribe
            callback();
        });

        inputElement.addEventListener("blur", function () {
            event.target.value = formatNumber(event.target.value);
        });
    }

    // Listeners para cambios en los campos que afectan el cálculo
    precioCompraInput.addEventListener("change", function() {
        usuarioEditaPrecioVenta = false;
        calcularPrecioVenta();
    });
    
    conIVASelect.addEventListener("change", function() {
        usuarioEditaPrecioVenta = false;
        calcularPrecioVenta();
    });
    
    porcentajeGananciaInput.addEventListener("change", function() {
        usuarioEditaPrecioVenta = false;
        calcularPrecioVenta();
    });

    // Manejo de entrada para los campos
    manejarEntrada(precioCompraInput, () => {
        usuarioEditaPrecioVenta = false;
        calcularPrecioVenta();
    });
    manejarEntrada(precioVentaInput, () => {}); // Permitir edición manual de precio_venta sin recalcular
    manejarEntrada(porcentajeGananciaInput, () => {
        usuarioEditaPrecioVenta = false;
        calcularPrecioVenta();
    }, false);

    // Listeners para el campo de precio venta
    precioVentaInput.addEventListener("focus", function () {
        usuarioEditaPrecioVenta = true;
    });

    precioVentaInput.addEventListener("blur", function () {
        usuarioEditaPrecioVenta = false;
        precioVentaInput.value = formatNumber(precioVentaInput.value);
    });

    // Formatear valores iniciales
    if (precioCompraInput.value) {
        precioCompraInput.value = formatNumber(precioCompraInput.value);
    }
    if (precioVentaInput.value) {
        precioVentaInput.value = formatNumber(precioVentaInput.value);
    }
    if (porcentajeGananciaInput.value) {
        porcentajeGananciaInput.value = porcentajeGananciaInput.value.replace(",", "."); // Evita problemas con el input number
    }

    // Manejo del formulario
    const form = document.querySelector("form");
    if (form) {
        form.addEventListener("submit", function () {
            precioCompraInput.value = unformatNumber(precioCompraInput.value);
            precioVentaInput.value = unformatNumber(precioVentaInput.value);
            porcentajeGananciaInput.value = porcentajeGananciaInput.value.replace(",", "."); // Evita errores de formato
        });
    }
});
</script>
</body>
</html>