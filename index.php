<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    
    <!-- Ícono principal -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    
    <!-- Compatibilidad con navegadores modernos -->
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16x16.png">


    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0066cc">

    <!-- Para iOS (Safari) -->
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <link rel="stylesheet" href="style/css/index.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'> 
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet">
</head>
<body>
    <main id="inicio">
        <div id="principal">
            <section id="logo">
                <figure id="img">
                    <img src="style/images/logo_gestion.png" alt="Gestión de Inventario - Logo">
                </figure>
            </section>

            <section id="info">
                <?php if (isset($error)): ?>
                    <p id="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <form action="vigia.php" method="POST" id="entrar">
                    <div class="campos">
                        <i class='bx bxs-id-card'></i>
                        <input type="text" id="usuario_documento" name="documento" placeholder="Número de documento" pattern="[0-9]{6,12}" maxlength="12" minlength="6" autofocus required>
                    </div>

                    <div class="campos">
                        <input type="password" id="contra" name="contra" placeholder="Contraseña" required>
                        <i class='bx bx-hide' id="togglePassword"></i>
                    </div>

                    <button type="submit" class="btn_entrar">Entrar</button>

                </form>
            </section>
        </div>
        <footer>
            <p>© Software: Eduar Corrales Grisales - Nicol Dahyanna Motoa</p>
        </footer>
    </main>

<!----- Script para mostrar/ocultar la contraseña ----->    
<script>
    document.getElementById("togglePassword").addEventListener("click", function () {
        const passwordField = document.getElementById("contra");
        const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
        passwordField.setAttribute("type", type);
        
        // Alternar ícono
        this.classList.toggle("bx-hide");
        this.classList.toggle("bx-show");
    });
</script>
</body>
</html>
