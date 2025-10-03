<?php
/* factus/factus_mapper.php
   Convierte tu fila de `clientes` (como la tienes hoy) al bloque `customer` de Factus.
   No cambia tu DB: normaliza "sobre la marcha".
*/

function buildFactusCustomerFromCliente(array $c): array {
    // 1) Tipo de persona → legal_organization_id (1=Jurídica, 2=Natural)
    $tipoPersona  = strtolower(trim($c['tipo_persona'] ?? ''));
    $legalOrgId   = ($tipoPersona === 'juridica') ? 1 : 2;

    // 2) Tipo de documento (texto) → identification_document_id (ID de Factus)
    $idTipoDoc    = mapTipoDocumentoToFactusId($c['tipo_documento'] ?? '');

    // 3) names / company según persona
    if ($legalOrgId === 2) { // Natural
        $names   = trim(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? ''));
        $company = null;
    } else { // Jurídica
        $company = trim(($c['nom_comercial'] ?? '') !== ''
            ? $c['nom_comercial']
            : (($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? '')));
        $names   = null;
    }

    // 4) DV si es NIT
    $dv = null;
    if (strtolower(trim($c['tipo_documento'] ?? '')) === 'nit') {
        $dv = calcularDV($c['documento'] ?? '');
    }

    // 5) Email / Teléfono / Dirección
    $email = trim($c['correo'] ?? '');
    $phone = preg_replace('/\D+/', '', (string)($c['celular'] ?: $c['telefono'] ?: ''));
    $addr  = trim($c['direccion'] ?? '');

    // 6) Municipio (para empezar: opcional/fijo de prueba)
    //    Más adelante lo resolvemos por nombre o guardamos el ID real.
    //    De momento, si quieres probar ya, usa Bogotá (11001) como ejemplo.
    $municipality = ['id' => 11001]; // <-- CAMBIA cuando mapeemos bien.

    // 7) Tributo (si ya guardas un ID compatible con Factus)
    $tributeId = isset($c['tributo_id']) && $c['tributo_id'] !== '' ? (int)$c['tributo_id'] : null;

    // 8) Construir bloque
    $customer = [
        'identification_document_id' => $idTipoDoc,
        'identification'             => (string)($c['documento'] ?? ''),
        'legal_organization_id'      => $legalOrgId,
        'address'                    => $addr ?: null,
        'email'                      => $email ?: null,
        'phone'                      => $phone ?: null,
        'tribute_id'                 => $tributeId ?: null,
        'municipality'               => $municipality, // por ahora fijo p/tests
    ];

    if ($legalOrgId === 2) {
        $customer['names'] = $names ?: null;
    } else {
        $customer['company'] = $company ?: null;
        if ($dv !== null) $customer['dv'] = (int)$dv;
    }

    // Limpia nulls
    return array_filter($customer, fn($v) => !is_null($v));
}

/* Mapea tu string → ID Factus. Luego lo reemplazaremos por una tabla real. */
function mapTipoDocumentoToFactusId(string $t): int {
    $t = strtolower(trim($t));
    $map = [
        'cedula de ciudadania'   => 3,
        'cédula de ciudadanía'   => 3,
        'cedula de extranjeria'  => 5, // ajusta según tabla oficial
        'cédula de extranjería'  => 5,
        'pasaporte'              => 7,
        'nit'                    => 6,
        'estatus de proteccion temporal' => 8, // si aplica
    ];
    return $map[$t] ?? 3; // por defecto CC
}

/* Cálculo DV del NIT (algoritmo DIAN) */
function calcularDV(string $nit): int {
    $nit = preg_replace('/\D+/', '', $nit);
    $arr = array_reverse(str_split($nit));
    $p   = [3,7,13,17,19,23,29,37,41,43,47,53,59,67,71];
    $s   = 0;
    foreach ($arr as $i => $d) { $s += ((int)$d) * ($p[$i] ?? 0); }
    $r = $s % 11;
    return ($r > 1) ? (11 - $r) : $r;
}

