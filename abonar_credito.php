<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="js/menu.js"></script>
    <link rel="stylesheet" href="style/css/menu.css">
    <link rel="stylesheet" href="style/css/registrar_abono.css">
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
                <a href="ver_creditos.php">
                    <i class='bx bx-left-arrow-alt'></i>
                </a>
                <h4>REGISTRAR ABONOS</h4>
            </div>
        </section>

        <form action="registrar_abono.php" method="post" id="registro_abono">
            <div id="section_1" class="contenedores">  
                <input type="hidden" name="contador_creditos" value="<?php echo $_GET['contador_creditos']; ?>">  

                <div id="" class="campos">
                    <i class='bx bx-calendar'></i>
                    <input type="date" name="fecha_abono" 
                     autofocus="" tabindex="1" required>
                </div>

                <div id="" class="campos">
                    <i class='bx bx-money'></i>
                    <select name="metodo_pago" id="metodo_pago" autofocus="" tabindex="2" required>
                        <option value="">Elige una Opción</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Nequi">Nequi</option>
                        <option value="Tarjeta de Credito">Tarjeta de Credito</option>
                        <option value="Tarjeta de Debito">Tarjeta de Debito</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                </div>
                <div class="campos">
                    <i class='bx bxs-discount'></i>
                    <input type="text" name="referencia_pago" id="referencia_pago" placeholder="Referencia de Pago" tabindex="3">
                </div>

                <div id="" class="campos">
                    <i class='bx bx-money-withdraw'></i>
                    <input type="number" name="valor_abono" autofocus="" tabindex="4" required>
                </div>

                <div class="campos">
                    <i class='bx bx-comment-dots'></i>
                    <textarea name="observaciones" id="observaciones" tabindex="5" ></textarea>
                </div>
               
                <button id="registrar" type="submit" class="btn_registrar">Registrar</button>      
            </div>
        </form>

    </main> 
</body>
</html>