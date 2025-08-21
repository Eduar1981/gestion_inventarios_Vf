<?php
// factura_factus.php

header('Content-Type: application/json');

function obtenerTokenFactus() {
    $url = "https://api-sandbox.factus.com.co/v1/security/token";
    $data = [
        "client_id" => "9ea327c2-230e-4783-bd99-282cce71731b",
        "username" => "sandbox@factus.com.co",
        "password" => "sandbox2024%"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    $response = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($response, true);
    return $json['token'] ?? null;
}

$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input['cliente'], $input['productos'], $input['metodo_pago'], $input['total'])) {
    echo json_encode(["error" => "Faltan datos en el JSON recibido."]);
    exit;
}

$cliente = $input['cliente'];
$productos = $input['productos'];
$metodo_pago = $input['metodo_pago'];
$total = $input['total'];

$payload = [
    "document" => "01",
    "numbering_range_id" => 4,
    "reference_code" => $input['referencia'] ?? uniqid('VENTA_'),
    "observation" => $input['observacion'] ?? '',
    "payment_form" => $input['forma_pago'] ?? 1,
    "payment_due_date" => $input['fecha_vencimiento'] ?? null,
    "payment_method_code" => $metodo_pago,
    "operation_type" => 10,
    "customer" => [
        "identification_document_id" => $cliente['tipo_documento'],
        "identification" => $cliente['documento'],
        "dv" => $cliente['dv'] ?? null,
        "company" => $cliente['razon_social'] ?? null,
        "trade_name" => $cliente['nombre_comercial'] ?? null,
        "names" => $cliente['nombre'],
        "address" => $cliente['direccion'],
        "email" => $cliente['correo'],
        "phone" => $cliente['telefono'],
        "legal_organization_id" => $cliente['tipo_persona'],
        "tribute_id" => $cliente['tributo'],
        "municipality_id" => $cliente['ciudad']
    ],
    "items" => []
];

foreach ($productos as $p) {
    $payload['items'][] = [
        "code" => $p['codigo'],
        "description" => $p['nombre'],
        "quantity" => $p['cantidad'],
        "price" => $p['precio_unitario'],
        "taxes" => []
    ];
}

$token = obtenerTokenFactus();
if (!$token) {
    echo json_encode(["error" => "No se pudo obtener el token de autenticación"]);
    exit;
}

$url = "https://api-sandbox.factus.com.co/v1/bills/validate";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    echo json_encode(["error" => "Error al enviar factura: $response"]);
} else {
    echo $response;
}
