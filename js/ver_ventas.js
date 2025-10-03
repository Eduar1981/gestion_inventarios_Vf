/* ------- Script para visualizar el detalle de la venta ------- */
document.addEventListener("DOMContentLoaded", function () {
  // ===================== Helpers ======================
  // setText: escribe en .textContent o .value según el elemento.
  const setText = (id, val) => {
    const el = document.getElementById(id);
    if (!el) return;
    if ("value" in el) el.value = val;
    else el.textContent = val;
  };

  const formatoCOP = (valor) =>
    new Intl.NumberFormat("es-CO", {
      style: "currency",
      currency: "COP",
      minimumFractionDigits: 0,
    }).format(Number(valor) || 0);

  // ===================== Refs UI ======================
  const verMasVentaLinks = document.querySelectorAll(".verMasVenta");
  const modalVenta = document.getElementById("modalVenta");
  const modalFactura = document.getElementById("modalFactura");
  const btnSolicitarFactura = document.getElementById("btnSolicitarFactura");
  const formFactura = document.getElementById("formFactura");
  const submitButton = formFactura ? formFactura.querySelector("button[type='submit']") : null;

  // ===================== Abrir modal de venta ======================
  verMasVentaLinks.forEach(function (link) {
    link.addEventListener("click", function (event) {
      event.preventDefault();
      const ventaId = this.getAttribute("data-id");

      fetch("detalle_venta.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `cont_venta=${encodeURIComponent(ventaId)}`,
      })
        .then((r) => r.json())
        .then((data) => {
          if (!data || data.error || !Array.isArray(data) || data.length === 0) {
            alert("No se pudo cargar la información de la venta.");
            return;
          }

          // ===== Cabecera del modal (solo IDs que existen) =====
          setText("cont_venta", `RDV-${data[0].cont_venta}`);
          setText("metodo_pago", data[0].metodo_pago);
          setText("total_cantidad_productos", data[0].total_cantidad_productos);
          setText("detalle_tiempo_registro", data[0].detalle_tiempo_registro);
          setText("documento_operador", data[0].nombre_vendedor);
          setText("cliente_documento", data[0].documento_cliente || "");
          setText("cliente_nombre",    data[0].nombre_cliente   || "");

          // Guardar cont_venta para el modal de factura (input hidden)
          setText("factura_cont_venta", data[0].cont_venta);

          // ===== Tabla de productos con totales en <tfoot> =====
          const metPago = (data[0].metodo_pago || "").toLowerCase();
          let subtotalTabla = 0;

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

          data.forEach((item) => {
            const sub = Number(item.sub_total || 0);
            subtotalTabla += sub;
            detalleHTML += `
              <tr>
                <td>${item.nombre_producto}</td>
                <td>${item.cantidad_productos}</td>
                <td>${formatoCOP(item.precio_unitario)}</td>
                <td>${formatoCOP(sub)}</td>
              </tr>`;
          });

          const descuentoTotal = Number(data[0].descuento_total || 0);
          const totalVenta = Number(data[0].total_venta || 0);
          const recibido = Number(data[0].recibido || 0);
          const cambio = Number(data[0].cambio || 0);

          detalleHTML += `</tbody>
            <tfoot>
              <tr>
                <td colspan="3"><strong>Subtotal:</strong></td>
                <td>${formatoCOP(subtotalTabla)}</td>
              </tr>
              <tr>
                <td colspan="3"><strong>Descuento aplicado:</strong></td>
                <td>-${formatoCOP(descuentoTotal)}</td>
              </tr>
              <tr>
                <td colspan="3"><strong>Total Venta:</strong></td>
                <td><strong>${formatoCOP(totalVenta)}</strong></td>
              </tr>
              ${
                metPago === "efectivo"
                  ? `
              <tr>
                <td colspan="3"><strong>Recibido:</strong></td>
                <td>${formatoCOP(recibido)}</td>
              </tr>
              <tr>
                <td colspan="3"><strong>Cambio:</strong></td>
                <td>${formatoCOP(cambio)}</td>
              </tr>`
                  : ``
              }
            </tfoot>
          </table>`;

          const contDetalle = document.getElementById("detalleVenta");
          if (contDetalle) contDetalle.innerHTML = detalleHTML;

          // Mostrar el modal
          if (modalVenta) modalVenta.style.display = "block";
        })
        .catch((error) => {
          console.error("❌ Error:", error);
          alert("Ocurrió un error al cargar el detalle.");
        });
    });
  });

  // ===================== Cerrar modal de venta ======================
  const cerrarModalBtn = document.getElementById("cerrarModal");
  if (cerrarModalBtn) {
    cerrarModalBtn.addEventListener("click", function () {
      if (modalVenta) modalVenta.style.display = "none";
    });
  }
  window.addEventListener("click", function (event) {
    if (event.target === modalVenta) {
      modalVenta.style.display = "none";
    }
  });

  // ===================== Abrir modal de factura ======================
  if (btnSolicitarFactura) {
    btnSolicitarFactura.addEventListener("click", function () {
      const contVentaElement = (document.getElementById("cont_venta")?.textContent || "").trim();
      if (!contVentaElement) {
        alert("❌ No se encontró el número de la venta.");
        return;
      }
      const contVentaClean = contVentaElement.replace("RDV-", "").trim();
      setText("factura_cont_venta", contVentaClean);

      if (formFactura) {
        const cv = contVentaClean; // preserva antes de reset
        formFactura.reset();
        setText("factura_cont_venta", cv);
      }
      if (modalFactura) modalFactura.style.display = "block";
    });
  }

  // ===================== Cerrar modal de factura ======================
  const cerrarModalFacturaBtn = document.getElementById("cerrarModalFactura");
  if (cerrarModalFacturaBtn) {
    cerrarModalFacturaBtn.addEventListener("click", function () {
      if (modalFactura) modalFactura.style.display = "none";
    });
  }
  window.addEventListener("click", function (event) {
    if (event.target === modalFactura) {
      modalFactura.style.display = "none";
    }
  });

  // ===================== Enviar factura ======================
  if (formFactura) {
    formFactura.addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new FormData(this);
      const contVentaValue = (formData.get("cont_venta") || "").toString().trim();

      if (!contVentaValue) {
        alert("❌ Error: No se encontró el número de la venta.");
        return;
      }

      if (submitButton) submitButton.disabled = true;

      fetch("procesar_factura.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.text())
        .then((text) => {
          try {
            return JSON.parse(text);
          } catch (e) {
            throw new Error("Respuesta inválida del servidor");
          }
        })
        .then((data) => {
          if (data.success) {
            alert("✅ Factura enviada correctamente al correo del cliente.");
            if (modalFactura) modalFactura.style.display = "none";
            formFactura.reset();
            setText("factura_cont_venta", contVentaValue); // mantener el id de venta
          } else {
            alert("❌ Error en la factura: " + (data.error || "Desconocido"));
          }
        })
        .catch((error) => {
          console.error("❌ Error en AJAX:", error);
          alert("❌ Ocurrió un error al enviar la factura.");
        })
        .finally(() => {
          if (submitButton) submitButton.disabled = false;
        });
    });
  }
});
