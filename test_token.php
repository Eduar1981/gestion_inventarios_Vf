<?php
require __DIR__ . '/includes/factus_client.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $token = factus_get_token();
    echo json_encode([
        'ok' => true,
        'token_prefix' => substr($token, 0, 12) . '...',
        'cache_file_exists' => file_exists(__DIR__ . '/cache/factus_token.json'),
        'cache_path' => __DIR__ . '/cache/factus_token.json'
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
