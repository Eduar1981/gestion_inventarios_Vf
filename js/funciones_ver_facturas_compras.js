document.querySelectorAll('.verMasFactComp').forEach(function (link) {
    link.addEventListener('click', function (event) {
        event.preventDefault();

        const facturaNum = this.getAttribute('data-factura');

        function formatearFecha(fechaISO) {
            const partes = fechaISO.split("-"); // [aaaa, mm, dd]
            return `${partes[2]}-${partes[1]}-${partes[0]}`; // dd-mm-aaaa
        }
        

        fetch('detalle_factura_compra.php', {
            method: 'POST',
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `num_fact_comp=${facturaNum}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert("❌ No se pudo cargar la información de la factura.");
                return;
            }

            // ✅ Mostrar info general de la factura
            document.getElementById("facturaTitulo").textContent = data.factura.num_fact_comp;
            document.getElementById("proveedorFactura").textContent = data.factura.nom_comercial;
            document.getElementById("fechaFactura").textContent = formatearFecha(data.factura.fecha_compra);
            document.getElementById("fechaPagoFactura").textContent = formatearFecha(data.factura.fecha_pago_fact_comp);
            document.getElementById("totalFactura").textContent = formatoCOP(data.factura.precio_compra_total);

            // ✅ Construir tabla de productos
            let detalleHTML = `<table border="1">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Precio Compra</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>`;

            data.productos.forEach(prod => {
                const subtotal = prod.cantidad * prod.precio_compra;
                detalleHTML += `
                    <tr>
                        <td>${prod.nombre}</td>
                        <td>${prod.descripcion}</td>
                        <td>${prod.cantidad}</td>
                        <td>${formatoCOP(prod.precio_compra)}</td>
                        <td>${formatoCOP(subtotal)}</td>
                    </tr>`;
            });

            detalleHTML += `</tbody></table>`;

            document.getElementById("detalleFactura").innerHTML = detalleHTML;

            // ✅ Mostrar modal
            document.getElementById("modalFactura").style.display = "block";
        })
        .catch(error => {
            console.error("❌ Error:", error);
            document.getElementById("detalleFactura").innerHTML = "<p style='color:red;'>Error al cargar los datos.</p>";
        });
    });
});

// ✅ Cerrar modal
document.getElementById("cerrarModal").addEventListener("click", function () {
    document.getElementById("modalFactura").style.display = "none";
});

// ✅ Cerrar al hacer clic fuera del modal
window.addEventListener("click", function (e) {
    const modal = document.getElementById("modalFactura");
    if (e.target === modal) {
        modal.style.display = "none";
    }
});

// ✅ Función para formatear precios en pesos colombianos
function formatoCOP(valor) {
    return new Intl.NumberFormat("es-CO", {
        style: "currency",
        currency: "COP",
        minimumFractionDigits: 0
    }).format(valor);
};

/* Script para buscar y mostrar sugerencias de facturas de compra */
$(document).ready(function () {
    /* console.log("✅ Script cargado"); */

    // 1. Búsqueda dinámica de facturas
    $("#buscarFactura").on("keyup", function () {
        let query = $(this).val().trim();
        /* console.log("🔍 Input detectado:", query); */

        if (query.length >= 3 || query.length === 0) {
            $.ajax({
                url: "buscar_facturas_compras.php",
                type: "GET",
                data: { q: query },
                dataType: "json",
                success: function (data) {
                    let tablaFacturas = $("#tablaFacturas");
                    tablaFacturas.empty();

                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(fact => {
                            tablaFacturas.append(`
                                <tr>
                                    <td>${fact.nom_comercial}</td>
                                    <td>${fact.fecha_compra}</td>
                                    <td>${fact.num_fact_comp}</td>
                                    <td>
                                        <a class="verMasFactComp" data-id="${fact.cont_fact_compra}">
                                        <i class='bx bx-plus-circle' style="font-size: 20px;"></i>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tablaFacturas.append('<tr><td colspan="4">No se encontraron facturas</td></tr>');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("❌ Error AJAX:", error);
                    $("#tablaFacturas").html('<tr><td colspan="4">Error al buscar facturas</td></tr>');
                }
            });
        } else {
            /* console.log("❗ Input con menos de 3 letras, no se hace búsqueda."); */
        }
    });

    // 2. Delegar el click para ver más (modal)
    $(document).on("click", ".verMasFactComp", function (e) {
        e.preventDefault();

        const factura = $(this).data("factura");
        const proveedor = $(this).data("proveedor");
        const fechaCompra = $(this).data("fecha-compra");
        const fechaPago = $(this).data("fecha-pago");
        const total = $(this).data("precio_compra_total");

        /* console.log("🧾 Factura:", factura);
        console.log("👤 Proveedor:", proveedor); */

        // Mostrar el modal
        $("#modalFactura").show(); // Si no usas Bootstrap

        // Rellenar datos del modal
        $("#facturaTitulo").text(factura);
        $("#proveedorFactura").text(proveedor);
        $("#fechaFactura").text(fechaCompra);
        $("#fechaPagoFactura").text(fechaPago);
        $("#totalFactura").text(total);
    });

    // 3. Cerrar modal con la X
    $("#cerrarModal").on("click", function () {
        $("#modalFactura").hide();
    });

    // 4. Cerrar modal al hacer clic fuera del contenido
    $(window).on("click", function (e) {
        if ($(e.target).is("#modalFactura")) {
            $("#modalFactura").hide();
        }
    });
});

/* --- Script para descargar PDF del modal de la factura de compra a proveedor --- */
document.getElementById("btnImprimirFactura").addEventListener("click", function () {
    const cerrarX = document.getElementById("cerrarModal");
    cerrarX.style.display = "none"; // 👈 ocultar la X temporalmente

    const elemento = document.getElementById("contenidoFacturaPDF");

    const opciones = {
        margin:       0.5,
        filename:     `factura_${document.getElementById("facturaTitulo").textContent}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
    };

    setTimeout(() => {
        html2pdf().set(opciones).from(elemento).save().then(() => {
            cerrarX.style.display = "inline"; // 👈 volver a mostrar después
        });
    }, 300);
});
