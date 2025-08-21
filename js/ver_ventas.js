/* ------- Script para visualizar el detalle de la venta ________ */
document.addEventListener("DOMContentLoaded", function () {
    const verMasVentaLinks = document.querySelectorAll(".verMasVenta");
    const modalVenta = document.getElementById("modalVenta");
    const modalFactura = document.getElementById("modalFactura");
    const btnSolicitarFactura = document.getElementById("btnSolicitarFactura");
    const formFactura = document.getElementById("formFactura");
    const submitButton = formFactura.querySelector("button[type='submit']");

    verMasVentaLinks.forEach(function (link) {
        link.addEventListener("click", function (event) {
            event.preventDefault();
            const ventaId = this.getAttribute("data-id");

            fetch("detalle_venta.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `cont_venta=${ventaId}`,
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert("No se pudo cargar la información de la venta.");
                    return;
                }

                // ✅ Mostrar detalles en el modalVenta
                document.getElementById("cont_venta").textContent = `RDV-${data[0].cont_venta}`;
                document.getElementById("metodo_pago").textContent = data[0].metodo_pago;
                document.getElementById("total_cantidad_productos").textContent = data[0].total_cantidad_productos;
                document.getElementById("detalle_tiempo_registro").textContent = data[0].detalle_tiempo_registro;
                document.getElementById("documento_operador").textContent = data[0].nombre_vendedor;

                // ✅ Guardar `cont_venta` para el modalFactura
                document.getElementById("factura_cont_venta").value = data[0].cont_venta;

                // ✅ Construir la tabla de productos
                let detalleHTML = `<table border="1">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>`;

                data.forEach(producto => {
                    detalleHTML += `
                        <tr>
                            <td>${producto.nombre_producto}</td>
                            <td>${producto.cantidad_productos}</td>
                            <td>${formatoCOP(producto.precio_unitario)}</td>
                            <td>${formatoCOP(producto.sub_total)}</td>
                        </tr>`;
                });

                detalleHTML += `</tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"><strong>Total Venta:</strong></td>
                            <td><strong>${formatoCOP(data[0].total_venta)}</strong></td>
                        </tr>
                    </tfoot>
                </table>`;

                document.getElementById("detalleVenta").innerHTML = detalleHTML;

                // ✅ Mostrar el modal de detalles de venta
                modalVenta.style.display = "block";
            })
            .catch(error => console.error("❌ Error:", error));
        });
    });

    // ✅ Cerrar modal de venta al hacer clic en la 'X'
    document.getElementById("cerrarModal").addEventListener("click", function () {
        modalVenta.style.display = "none";
    });

    // ✅ Cerrar modal de venta si el usuario hace clic fuera del contenido
    window.addEventListener("click", function (event) {
        if (event.target === modalVenta) {
            modalVenta.style.display = "none";
        }
    });

    // ✅ Abrir modal de factura con `cont_venta`
    btnSolicitarFactura.addEventListener("click", function () {
        let contVentaElement = document.getElementById("cont_venta").textContent.trim();
        if (!contVentaElement) {
            alert("❌ No se encontró el número de la venta.");
            return;
        }

        // ✅ Extraer solo el número sin "RDV-"
        let contVentaClean = contVentaElement.replace("RDV-", "").trim();
        document.getElementById("factura_cont_venta").value = contVentaClean;

        // ✅ Limpiar los demás campos del formulario SIN afectar `cont_venta`
        formFactura.reset();
        document.getElementById("factura_cont_venta").value = contVentaClean;

        modalFactura.style.display = "block";
    });

    // ✅ Cerrar modal de factura al hacer clic en la 'X'
    document.getElementById("cerrarModalFactura").addEventListener("click", function () {
        modalFactura.style.display = "none";
    });

    // ✅ Cerrar modal de factura si el usuario hace clic fuera del contenido
    window.addEventListener("click", function (event) {
        if (event.target === modalFactura) {
            modalFactura.style.display = "none";
        }
    });

    // ✅ Enviar factura
    formFactura.addEventListener("submit", function (event) {
        event.preventDefault();

        let formData = new FormData(this);
        let contVentaValue = formData.get("cont_venta");

        if (!contVentaValue || contVentaValue.trim() === "") {
            alert("❌ Error: No se encontró el número de la venta.");
            return;
        }

        /* console.log("📤 Enviando factura para cont_venta:", contVentaValue); */

        // 🔹 Deshabilitar botón temporalmente para evitar doble envío
        submitButton.disabled = true;

        fetch("procesar_factura.php", {
            method: "POST",
            body: formData,
        })
        .then(response => response.text())
        .then(text => {
            /* console.log("📌 Respuesta del servidor (RAW):", text); */
            return JSON.parse(text);
        })
        .then(data => {
            if (data.success) {
                alert("✅ Factura enviada correctamente al correo del cliente.");
                modalFactura.style.display = "none";

                // ✅ Limpiar el formulario SIN borrar `cont_venta`
                formFactura.reset();
                document.getElementById("factura_cont_venta").value = contVentaValue;
            } else {
                alert("❌ Error en la factura: " + data.error);
                /* console.error("❌ Error en la respuesta del servidor:", data.error); */
            }
        })
        .catch(error => {
            /* console.error("❌ Error en AJAX:", error); */
            alert("❌ Ocurrió un error al enviar la factura.");
        })
        .finally(() => {
            // 🔹 Rehabilitar botón después de completar la solicitud
            submitButton.disabled = false;
        });
    });
});

// ✅ Función para formatear valores en Pesos Colombianos (COP)
function formatoCOP(valor) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(valor);
};
