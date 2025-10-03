<?php
// includes/factus_service.php
require_once __DIR__ . '/factus_client.php';

/**
 * Construye el payload que le enviaremos a Factus leyendo la venta en DB.
 */
function factus_build_payload_from_cont_venta(PDO $pdo, int $cont_venta): array {
    // --- Venta + Cliente ---
    $sqlVenta = "SELECT
                    v.cont_venta, v.total_venta, v.metodo_pago, v.documento AS doc_cliente,
                    c.nombre, c.apellido, c.correo, c.celular, c.direccion, c.ciudad, c.departamento,
                    c.tributo_id, c.tipo_persona, c.tipo_documento, c.documento, c.nom_comercial
                 FROM ventas v
                 JOIN clientes c ON c.documento = v.documento
                 WHERE v.cont_venta = ?";
    $st = $pdo->prepare($sqlVenta);
    $st->execute([$cont_venta]);
    $venta = $st->fetch(PDO::FETCH_ASSOC);
    if (!$venta) {
        throw new Exception("Venta $cont_venta no encontrada");
    }

    // --------- CUSTOMER (¡ESTO FALTABA / ESTABA MAL NOMBRADO!) ----------
    // Mapea con valores que sabes que funcionan en sandbox:
    // Natural: identification_document_id=3 (CC), legal_organization_id=2
    // Jurídica: identification_document_id=6 (NIT), legal_organization_id=1
    $isJuridica = (strtolower((string)$venta['tipo_persona']) === 'juridica');

    if ($isJuridica) {
        $customer = [
            'identification_document_id' => 6,
            'identification'             => $venta['documento'],                  // NIT
            'legal_organization_id'      => 1,                                    // Jurídica
            'address'                    => $venta['direccion'] ?: 'N/A',
            'email'                      => $venta['correo']    ?: 'na@local',
            'phone'                      => $venta['celular']   ?: '0000000000',
            'tribute_id'                 => (int)($venta['tributo_id'] ?: 21),    // 21 = “No aplica” en tu sandbox
            'municipality'               => ['id' => 11001],
            'company'                    => $venta['nom_comercial'] ?: (($venta['nombre'] ?: '') . ' ' . ($venta['apellido'] ?: '')),
            // si tuvieras DV en la tabla clientes, podrías agregar 'dv' => (int)$venta['dv']
        ];
    } else {
        $customer = [
            'identification_document_id' => 3,
            'identification'             => $venta['documento'],                  // CC
            'legal_organization_id'      => 2,                                    // Natural
            'address'                    => $venta['direccion'] ?: 'N/A',
            'email'                      => $venta['correo']    ?: 'na@local',
            'phone'                      => $venta['celular']   ?: '0000000000',
            'tribute_id'                 => (int)($venta['tributo_id'] ?: 21),
            'municipality'               => ['id' => 11001],
            'names'                      => trim(($venta['nombre'] ?: '') . ' ' . ($venta['apellido'] ?: '')) ?: ($venta['nom_comercial'] ?: 'Cliente'),
        ];
    }

    // --- Items ---
    $sqlDet = "SELECT
                  d.cont_producto,
                  d.descripcion,
                  d.cantidad_productos,
                  d.precio_unitario,
                  p.codigo_producto,
                  p.unit_measure_id,
                  p.iva_rate
               FROM detalle_venta d
               JOIN productos p ON p.cont_producto = d.cont_producto
               WHERE d.cont_venta = ?";
    $sd = $pdo->prepare($sqlDet);
    $sd->execute([$cont_venta]);

    $items = [];
    while ($row = $sd->fetch(PDO::FETCH_ASSOC)) {
        $qty   = (int)$row['cantidad_productos'];
        $price = (float)$row['precio_unitario'];

        // Fallbacks seguros que ya viste funcionar
        $unit_measure_id = (int)($row['unit_measure_id'] ?: 70); // 70 ~ “unidad” (code 94)
        $tax_rate        = isset($row['iva_rate']) ? number_format((float)$row['iva_rate'], 2, '.', '') : '19.00';

        $items[] = [
            'code_reference'  => $row['codigo_producto'] ?: ('cd-' . str_pad($row['cont_producto'], 3, '0', STR_PAD_LEFT)),
            'name'            => $row['descripcion'] ?: 'Item',
            'quantity'        => $qty,
            'price'           => $price,
            'tax_rate'        => $tax_rate,
            'unit_measure_id' => $unit_measure_id,
            'discount_rate'   => 0,
            'is_excluded'     => 0,
            // Los siguientes dos campos te funcionaron en tu prueba manual:
            'standard_code_id'=> 1,
            'tribute_id'      => 1, // IVA
        ];
    }
    if (!$items) {
        throw new Exception("Venta $cont_venta sin items");
    }

    // --- Forma de pago ---
    $payment_form        = ($venta['metodo_pago'] === 'credito') ? 2 : 1; // 1=Contado, 2=Crédito
    $payment_method_code = 10; // Efectivo (ajusta si quieres mapear más métodos)

    // --- Payload completo ---
    return [
        'document'            => '01',
        'reference_code'      => (string)$cont_venta,
        'payment_form'        => $payment_form,
        'payment_method_code' => $payment_method_code,
        'send_email'          => false,
        'customer'            => $customer,  // <- ¡ahora sí existe!
        'items'               => $items,
    ];
}

/**
 * Llama a Factus con el payload
 */
function factus_emitir_factura(PDO $pdo, int $cont_venta, bool $soloValidar=false): array {
    $payload = factus_build_payload_from_cont_venta($pdo, $cont_venta);

    $path = $soloValidar ? '/v1/bills/validate' : '/v1/bills';
    [$status, $body, $err] = factus_api_request('POST', $path, $payload);

    return [
        'ok'      => ($status === 200 || $status === 201),
        'status'  => $status,
        'error'   => $err,
        'payload' => $payload,
        'resp'    => json_decode($body, true) ?: $body,
    ];
}
