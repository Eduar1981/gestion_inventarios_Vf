<?php
// Conexión a la base de datos con PDO
require 'pdo.php';

session_start();

// Asegurar autenticación del usuario
if (!isset($_SESSION['documento'])) {
    header('Location: index.php');
    exit();
}

$rol_usuario = $_SESSION['rol'] ?? NULL;

// 📌 Consulta de ventas del día
$sql_dia = "SELECT SUM(total_venta) AS total FROM ventas WHERE DATE(tiempo_registro) = CURDATE() AND estado = 'activo'";
$total_dia = $pdo->query($sql_dia)->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// 📌 Consulta de ventas de la semana
$sql_semana = "SELECT SUM(total_venta) AS total FROM ventas WHERE YEARWEEK(tiempo_registro, 1) = YEARWEEK(CURDATE(), 1) AND estado = 'activo'";
$total_semana = $pdo->query($sql_semana)->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// 📌 Consulta de ventas de la quincena
$sql_quincena = "SELECT SUM(total_venta) AS total FROM ventas WHERE 
    MONTH(tiempo_registro) = MONTH(CURDATE()) 
    AND YEAR(tiempo_registro) = YEAR(CURDATE())
    AND (
        (DAY(tiempo_registro) BETWEEN 1 AND 15) OR 
        (DAY(tiempo_registro) > 15 AND DAY(CURDATE()) > 15)
    )
    AND estado = 'activo'";
$total_quincena = $pdo->query($sql_quincena)->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// 📌 Consulta de ventas del mes
$sql_mes = "SELECT SUM(total_venta) AS total FROM ventas WHERE 
    MONTH(tiempo_registro) = MONTH(CURDATE()) 
    AND YEAR(tiempo_registro) = YEAR(CURDATE()) 
    AND estado = 'activo'";
$total_mes = $pdo->query($sql_mes)->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.lineicons.com/5.0/lineicons.css" rel="stylesheet">
    <script src="js/menu.js"></script>
    <link rel="stylesheet" href="style/css/menu.css">
    <link rel="stylesheet" href="style/css/home.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <title>Home</title>
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
        <div id="nombre">
            <h3>Hola, <?php echo ($_SESSION['user_name']); ?></h3>
        </div>
        
        <div class="content">
            <div class="card-container">
                <div class="content-card" id="new_venta">
                    <div class="card" id="vender" onclick="window.location.href='carrito.php'">
                        <h3>🛒 Nueva Venta</h3>
                    </div>
                </div>

                <div class="content-card">
                    <div class="card"  onclick="cargarVentas('dia')">
                        <h3>📊 Ventas del Día</h3>
                    </div>

                    <div class="card" onclick="cargarVentas('semana')">
                        <h3>📊 Ventas de la Semana</h3>
                    </div>

                    <div class="card" onclick="cargarVentas('quincena')">
                        <h3>📊 Ventas de la Quincena</h3>
                    </div>

                    <div class="card" onclick="cargarVentas('mes')">
                        <h3>📊 Ventas del Mes</h3>
                    </div>
                </div>

                <div class="content-card">
                    <div class="card" onclick="cargarProductos('quincena')">
                        <h3>📦 Más Vendidos de la quincena</h3>
                    </div>

                    <div class="card" onclick="cargarProductos('mes')">
                        <h3>📦 Más Vendidos del Mes</h3>
                    </div>
                </div>

               
                <!-- 📌 Tarjetas de ventas por período -->
                

                <!-- 📌 Tarjetas de productos más vendidos -->
                
            </div>
        </div>
        <!-- 📌 Contenedor para mostrar ventas por período -->
        <div id="detalles-productos" style="display: none;">
            <span id="cerrar-productos" style="position: absolute; top: 0px; right: 10px; cursor: pointer; font-size: 28px;">&times;</span>
            <h3 id="titulo-productos">Ventas por Período</h3>
            <div id="contenido-productos"></div>
        </div>


        <!-- 📌 Contenedor para mostrar productos más vendidos -->
        <div id="detalles-productos" style="display: none; padding: 20px; border: 1px solid #ccc; margin-top: 20px; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; width: 50%; box-shadow: 2px 2px 10px rgba(0,0,0,0.1); z-index: 1000;">
            <span id="cerrar-productos" style="position: absolute; top: 5px; right: 10px; cursor: pointer; font-size: 20px;">&times;</span>
            <h3 id="titulo-productos">Productos Más Vendidos</h3>
            <div id="contenido-productos"></div>
        </div>


    </main>

    <script>
function cargarProductos(tipo) {
    fetch('detalles_productos_mas_vendidos.php?tipo=' + tipo)
        .then(response => response.json()) // Convertir respuesta a JSON
        .then(data => {
            if (data.error) {
                document.getElementById("contenido-productos").innerHTML = `<p>${data.error}</p>`;
            } else {
                let lista = "<ul>";
                data.forEach(producto => {
                    lista += `<li><strong>${producto.nombre}</strong> - ${producto.total_vendidos} unidades</li>`;
                });
                lista += "</ul>";
                document.getElementById("contenido-productos").innerHTML = lista;
            }

            // Mostrar el modal
            document.getElementById("detalles-productos").style.display = "block";
        })
        .catch(error => console.error("Error en la carga de datos:", error));
}

// Función para cerrar el modal
document.getElementById("cerrar-productos").addEventListener("click", function() {
    document.getElementById("detalles-productos").style.display = "none";
});

// Cerrar el modal si se hace clic fuera de él
window.addEventListener("click", function(event) {
    var detalles = document.getElementById("detalles-productos");
    if (event.target !== detalles && !detalles.contains(event.target)) {
        detalles.style.display = "none";
    }
});
</script>

<!-------- Script para mostar los datos del modal de ventas por dia , semana ,quicenal, mesual -->
<script>
function cargarVentas(periodo) {
    fetch('detalles_ventas.php?periodo=' + periodo)
        .then(response => response.json()) // Convertir respuesta a JSON
        .then(data => {
            if (data.error) {
                document.getElementById("contenido-productos").innerHTML = `<p>${data.error}</p>`;
            } else {
                let tarjetas = "";
                data.forEach(fila => {
                    tarjetas += `<div class="tarjeta-venta">
                                    <h3>${fila.metodo_pago}</h3>
                                    <p><strong>Número de Ventas:</strong> ${fila.total_ventas}</p>
                                    <p><strong>Total Pagado:</strong> $${fila.total_pagado}</p>
                                </div>`;
                });

                document.getElementById("contenido-productos").innerHTML = tarjetas;
            }

            // Mostrar el modal
            document.getElementById("detalles-productos").style.display = "block";
        })
        .catch(error => console.error("Error en la carga de datos:", error));
}

// Función para cerrar el modal
document.getElementById("cerrar-productos").addEventListener("click", function() {
    document.getElementById("detalles-productos").style.display = "none";
});

// Cerrar el modal si se hace clic fuera de él
window.addEventListener("click", function(event) {
    var detalles = document.getElementById("detalles-productos");
    if (event.target !== detalles && !detalles.contains(event.target)) {
        detalles.style.display = "none";
    }
});
</script>

<script src="js/alerta_cant_mini.js"></script>
</body>
</html>