/**
 * Convierte el detalle de una venta (cont_venta) al arreglo items[] que pide Factus,
 * usando tu esquema actual.
 *
 * - detalle_venta: cont_venta, cont_producto, cantidad_productos, precio_unitario
 * - productos: cont_producto, codigo_producto, nombre, con_iva (0/1), precio_venta
 * - Si el precio en detalle_venta ya es el que cobraste en caja (generalmente con IVA),
 *   usa $preciosIncluyenIVA=true para convertirlo a base.
 */
function buildFactusItemsFromVenta(PDO $pdo, int $contVenta, bool $preciosIncluyenIVA = true): array {
    // Tarifa IVA por defecto si el producto es "con_iva" y no trae iva_rate
    $IVA_DEFAULT = 19.0;

    $sql = "
        SELECT 
            dv.cont_producto,
            dv.cantidad_productos,
            dv.precio_unitario,                -- precio usado en caja (suele venir CON IVA)
            p.codigo_producto,
            p.nombre AS nombre_producto,
            p.con_iva,                         -- 0 = exento, 1 = gravado
            p.precio_venta,                    -- respaldo si detalle no trae precio_unitario
            COALESCE(p.iva_rate, 19.00) AS iva_rate,      -- NUEVO: tarifa IVA por producto (si aplica)
            COALESCE(p.unit_measure_id, 94) AS unit_measure_id -- NUEVO: unidad por producto (default 94)
        FROM detalle_venta dv
        LEFT JOIN productos p ON p.cont_producto = dv.cont_producto
        WHERE dv.cont_venta = :cv
        ORDER BY dv.cont_detalle_venta ASC
    ";

    $st = $pdo->prepare($sql);
    $st->execute([':cv' => $contVenta]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) return [];

    $items = [];
    foreach ($rows as $r) {
        // Cantidad
        $qty = (float)($r['cantidad_productos'] ?? 0);
        if ($qty <= 0) {
            throw new RuntimeException("Producto {$r['cont_producto']} con cantidad <= 0 en detalle_venta.");
        }

        // Precio de caja (con IVA, normalmente)
        $precioDet  = isset($r['precio_unitario']) ? (float)$r['precio_unitario'] : 0.0;
        $precioProd = isset($r['precio_venta'])    ? (float)$r['precio_venta']    : 0.0;
        $priceCaja  = $precioDet > 0 ? $precioDet : $precioProd;
        if ($priceCaja <= 0) {
            throw new RuntimeException("Producto {$r['cont_producto']} sin precio válido en detalle_venta/productos.");
        }

        // IVA segun con_iva e iva_rate
        $gravado = (int)($r['con_iva'] ?? 1) === 1;
        $rate    = $gravado ? (float)($r['iva_rate'] ?? $IVA_DEFAULT) : 0.0;

        // Factus (mapper interno) maneja 'price' como BASE (sin IVA).
        // Si en caja guardas precio con IVA, lo convertimos a base.
        $priceBase = $priceCaja;
        if ($preciosIncluyenIVA && $rate > 0) {
            $priceBase = round($priceCaja / (1 + ($rate / 100)), 6);
        }

        // Unidad de medida desde DB (default 94 = Unidad)
        $umCode = (int)($r['unit_measure_id'] ?? 94);

        // Tributos (interno del mapper). Luego los convertimos a 'tax_rate' en factura_factus.php
        $tributes = [];
        if ($rate > 0) {
            $tributes[] = ['code' => '01', 'rate' => $rate]; // 01 = IVA
        }

        // Código y nombre del producto
        $code = (string)($r['codigo_producto'] ?? $r['cont_producto']);
        $name = (string)($r['nombre_producto'] ?? 'Producto');

        $items[] = [
            'code'             => $code,
            'name'             => $name,
            'quantity'         => $qty,
            'price'            => $priceBase,                 // BASE (sin IVA)
            'measurement_unit' => ['code' => (string)$umCode],// lo convertimos a unit_measure_id más adelante
            'tributes'         => $tributes,
        ];
    }
    return $items;
}

