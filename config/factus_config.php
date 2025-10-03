<?php
// config/factus_config.php
define('FACTUS_BASE_URL',       'https://api-sandbox.factus.com.co');
define('FACTUS_OAUTH_ENDPOINT', FACTUS_BASE_URL . '/oauth/token');

// Credenciales OAuth (sandbox)
define('FACTUS_CLIENT_ID',     '9ea327c2-230e-4783-bd99-282cce71731b');
define('FACTUS_CLIENT_SECRET', 'FEBmUlXnptvb9kmK6Z71ir7RQmaX4KARYm5xdM51');
define('FACTUS_USERNAME',      'sandbox@factus.com.co'); // correo usuario
define('FACTUS_PASSWORD',      'sandbox2024%');     // contraseña usuario

// Ruta donde cacheamos el token (asegura permisos de escritura)
define('FACTUS_TOKEN_CACHE', __DIR__ . '/../cache/factus_token.json');
