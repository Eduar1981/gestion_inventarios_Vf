<?php
require 'pdo.php';
session_start(); // Descomentar esta línea es crucial
date_default_timezone_set('America/Bogota');

// Inicializar las variables para mostrar mensajes
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

// Limpiar los mensajes de sesión después de usarlos
unset($_SESSION['error_message']);
unset($_SESSION['success_message']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // ✅ Recoger los datos del formulario
        $tipo_doc = $_POST['tipo_documento'];
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $fecha_nacimiento = $_POST['fecha_nacimiento'];
        $movil = $_POST['celular'];
        $documento = $_POST['documento'];  // Se usará como usuario
        $correo = $_POST['correo'];
        $rol = $_POST['rol'];
        $ciudad = $_POST['ciudad'];
        $direccion = $_POST['direccion'];
        $tipo_documento = $_POST['tipo_documento'];
        $contrasena = $_POST['contra'];  // La nueva contraseña ingresada por el usuario
        $fecha_registro = date('Y-m-d H:i:s');

        // ✅ Verificar la edad mínima (18 años)
        $fecha_nacimiento_obj = new DateTime($fecha_nacimiento);
        $fecha_actual = new DateTime();
        $edad = $fecha_actual->diff($fecha_nacimiento_obj)->y;

        if ($edad < 18) {
            $_SESSION['error_message'] = "Debes ser mayor o igual de 18 años para poder registrarte";
        } else {
            // ✅ Verificar si el usuario ya existe (por documento o correo)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE documento = ? OR correo = ? AND estado = 'activo'");
            $stmt->execute([$documento, $correo]);
            $user_exists = $stmt->fetchColumn();

            if ($user_exists > 0) {
                $_SESSION['error_message'] = "Usuario ya está registrado.";
            } else {
                // ✅ Encriptar la contraseña con BCRYPT
                $password_hash = password_hash($contrasena, PASSWORD_BCRYPT);

                // ✅ Insertar el usuario en la base de datos con contraseña cifrada
                $stmt = $pdo->prepare("
                    INSERT INTO usuarios(tipo_doc, documento, nombre, apellido, fecha_nacimiento, celular, ciudad, direccion, rol, correo, contra, tiempo_registro, estado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                if ($stmt->execute([$tipo_documento, $documento, $nombre, $apellido, $fecha_nacimiento, $movil, $ciudad, $direccion, $rol, $correo, $password_hash, $fecha_registro, 'activo'])) {
                    $_SESSION['success_message'] = "Usuario registrado con éxito.";
                    header('Location: index.php');
                    exit();
                } else {
                    $_SESSION['error_message'] = "Error al registrar el usuario. Inténtalo de nuevo.";
                }
            }
        }

    } catch (PDOException $e) {
        error_log("Error en la base de datos: " . $e->getMessage());
        $_SESSION['error_message'] = "Error de conexión con la base de datos: " . $e->getMessage();
    }

    // Solo redirigir si no hay salida previa
    if (!headers_sent()) {
        header('Location: registrar_usuario.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel="stylesheet">
    <link rel="stylesheet" href="style/css/registrar_usuario.css">
    <title>Registro</title>
</head>
<body>
    <main id="inicio">
        <section id="barra">
            <div id="atras">
                <a href="index.php">
                    <i class="lni lni-arrow-left"></i>
                </a>
            </div>
        </section>   
        <!-- Mostrar el mensaje dentro de la sección principal -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <section id="datos">
            <h3>REGISTRA UN NUEVO USUARIO</h3>
            <form action="registrar_usuario.php" method="post" id="info_registro">
                
                <div id="section_1" class="contenedores">  
                    <div class="dato_user">
                        <div id="nombre" class="campos">
                            <!-- <p>Nombre</p> -->
                            <i class="lni lni-user"></i>
                            <input type="text" name="nombre" maxlength="25" minlength="2"
                            pattern="[a-zA-Z ]{2,25}" placeholder="Nombre (s)" autofocus="" tabindex="1" required>
                        </div>
                        <div id="apellido" class="campos">
                            <!-- <p>Apellido</p> -->
                            <input type="text" name="apellido" maxlength="25" minlength="2"
                            pattern="[a-zA-Z ]{2,25}" placeholder="Apellido (s)" tabindex="2" required>
                        </div>

                    </div>
                        
                    <div class="dato_user">
                        <div class="campos">
                            <select name="tipo_documento" id="tipo_documento" class="campo_select" tabindex="3" required>
                                <option value="">Tipo de documento</option>
                                <option value="Cedula de Ciudadania">Cédula de ciudadanía</option>
                                <option value="Cedula de Extranjeria">Cédula de extranjería</option>
                                <option value="Pasaporte">Pasaporte</option>
                                <option value="Estatus de Proteccion Temporal">Estatus de protección temporal (PPT)</option>
                            </select>
                        </div>
                        <div id="num_doc" class="campos">
                            <!-- <p>Número de documento</p> -->
                            <i class="lni lni-postcard"></i>
                            <input type="text" name="documento" pattern="[0-9]{6,12}" maxlength="12" minlength="4" autocomplete="off" placeholder="Documento" tabindex="5" required>
                        </div>
                    </div>

                    <div class="dato_user">
                        <div id="ciudad" class="campos">
                            <!-- <p>ciudad</p> -->
                            <input type="text" name="ciudad" maxlength="25" minlength="2"
                            pattern="[a-zA-Z ]{2,56}" placeholder="Ciudad" tabindex="5" required>
                        </div>
                        <div id="direccion" class="campos">
                            <i class='bx bxs-map'></i>
                            <input type="text" name="direccion" maxlength="60" minlength="10"
                            pattern="[a-zA-Z0-9#\- ]{10,60}" placeholder="Direccion" tabindex="6" required>
                        </div>

                    </div>

                    <div class="dato_user">
                        <div id="rol" class="campos">
                            <i class="lni lni-users"></i>
                            <select name="rol" id="" tabindex="7" required>
                                <option value="">Selecciona tu rol</option>
                                <option value="administrador">Administrador</option>
                                <option value="almacenista">Almacenista</option>
                                <option value="vendedor">Vendedor</option>
                            </select>
                        </div> 
                    
                    
                        <div id="fecha_nac" class="campos">
                            <p class="letras">Fecha de nacimiento</p>
                            <input type="date" name="fecha_nacimiento"
                            tabindex="8" required>
                        </div>

                    </div>
                    <div class="dato_user">
                        <div id="num_movil" class="campos">
                            <!-- <p>Número de celular</p> -->
                            <i class='bx bx-phone'></i>
                            <input type="tel" name="celular" maxlength="10" pattern="[0-9]{10}" autocomplete="off" placeholder="Celular" tabindex="9" required>
                        </div>
                        <div id="correo" class="campos"> 
                            <!-- <p>Correo Electrónico</p> -->
                            <i class="lni lni-envelope"></i>
                            <input type="email" name="correo" inputmode="email" maxlength="56" placeholder="Correo" tabindex="10" required>
                        </div>                

                    </div>
                    
                    <div class="dato_user">
                        <div id="pssw" class="campos">
                           <input type="password" id="contrasena" name="contra"
                                placeholder="Ingrese su contraseña" required
                                minlength="5" maxlength="10"
                                pattern="^\d{5,10}$"
                                inputmode="numeric"
                                title="La contraseña debe contener solo números y tener entre 5 y 10 dígitos.">

                            <!-- Icono para mostrar/ocultar contraseña -->
                            <i class="bx bx-hide" id="togglePassword" style="cursor: pointer;"></i>
                        </div>
                    </div>
            </div>

            <div id="section_3" class="contenedores">
                <button type="submit" class="btn_registrar">Registrarme</button>      
            </div>
        </form>
    </section>
    </main>
<script>
    const togglePassword = document.querySelector('#togglePassword');
    const passwordField = document.querySelector('#contrasena');

    togglePassword.addEventListener('click', function () {
        // Alternar el atributo 'type' entre 'password' y 'text'
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        
        // Alternar el ícono entre mostrar ('bx-show') y ocultar ('bx-hide')
        if (type === 'text') {
            this.classList.remove('bx-hide');
            this.classList.add('bx-show');
        } else {
            this.classList.remove('bx-show');
            this.classList.add('bx-hide');
        }
    });
</script>
</body>
</html>