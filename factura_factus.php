<?php
// factura_factus.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/pdo.php';
require_once __DIR__ . '/includes/factus_client.php';
require_once __DIR__ . '/includes/factus_mapper.php'; // <-- tu mapper existente

// (Opcional) utilidades para DANE si no usas municipality fijo en el mapper
function normalize_txt($s) {
    $s = trim(mb_strtoupper($s, 'UTF-8'));
    $s = strtr($s, ['Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ü'=>'U','Ñ'=>'N']);
    $s = preg_replace('/[^A-Z0-9\s\-]/u', ' ', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
}
function load_dane_catalog() {
    static $cat = null;
    if (!is_null($cat)) return $cat;
    $path = __DIR__ . '/data/dane_municipios.json';
    if (!is_file($path)) return $cat = [];
    $arr = json_decode(file_get_contents($path), true);
    if (!is_array($arr)) return $cat = [];
    $map = [];
    foreach ($arr as $r) {
        $m = normalize_txt($r['municipio'] ?? '');
        $d = normalize_txt($r['departamento'] ?? '');
        if ($m !== '') {
            $map[$m] = (int)$r['id'];
            if ($d !== '') $map["$m, $d"] = (int)$r['id'];
        }
    }
    return $cat = $map;
}
function dane_from_city($ciudad, $departamento = null) {
    $map = load_dane_catalog();
    if (!$map) return null;
    $k = normalize_txt($ciudad ?? '');
    if ($departamento) {
        $kd = $k . ', ' . normalize_txt($departamento);
        if (isset($map[$kd])) return $map[$kd];
    }
    return $map[$k] ?? null;
}

// --------- Entrada ----------
$cont_venta = isset($_GET['cont_venta']) ? (int)$_GET['cont_venta'] : 0;
$validate   = (int)($_GET['validate'] ?? 0) === 1;

if ($cont_venta <= 0) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Falta ?cont_venta=']);
    exit;
}

