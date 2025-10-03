<?php
require_once __DIR__ . '/factus_config.php';
require_once __DIR__ . '/../pdo.php'; // $pdo global

// ------------------------- Helpers de DB -------------------------
function factus_load_token(PDO $pdo): array {
    $st = $pdo->query("SELECT access_token, refresh_token, expires_at FROM factus_tokens WHERE id=1");
    return $st->fetch(PDO::FETCH_ASSOC) ?: ['access_token'=>'','refresh_token'=>'','expires_at'=>0];
}
function factus_save_token(PDO $pdo, string $access, ?int $expires_in, ?string $refresh): void {
    $now = time();
    $expires_at = $now + (int)($expires_in ?: 3600);
    $st = $pdo->prepare("UPDATE factus_tokens SET access_token=?, refresh_token=?, expires_at=? WHERE id=1");
    $st->execute([$access, $refresh, $expires_at]);
}

// ------------------------- Obtener token válido -------------------------
function factus_get_token(PDO $pdo): string {
    if (FACTUS_AUTH_MODE === 'STATIC_TOKEN') {
        if (!FACTUS_STATIC_TOKEN) throw new Exception('FACTUS_STATIC_TOKEN vacío.');
        return FACTUS_STATIC_TOKEN;
    }

    $row = factus_load_token($pdo);
    $now = time();

    // Si válido (le damos 60s de margen)
    if (!empty($row['access_token']) && (int)$row['expires_at'] > $now + 60) {
        return $row['access_token'];
    }

    // Si hay refresh_token, intenta refrescar
    if (!empty($row['refresh_token'])) {
        $ref = factus_refresh_token($row['refresh_token'], $row['access_token']); // pasa access para header Authorization
        if ($ref && !empty($ref['access_token'])) {
            factus_save_token($pdo, $ref['access_token'], $ref['expires_in'] ?? 3600, $ref['refresh_token'] ?? $row['refresh_token']);
            return $ref['access_token'];
        }
    }

    // No había token/refresh o falló → token inicial
    $init = factus_fetch_new_token();
    if (!$init || empty($init['access_token'])) {
        throw new Exception('No se pudo obtener token inicial de Factus');
    }
    factus_save_token($pdo, $init['access_token'], $init['expires_in'] ?? 3600, $init['refresh_token'] ?? null);
    return $init['access_token'];
}

// ------------------------- Token inicial -------------------------
function factus_fetch_new_token(): ?array {
    // La doc de Factus dice "form-data". Con cURL en PHP, mandar arreglo en POSTFIELDS crea multipart/form-data.
    $fields = [
        'grant_type'    => FACTUS_GRANT_TYPE,
        'client_id'     => FACTUS_CLIENT_ID,
        'client_secret' => FACTUS_CLIENT_SECRET,
    ];
    if (FACTUS_GRANT_TYPE === 'password') {
        $fields['username'] = FACTUS_USERNAME;
        $fields['password'] = FACTUS_PASSWORD;
    }

    $ch = curl_init(FACTUS_OAUTH_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $fields, // multipart/form-data
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_TIMEOUT        => 30
    ]);
    $raw  = curl_exec($ch);
    $err  = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err || $code < 200 || $code >= 300) {
        error_log("[FACTUS][TOKEN_INIT] HTTP $code err=$err resp=$raw");
        return null;
    }
    $j = json_decode($raw, true);
    return [
        'access_token'  => $j['access_token']  ?? null,
        'expires_in'    => $j['expires_in']    ?? 3600,
        'refresh_token' => $j['refresh_token'] ?? null,
    ];
}

// ------------------------- Refresh token -------------------------
function factus_refresh_token(string $refresh_token, ?string $current_access = null): ?array {
    $fields = [
        'grant_type'    => 'refresh_token',
        'client_id'     => FACTUS_CLIENT_ID,
        'client_secret' => FACTUS_CLIENT_SECRET,
        'refresh_token' => $refresh_token,
    ];

    $headers = ['Accept: application/json'];
    // La guía que mostraste pide Authorization: Bearer <access_token actual>
    if ($current_access) {
        $headers[] = 'Authorization: Bearer ' . $current_access;
    }

    $ch = curl_init(FACTUS_OAUTH_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $fields, // multipart/form-data
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 30
    ]);
    $raw  = curl_exec($ch);
    $err  = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err || $code < 200 || $code >= 300) {
        error_log("[FACTUS][TOKEN_REFRESH] HTTP $code err=$err resp=$raw");
        return null;
    }
    $j = json_decode($raw, true);
    return [
        'access_token'  => $j['access_token']  ?? null,
        'expires_in'    => $j['expires_in']    ?? 3600,
        'refresh_token' => $j['refresh_token'] ?? $refresh_token,
    ];
}

// ------------------------- Cliente HTTP -------------------------
function factus_api_request(string $method, string $path, array $payload = null): array {
    global $pdo;
    $token = factus_get_token($pdo);

    [$code, $body, $err] = factus_do_request($method, $path, $payload, $token);

    // Si 401, intenta refrescar y reintenta 1 vez
    if ($code === 401 && FACTUS_AUTH_MODE === 'OAUTH_TOKEN') {
        $row = factus_load_token($pdo);
        if (!empty($row['refresh_token'])) {
            $ref = factus_refresh_token($row['refresh_token'], $token);
            if ($ref && !empty($ref['access_token'])) {
                factus_save_token($pdo, $ref['access_token'], $ref['expires_in'] ?? 3600, $ref['refresh_token'] ?? $row['refresh_token']);
                return factus_do_request($method, $path, $payload, $ref['access_token']);
            }
        }
    }

    return [$code, $body, $err];
}

function factus_do_request(string $method, string $path, ?array $payload, string $access_token): array {
    $url = rtrim(FACTUS_API_BASE, '/') . $path;

    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Accept: application/json',
    ];
    $body = null;
    if ($payload !== null) {
        $headers[] = 'Content-Type: application/json';
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 60,
    ]);
    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

    $respBody = curl_exec($ch);
    $err      = curl_error($ch);
    $code     = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$code, $respBody, $err];
}

