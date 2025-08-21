<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="js/menu.js"></script>
    <link rel="stylesheet" href="style/css/menu.css">
    <link rel="stylesheet" href="style/css/registrar_cliente.css">
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

        <div id="atras">
            <a href="ver_clientes.php">
                <i class="lni lni-arrow-left"></i>
            </a>
        </div>

        <h3>Registra un nuevo cliente</h3>

         <!-- Mostrar el mensaje dentro de la sección principal -->
         <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="registrar_clientes.php" method="post" id="">
            
            <div id="section_1" class="contenedores">  

                <div class="campos">
                    <select name="tipo_persona" id="tipo_persona" class="campo_select" tabindex="1" required>
                        <option value="">Tipo Persona</option>
                        <option value="Natural">Natural</option>
                        <option value="Juridica">Juridica</option>
                    </select>
                </div>

                <div class="campos">
                    <select name="tipo_documento" id="tipo_documento" class="campo_select" tabindex="2" required>
                        <option value="">Tipo de documento</option>
                        <option value="NIT">NIT</option>
                        <option value="Cedula de Ciudadania">Cédula de ciudadanía</option>
                        <option value="Cedula de Extranjeria">Cédula de extranjería</option>
                        <option value="Pasaporte">Pasaporte</option>
                        <option value="Estatus de Proteccion Temporal">Estatus de protección temporal (PPT)</option>
                    </select>
                </div>

                <div id="num_doc" class="campos">
                    <i class="lni lni-postcard"></i>
                    <input type="text" name="documento" pattern="[0-9]{6,12}" maxlength="12" minlength="6" autocomplete="off" placeholder="Documento" tabindex="3" required>
                </div>
                     
                <div id="nombre" class="campos">
                    <i class="lni lni-user"></i>
                    <input type="text" name="nombre" maxlength="25" minlength="4"
                    pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,25}" placeholder="Nombre (s)" autofocus="" tabindex="4" required>
                </div>

            </div>

            <div id="section_2" class="contenedores">

                <div id="apellido" class="campos">
                    <input type="text" name="apellido" maxlength="25" minlength="5"
                    pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{2,25}" placeholder="Apellido (s)" tabindex="5" required>
                </div>

                <div>
                    <label for="fecha_nacimiento">Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" tabaindex="6"required>
                    <small id="errorEdad" style="color: red; display: none;"></small>
                </div>

                <div id="num_movil" class="campos">
                    <i class="lni lni-postcard"></i>
                    <input type="tel" name="celular"  maxlength="10"  autocomplete="off" placeholder="Celular" tabindex="7" required>
                </div>

                <div id="correo" class="campos"> 
                    <i class="lni lni-envelope"></i>
                    <input type="email" name="correo" inputmode="email" maxlength="56" placeholder="Correo" tabindex="8" required>
                </div>
                
            </div>

            <div id="section_3" class="contenedores">

                <div id="direccion" class="campos">
                    <input type="text" name="direccion" maxlength="60" minlength="10"
                    pattern="[a-zA-Z0-9#\- ]{10,60}" placeholder="Direccion" tabindex="9" required>
                </div>

                <div id="ciudad" class="campos">
                    <input type="text" name="ciudad" maxlength="25" minlength="2"
                    pattern="[a-zA-Z ]{2,56}" placeholder="Ciudad" tabindex="10" required>
                </div>

                <div id="nom_comercial" class="campos">
                    <i class="lni lni-user"></i>
                    <input type="text" name="nom_comercial" maxlength="25" minlength="2"
                    pattern="[a-zA-Z ]{2,25}" placeholder="Nombre Comercial" autofocus="" tabindex="11">
                </div>

                <button type="submit" class="btn_registrar">Registrar</button>     

            </div>
        </form>
    </main>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    const fechaNacimientoInput = document.getElementById("fecha_nacimiento");
    const errorEdad = document.getElementById("errorEdad");

    // Limitar el input para que no permita fechas futuras
    const hoy = new Date();
    fechaNacimientoInput.max = hoy.toISOString().split("T")[0];

    // Validación al enviar el formulario
    const form = document.querySelector("form");
    form.addEventListener("submit", function (e) {
        const fechaNacimiento = new Date(fechaNacimientoInput.value);
        const hoy = new Date();

        const edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
        const mes = hoy.getMonth() - fechaNacimiento.getMonth();
        const dia = hoy.getDate() - fechaNacimiento.getDate();

        // Ajustar edad si aún no ha cumplido años este año
        const edadExacta = mes < 0 || (mes === 0 && dia < 0) ? edad - 1 : edad;

        if (edadExacta < 18) {
            e.preventDefault(); // Evitar que se envíe el formulario
            errorEdad.textContent = "Debe ser mayor o igual a 18 años para registrarse.";
            errorEdad.style.display = "block";
        } else {
            errorEdad.style.display = "none";
        }
    });
});
</script>

</body>
</html>