// --------- 1) Traer venta + cliente ----------
$stmtV = $pdo->prepare("
    SELECT v.*,
           c.contador_clientes, c.tipo_persona, c.tipo_documento, c.documento,
           c.nombre, c.apellido, c.fecha_nacimiento, c.celular, c.telefono,
           c.correo, c.departamento, c.ciudad, c.direccion, c.nom_comercial,
           c.tributo_id
    FROM ventas v
    JOIN clientes c ON c.documento = v.documento
    WHERE v.cont_venta = ?
");
$stmtV->execute([$cont_venta]);
$venta = $stmtV->fetch(PDO::FETCH_ASSOC);
if (!$venta) {
    http_response_code(404);
    echo json_encode(['ok'=>false,'error'=>'Venta no encontrada']);
    exit;
}

// --------- 2) Customer con tu mapper ----------
$customer = buildFactusCustomerFromCliente($venta);

// 2.1) Tomamos cualquier forma que venga (objeto o plano)
$munId = null;
if (isset($customer['municipality']['id'])) {
    $munId = (int)$customer['municipality']['id'];
} elseif (isset($customer['municipality_id'])) {
    $munId = (int)$customer['municipality_id'];
}

// 2.2) Si no hay municipio, intentamos derivarlo de ciudad/departamento
if (!$munId) {
    $derivedId = dane_from_city($venta['ciudad'] ?? null, $venta['departamento'] ?? null);

    // Caso especial: Bogotá suele venir como “Bogotá” y depto “Cundinamarca”
    if (!$derivedId) {
        $ci = normalize_txt($venta['ciudad'] ?? '');
        if (in_array($ci, ['BOGOTA','SANTA FE DE BOGOTA','BOGOTA DC','BOGOTA D C'])) {
            $derivedId = 11001; // Bogotá, D.C.
        }
    }
    if ($derivedId) {
        $munId = (int)$derivedId;
    }
}

// 2.3) Dejamos SIEMPRE el formato que mejor acepta Factus: objeto { id: ... }
if ($munId) {
    $customer['municipality'] = ['id' => $munId];
}
unset($customer['municipality_id']); // limpiamos el plano si quedó

// 2.4) tribute_id: usa el de la DB o default sandbox
if (empty($customer['tribute_id']) && !empty($venta['tributo_id'])) {
    $customer['tribute_id'] = (int)$venta['tributo_id'];
}
if (empty($customer['tribute_id'])) {
    $customer['tribute_id'] = 21;
}


// --------- 3) Items con tu mapper ----------
$preciosIncluyenIVA = true; // tu caja guarda precio con IVA, tu mapper ya lo convierte a base.
$items = buildFactusItemsFromVenta($pdo, $cont_venta, $preciosIncluyenIVA);
if (!$items) {
    http_response_code(422);
    echo json_encode(['ok'=>false,'error'=>'La venta no tiene ítems (detalle_venta vacío)']);
    exit;
}

// Convertir 'tributes' (de tu mapper) -> 'taxes' (lo que pide Factus en /v1/bills)
// Convertir items del mapper interno → formato /v1/bills (Factus)
$itemsForFactus = array_map(function($it) {
    // BASE sin IVA desde el mapper
    $priceBase = (float)$it['price'];

    // IVA desde 'tributes'
    $rate = 0.0;
    if (!empty($it['tributes']) && is_array($it['tributes'])) {
        foreach ($it['tributes'] as $t) {
            if (($t['code'] ?? '') === '01') {
                $rate = (float)($t['rate'] ?? 0);
                break;
            }
        }
    }

    // /v1/bills: price CON IVA
    $priceConIVA = $rate > 0 ? round($priceBase * (1 + $rate / 100), 6) : $priceBase;

    // quantity entero
    $qty = (int)round((float)$it['quantity']);

    // 🔒 Forzar unidad a un ID aceptado por el tenant (ignorar lo que venga del producto)
    // Primero probamos 70; si no pasa, probamos 1.
    $uom = 70;

    // UNSPSC válido (8 dígitos). Para “camisa”:
    $standardCodeId = 53101802;

    // Campos requeridos por el validador
    $discountRate   = 0;
    $isExcluded     = ($rate <= 0) ? 1 : 0;
    $itemTributeId  = ($rate > 0) ? 1 : null; // 1 = IVA

    $rateStr = number_format($rate, 2, '.', '');  // "19.00" o "0.00"

    $row = [
        'code_reference'   => $it['code'],
        'name'             => $it['name'] ?? 'Ítem',
        'quantity'         => $qty,
        'price'            => $priceConIVA,
        'tax_rate'         => $rateStr,     // string "19.00"
        'unit_measure_id'  => 70,           // válido según catálogo
        'discount_rate'    => 0,
        'is_excluded'      => ($rate <= 0) ? 1 : 0,
        'standard_code_id' => 1,            // ← CLAVE: ID del tipo de estándar
        'tribute_id'       => ($rate > 0) ? 1 : null,
    ];

    if (!is_null($itemTributeId)) {
        $row['tribute_id'] = $itemTributeId;
    }
    return $row;
}, $items);


// --------- Validaciones previas amigables ----------
$errors = [];
if (empty($customer['municipality']['id'])) {
    $errors[] = 'customer.municipality.id no resuelto. Revisa data/dane_municipios.json o el nombre en clientes.ciudad/departamento.';
}
if (!empty($errors)) {
    http_response_code(422);
    echo json_encode([
        'ok'=>false,
        'error'=>'Validación previa',
        'issues'=>$errors,
        'ciudad'=>$venta['ciudad'] ?? null,
        'depto'=>$venta['departamento'] ?? null
    ], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    exit;
}


// --------- 4) Payload ----------
$numberingRangeId = 8; // Ajusta según tu /v1/numbering-ranges sandbox

$payload = [
    'document'            => '01',
    // 'numbering_range_id'  => $numberingRangeId, // sigue comentado
    'reference_code'      => (string)$cont_venta,
    // 'currency'            => 'COP',   // quitemos por ahora
    'payment_form'        => 1,         // entero
    'payment_method_code' => 10,
    // 'operation_type'      => 10,      // quitemos por ahora
    'send_email'          => false,
    'customer'            => $customer,
    'items'               => $itemsForFactus,
];


// --------- 5) Llamada a Factus ----------
$path = $validate ? '/v1/bills/validate' : '/v1/bills';
list($status, $body, $err) = factus_api_request('POST', $path, $payload);

// --------- 6) Respuesta ----------
echo json_encode([
    'ok'      => ($status === 200 || $status === 201),
    'status'  => $status,
    'error'   => $err,
    'payload' => $payload,
    'resp'    => json_decode($body, true) ?? $body,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

