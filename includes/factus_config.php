<?php
define('FACTUS_API_BASE', 'https://api-sandbox.factus.com.co');

// Usa OAUTH con refresh token:
define('FACTUS_AUTH_MODE', 'OAUTH_TOKEN'); // 'STATIC_TOKEN' o 'OAUTH_TOKEN'

// Sólo si usas STATIC:
define('FACTUS_STATIC_TOKEN', '');

// ===== OAuth =====
define('FACTUS_OAUTH_URL', FACTUS_API_BASE . '/oauth/token');

// Elige el flujo que te dio Factus:
define('FACTUS_GRANT_TYPE', 'password'); // o 'client_credentials'

// Credenciales
define('FACTUS_CLIENT_ID',     '9ea327c2-230e-4783-bd99-282cce71731b');
define('FACTUS_CLIENT_SECRET', 'FEBmUlXnptvb9kmK6Z71ir7RQmaX4KARYm5xdM51');

// Si tu flujo es PASSWORD:
define('FACTUS_USERNAME', 'sandbox@factus.com.co');     // si aplica
define('FACTUS_PASSWORD', 'sandbox2024%');    // si aplica
