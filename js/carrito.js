document.addEventListener("DOMContentLoaded", function () {

    // =========================================================
    // 1) RESET CARRITO EN CARGA (si así se solicitó en otra pantalla)
    // =========================================================
    if (sessionStorage.getItem("limpiarCarrito") === "true") {
        localStorage.removeItem("carrito");
        sessionStorage.removeItem("limpiarCarrito");
    }

    // =========================================================
    // 2) ESTADO / REFERENCIAS GLOBALES
    // =========================================================
    let carrito = JSON.parse(localStorage.getItem("carrito")) || [];
    let plazoCreditoSeleccionado = null;
    let observacionesCredito = "";

    // Cliente seleccionado o recién registrado (variables GLOBALes)
    let documentoCliente = null; 
    let nombreCliente = null;
    let apellidoCliente = null;
    let correoCliente = null;

    // ⚠️ Nuevos campos globales para enviar TODO al backend
    let tipo_documentoCliente = null;
    let tipo_personaCliente = null;
    let departamentoCliente = null;
    let ciudadCliente = null;
    let direccionCliente = null;
    let tributoIdCliente = null;
    let celularCliente = null;
    let telefonoCliente = null;
    let fechaNacimientoCliente = null;
    let nombreComercialCliente = null;

    // ---------------------------------------------------------
    // DOM refs
    // ---------------------------------------------------------
    const buscarProducto = document.getElementById("buscarProducto");
    const sugerencias = document.getElementById("sugerencias");
    const nombreProducto = document.getElementById("nombreProducto");
    const disponibleProducto = document.getElementById("disponibleProducto");
    const precioProducto = document.getElementById("precioProducto");
    const cantidadProducto = document.getElementById("cantidadProducto");
    const metodoPago = document.getElementById("metodo_pago");
    const campoRecibido = document.getElementById("campoRecibido");
    const inputRecibido = document.getElementById("recibido");
    const campoCambio = document.getElementById("campoCambio");
    const cambioSpan = document.getElementById("cambio");
    const resumenTabla = document.getElementById("resumenTabla");
    const totalVentaResumen = document.getElementById("totalVenta");
    const totalItems = document.getElementById("total_items");
    /* const documentoOperador = document.getElementById("documento_operador"); */

    // Buscador cliente (modal seleccionar cliente)
    const buscarClienteInput = document.getElementById("buscarClienteInput");
    const sugerenciasCliente = document.getElementById("sugerenciasCliente");
    const tablaDatosCliente = document.getElementById("tablaDatosCliente");
    const datosClienteEncontrado = document.getElementById("datosClienteEncontrado");
    const botonRegistrarCliente = document.getElementById("botonRegistrarCliente");

    // Descuento
    const campoDescuento = document.getElementById("campoDescuento");
    const inputDescuento = document.getElementById("descuento");
    const resumenDescuento = document.getElementById("resumenDescuento");
    const subtotalAntesDescuento = document.getElementById("subtotalAntesDescuento");
    const valorDescuento = document.getElementById("valorDescuento");
    const totalConDescuento = document.getElementById("totalConDescuento");

    // =========================================================
    // 3) UTILIDADES
    // =========================================================
    function formatNumber(value) {
        let number = parseInt(value.replace(/[^0-9]/g, ""), 10);
        if (isNaN(number)) return "0";
        return "$" + number.toLocaleString("es-CO");
    }

    function unformatNumber(value) {
        if (!value) return "0";
        return value.replace(/\$/g, "").replace(/\./g, "");
    }

    // =========================================================
    // 4) BUSCAR CLIENTE (sugerencias)
    // =========================================================
    let timeoutBuscarCliente = null;
    buscarClienteInput.addEventListener("input", function () {
        const query = buscarClienteInput.value.trim();

        if (query.length < 3) {
            sugerenciasCliente.innerHTML = "";
            sugerenciasCliente.style.display = "none";
            return;
        }

        clearTimeout(timeoutBuscarCliente);
        timeoutBuscarCliente = setTimeout(() => {
            fetch(`buscar_cliente.php?query=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    sugerenciasCliente.innerHTML = "";
                    sugerenciasCliente.style.display = "block";

                    if (data.length === 0) {
                        sugerenciasCliente.innerHTML = "<div style='padding:10px;'>❌ Cliente no encontrado.</div>";
                        sugerenciasCliente.style.display = "block";
                        // Mostrar botón para registrar nuevo cliente
                        botonRegistrarCliente.style.display = "block";
                        return;
                    }

                    data.forEach(cliente => {
                        const div = document.createElement("div");
                        div.textContent = `${cliente.documento} - ${cliente.nombre} ${cliente.apellido}`;
                        div.classList.add("sugerencia");
                        div.style.padding = "10px";
                        div.style.cursor = "pointer";
                        div.style.borderBottom = "1px solid #ccc";
                        div.style.background = "white";

                        const seleccionar = () => {
                            // ⚠️ Para clientes existentes asignamos lo mínimo indispensable
                            documentoCliente = cliente.documento;
                            nombreCliente = cliente.nombre || "";
                            apellidoCliente = cliente.apellido || "";
                            correoCliente = cliente.correo || "";

                            alert(`✅ Cliente seleccionado: ${cliente.nombre} ${cliente.apellido}`);
                            document.getElementById("modalSeleccionCliente").style.display = "none";
                            buscarClienteInput.value = "";
                            sugerenciasCliente.innerHTML = "";
                            sugerenciasCliente.style.display = "none";
                        };

                        div.addEventListener("click", seleccionar);
                        div.addEventListener("touchstart", seleccionar);

                        sugerenciasCliente.appendChild(div);
                    });
                })
                .catch(err => {
                    console.error("Error al buscar cliente:", err);
                    sugerenciasCliente.innerHTML = "<div style='padding:10px;'>❌ Error al buscar cliente.</div>";
                    sugerenciasCliente.style.display = "block";
                });
        }, 300);
    });

    // Mostrar datos en tabla (si usas esta función en crédito)
    function seleccionarClienteCredito(cliente) {
        documentoCliente = cliente.documento;
        tablaDatosCliente.innerHTML = "";
        for (const campo in cliente) {
            const fila = document.createElement("tr");
            fila.innerHTML = `<td><strong>${campo}</strong></td><td>${cliente[campo]}</td>`;
            tablaDatosCliente.appendChild(fila);
        }
        datosClienteEncontrado.style.display = "block";
        botonRegistrarCliente.style.display = "none";
    }

    // =========================================================
    // 5) BUSCAR PRODUCTOS
    // =========================================================
    async function buscarProductos(query) {
        try {
            const response = await fetch(`buscar_productos.php?query=${encodeURIComponent(query)}`, {
                method: 'GET',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache'
                }
            });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error("Error en la búsqueda:", error);
            throw error;
        }
    }

    function seleccionarProducto(producto) {
        buscarProducto.value = producto.nombre;
        nombreProducto.textContent = producto.nombre;
        disponibleProducto.textContent = producto.cantidad;
        precioProducto.textContent = formatNumber(producto.precio_venta.toString());
        nombreProducto.dataset.id = producto.cont_producto;
        sugerencias.innerHTML = "";
        sugerencias.style.display = "none";
    }

    let timeoutId = null;
    buscarProducto.addEventListener("keyup", function () {
        const query = buscarProducto.value.trim();
        if (timeoutId) clearTimeout(timeoutId);

        if (query.length < 3) {
            sugerencias.innerHTML = "";
            sugerencias.style.display = "none";
            return;
        }

        timeoutId = setTimeout(async () => {
            try {
                const response = await fetch(`buscar_productos.php?query=${encodeURIComponent(query)}`);
                const data = await response.json();

                sugerencias.innerHTML = "";
                if (data.length === 0) {
                    sugerencias.style.display = "none";
                    return;
                }

                sugerencias.style.display = "block";
                data.forEach(producto => {
                    let item = document.createElement("div");
                    item.classList.add("sugerencia");
                    item.textContent = producto.nombre;
                    item.style.padding = "10px";
                    item.style.border = "1px solid black";
                    item.style.background = "white";
                    item.style.cursor = "pointer";

                    const handleSelection = (e) => {
                        e.preventDefault();
                        seleccionarProducto(producto);
                        sugerencias.style.display = "none";
                    };

                    item.addEventListener("click", handleSelection);
                    item.addEventListener("touchstart", handleSelection);
                    sugerencias.appendChild(item);
                });
            } catch (error) {
                console.error("Error en fetch:", error);
                sugerencias.innerHTML = "<div class='error'>Error al cargar productos. Intente nuevamente.</div>";
                sugerencias.style.display = "block";
            }
        }, 300);
    });

    // =========================================================
    // 6) CARRITO: render + helpers
    // =========================================================
    function actualizarCarrito() {
        resumenTabla.innerHTML = `
            <tr>
                <th>Producto</th>
                <th>Subtotal</th>
                <th>Cantidad</th>
            </tr>`;

        let total = 0;
        let items = 0;

        carrito.forEach((producto, index) => {
            const subtotal = producto.cantidad * producto.precio;
            total += subtotal;
            items += producto.cantidad;

            const row = resumenTabla.insertRow();
            row.innerHTML = `
                <td>${producto.nombre}</td>
                <td>${formatNumber(subtotal.toString())}</td>
                <td id="acciones">
                    <button onclick="modificarCantidad(${index}, -1)">-</button>
                    <span>${producto.cantidad}</span>
                    <button onclick="modificarCantidad(${index}, 1)">+</button>
                    <button id="elim_cantidad" onclick="eliminarProducto(${index})"><i class="lni lni-trash-can"></i></button>
                </td>`;
        });

        totalVentaResumen.textContent = formatNumber(total.toString());
        totalItems.textContent = items;
        localStorage.setItem("carrito", JSON.stringify(carrito));

        if (campoDescuento && campoDescuento.style.display === "block") {
            actualizarDescuento();
        }
    }

    window.eliminarProducto = function (index) {
        carrito.splice(index, 1);
        actualizarCarrito();
    };

    window.modificarCantidad = function (index, cambio) {
        carrito[index].cantidad += cambio;
        if (carrito[index].cantidad <= 0) {
            eliminarProducto(index);
        } else {
            actualizarCarrito();
        }
    };

    // =========================================================
    // 7) DESCUENTO
    // =========================================================
    function actualizarDescuento() {
        const totalOriginal = parseInt(unformatNumber(totalVentaResumen.textContent)) || 0;
        const descuento = parseInt(inputDescuento.value) || 0;

        if (descuento < 0) {
            alert("⚠️ El descuento no puede ser negativo.");
            inputDescuento.value = 0;
            return;
        }
        if (descuento > totalOriginal) {
            alert("⚠️ El descuento no puede ser mayor al total.");
            inputDescuento.value = 0;
            return;
        }

        const nuevoTotal = totalOriginal - descuento;
        subtotalAntesDescuento.textContent = formatNumber(totalOriginal.toString());
        valorDescuento.textContent = formatNumber(descuento.toString());
        totalConDescuento.textContent = formatNumber(nuevoTotal.toString());

        if (inputRecibido.value) {
            let recibidoTexto = inputRecibido.value.replace(/\./g, "").replace(/\D/g, "");
            let recibido = parseInt(recibidoTexto) || 0;
            const cambio = recibido - nuevoTotal;
            cambioSpan.textContent = `$${cambio.toLocaleString("es-CO")}`;
            campoCambio.style.display = "block";
        }
    }
    inputDescuento.addEventListener("input", actualizarDescuento);

    // =========================================================
    // 8) AGREGAR PRODUCTO AL CARRITO
    // =========================================================
    document.getElementById("agregarProducto").addEventListener("click", function () {
        const nombre = nombreProducto.textContent;
        const cont_producto = nombreProducto.dataset.id;
        const precio = parseInt(unformatNumber(precioProducto.textContent));
        const cantidad = parseInt(cantidadProducto.value);

        if (!nombre || isNaN(precio) || isNaN(cantidad) || cantidad <= 0) {
            alert("Debe seleccionar un producto válido y una cantidad mayor a 0.");
            return;
        }

        if (!documentoCliente) {
            alert("Debes registrar/seleccionar el cliente antes de agregar productos.");
            document.getElementById("modalSeleccionCliente").style.display = "block";
            return;
        }

        carrito.push({ cont_producto, nombre, cantidad, precio });
        actualizarCarrito();

        buscarProducto.value = "";
        nombreProducto.textContent = "";
        disponibleProducto.textContent = "";
        precioProducto.textContent = "";
        cantidadProducto.value = "";
    });

    // =========================================================
    // 9) MÉTODO DE PAGO + EFECTIVO (RECIBIDO/CAMBIO)
    // =========================================================
    metodoPago.addEventListener("change", function () {
        const metodo = metodoPago.value;

        if (metodo === "efectivo") {
            campoRecibido.style.display = "block";
            campoDescuento.style.display = "block";
            resumenDescuento.style.display = "block";
            inputRecibido.value = "";
            campoCambio.style.display = "none";
            actualizarDescuento();
        } else {
            campoRecibido.style.display = "none";
            campoDescuento.style.display = "block";
            resumenDescuento.style.display = "block";
            campoCambio.style.display = "none";
            inputRecibido.value = "";
            actualizarDescuento();
        }

        if (metodo === "credito") {
            document.getElementById("modalCreditoCliente").style.display = "block";
            if (documentoCliente === "22222222222") {
                alert("❌ No se permite venta a crédito con Consumidor Final. Selecciona un cliente real.");
                metodoPago.value = "";
                return;
            }
        }
    });

    let timeoutRecibido = null;
    inputRecibido.addEventListener("input", function (e) {
        let valorNumerico = e.target.value.replace(/\D/g, "");
        if (valorNumerico === "") {
            e.target.value = "";
            cambioSpan.textContent = "$0";
            campoCambio.style.display = "block";
            return;
        }
        if (valorNumerico.length > 9) {
            valorNumerico = valorNumerico.substring(0, 9);
        }
        const numeroFormateado = parseInt(valorNumerico).toLocaleString("es-CO");
        e.target.value = numeroFormateado;

        clearTimeout(timeoutRecibido);
        timeoutRecibido = setTimeout(() => {
            const recibidoSinFormato = parseInt(valorNumerico) || 0;
            let totalPagar;
            if (metodoPago.value === "efectivo" && inputDescuento.value) {
                totalPagar = parseInt(unformatNumber(totalConDescuento.textContent)) || 0;
            } else {
                totalPagar = parseInt(unformatNumber(totalVentaResumen.textContent)) || 0;
            }

            if (recibidoSinFormato >= totalPagar) {
                const cambio = recibidoSinFormato - totalPagar;
                cambioSpan.textContent = `$${cambio.toLocaleString("es-CO")}`;
                campoCambio.style.display = "block";
            } else {
                cambioSpan.textContent = "$0";
                campoCambio.style.display = "block";
            }
        }, 300);
    });

    // =========================================================
    // 10) CRÉDITO: CONFIRMAR DATOS
    // =========================================================
    document.getElementById("confirmarCreditoVenta").addEventListener("click", function () {
        const plazo = document.getElementById("plazoCredito").value;
        const obs = document.getElementById("observacionesCredito").value.trim();

        if (!documentoCliente) {
            alert("Debes seleccionar un cliente válido para venta a crédito.");
            return;
        }

        observacionesCredito = obs.length > 0 ? obs : "Sin observaciones";
        plazoCreditoSeleccionado = parseInt(plazo);

        document.getElementById("modalCreditoCliente").style.display = "none";
        alert("✅ Datos de crédito registrados. Ahora puedes finalizar la venta.");
    });

    // =========================================================
    // 11) REGISTRAR NUEVO CLIENTE (modal): ENVÍA TODO AL BACKEND
    // =========================================================
    document.getElementById("registrarNuevoCliente").addEventListener("click", async function () {
        // Tomar valores del modal
        const nombre           = document.getElementById("nuevoNombre").value.trim();
        const apellido         = document.getElementById("nuevoApellido").value.trim();
        const fecha_nacimiento = document.getElementById("nuevaFechaNacimiento").value; // puede ir vacío en jurídica
        const tipo_documento   = document.getElementById("nuevoTipoDocumento").value;
        const doc              = document.getElementById("nuevoDocumento").value.trim();
        const tipo_persona     = document.getElementById("nuevoTipoPersona").value;
        const nom_comercial    = document.getElementById("nuevoNombreComercial").value.trim();
        const departamento     = document.getElementById("nuevoDepartamento").value;
        const ciudad           = document.getElementById("nuevoMunicipio").value;
        const direccion        = document.getElementById("nuevaDireccion").value.trim();
        const celular          = document.getElementById("nuevoCelular").value.trim();
        const telefono         = document.getElementById("nuevoTelefono").value.trim();
        const correo           = document.getElementById("nuevoCorreo").value.trim();
        const tributo_id       = document.getElementById("nuevoTributo").value;
        

        // Validaciones mínimas
        if (!tipo_persona || !tipo_documento || !doc || !departamento || !ciudad || !direccion || !celular || !correo || !tributo_id) {
            alert("⚠ Completa todos los campos obligatorios.");
            return;
        }
        if (tipo_persona === "natural" && (!nombre || !apellido)) {
            alert("⚠ Nombre y apellido obligatorios para persona natural.");
            return;
        }

        // Construir POST a registrar_clientes.php (FormData)
        const fd = new FormData();
        fd.append("tipo_persona",     tipo_persona);
        fd.append("tipo_documento",   tipo_documento);
        fd.append("documento",        doc);
        fd.append("nombre",           nombre);
        fd.append("apellido",         apellido);
        fd.append("fecha_nacimiento", fecha_nacimiento); // "" si no aplica
        fd.append("celular",          celular);
        fd.append("telefono",         telefono);         // opcional
        fd.append("correo",           correo);
        fd.append("departamento",     departamento);
        fd.append("ciudad",           ciudad);
        fd.append("direccion",        direccion);
        fd.append("nom_comercial",    nom_comercial);
        fd.append("tributo_id",       tributo_id);

        try {
            const resp = await fetch("registrar_clientes.php", {
                method: "POST",
                body: fd,
                credentials: "include"
            });

            // Si el servidor redirige (flujo PHP clásico)
            if (resp.redirected) {
                // Guardar globales para continuar la venta
                asignarGlobalesCliente(
                    doc, nombre, apellido, correo, tipo_documento, tipo_persona,
                    departamento, ciudad, direccion, tributo_id, celular, telefono,
                    fecha_nacimiento, nom_comercial
                );
                alert("✅ Cliente registrado y seleccionado.");
                cerrarModalesCliente();
                // window.location.href = resp.url; // opcional, si quieres seguir la redirección
                return;
            }

            // Si no hay redirect, intentamos leer texto o JSON (según tu PHP)
            const texto = await resp.text();
            if (resp.ok) {
                asignarGlobalesCliente(
                    doc, nombre, apellido, correo, tipo_documento, tipo_persona,
                    departamento, ciudad, direccion, tributo_id, celular, telefono,
                    fecha_nacimiento, nom_comercial
                );
                alert("✅ Cliente registrado y seleccionado.");
                cerrarModalesCliente();
            } else {
                console.error(texto);
                alert("❌ Error al registrar el cliente.");
            }
        } catch (e) {
            console.error(e);
            alert("❌ Error de red al registrar el cliente.");
        }
    });

    function asignarGlobalesCliente(
        doc, nombre, apellido, correo, tipo_documento, tipo_persona,
        departamento, ciudad, direccion, tributo_id, celular, telefono,
        fecha_nacimiento, nom_comercial
    ) {
        documentoCliente       = doc;
        nombreCliente          = nombre || "";
        apellidoCliente        = apellido || "";
        correoCliente          = correo || "";
        tipo_documentoCliente  = tipo_documento || "";
        tipo_personaCliente    = tipo_persona || "";
        departamentoCliente    = departamento || "";
        ciudadCliente          = ciudad || "";
        direccionCliente       = direccion || "";
        tributoIdCliente       = tributo_id || "";
        celularCliente         = celular || "";
        telefonoCliente        = telefono || "";
        fechaNacimientoCliente = fecha_nacimiento || "";
        nombreComercialCliente = nom_comercial || "";
    }

    function cerrarModalesCliente() {
        const f = document.getElementById("formNuevoCliente");
        const m = document.getElementById("modalSeleccionCliente");
        if (f) f.style.display = "none";
        if (m) m.style.display = "none";
    }

    document.getElementById("abrirRegistroCliente").addEventListener("click", function () {
        document.getElementById("formNuevoCliente").style.display = "block";
        botonRegistrarCliente.style.display = "none";
    });

    // =========================================================
    // 12) FINALIZAR COMPRA: ENVÍA VENTA + DATOS DE CLIENTE
    // =========================================================
    document.getElementById("finalizarCompra").addEventListener("click", function () {
        if (carrito.length === 0) {
            alert("El carrito está vacío.");
            return;
        }
        if (!metodoPago.value) {
            alert("Debe seleccionar un método de pago antes de continuar.");
            return;
        }

        let metodo = metodoPago.value;
        let recibido = null;

        // Descuento
        let descuento = parseFloat(inputDescuento.value) || 0;
        let totalOriginal = parseInt(unformatNumber(totalVentaResumen.textContent)) || 0;
        let totalPagar = totalOriginal;
        if (descuento > 0) {
            totalPagar = parseInt(unformatNumber(totalConDescuento.textContent)) || 0;
        }

        if (metodo === "efectivo") {
            let recibidoTexto = inputRecibido.value.replace(/\./g, "").replace(/\D/g, "");
            recibido = parseInt(recibidoTexto) || 0;
            if (recibido < totalPagar) {
                alert("El monto recibido es inferior al total.");
                return;
            }
        }

        let cambio = null;
        if (metodo === "efectivo") {
            cambio = (recibido - totalPagar);
        }

        /* const documento = documentoOperador.value;
        if (!documento) {
            alert("Error: No se ha podido obtener el documento del operador.");
            return;
        } */

        if (!documentoCliente) {
            document.getElementById("modalSeleccionCliente").style.display = "block";
            return;
        }

        // ---- Construir el payload de la venta
        let bodyData = {
            productos: carrito,
            metodo_pago: metodo,
            /* documento_operador: documento, */
            estado: "activo",
            recibido: recibido,
            cambio: cambio,
            descuento_en_pesos: descuento,
            total_original: totalOriginal,
            total_con_descuento: totalPagar,
            documento_cliente: documentoCliente
        };

        // 👉 Si tenemos datos del cliente (nuevo o seleccionado con datos completos), adjuntarlos
        if (documentoCliente) {
            bodyData.nombre_cliente            = nombreCliente || "";
            bodyData.apellido_cliente          = apellidoCliente || "";
            bodyData.tipo_documento_cliente    = tipo_documentoCliente || "";
            bodyData.tipo_persona_cliente      = tipo_personaCliente || "";
            bodyData.departamento_cliente      = departamentoCliente || "";
            bodyData.ciudad_cliente            = ciudadCliente || "";
            bodyData.direccion_cliente         = direccionCliente || "";
            bodyData.correo_cliente            = correoCliente || "";
            bodyData.tributo_id_cliente        = tributoIdCliente || "";
            bodyData.celular_cliente           = celularCliente || "";
            bodyData.telefono_cliente          = telefonoCliente || "";
            bodyData.fecha_nacimiento_cliente  = fechaNacimientoCliente || "";
            bodyData.nom_comercial_cliente     = nombreComercialCliente || "";
        }

        // 👉 Datos de crédito (si aplica)
        if (metodo === "credito") {
            if (!plazoCreditoSeleccionado || !observacionesCredito) {
                alert("Faltan los datos del crédito. Por favor verifica el plazo y las observaciones.");
                return;
            }
            bodyData.plazo_credito = plazoCreditoSeleccionado;
            bodyData.observaciones = observacionesCredito;
        }

        // Suponiendo que creaste una tabla o contenedor con filas de pago:
        function colectarPagosDesdeUI() {
        // Ejemplo de estructura: cada fila tiene .selMetodo, .inpMonto, .inpRef
        const filas = document.querySelectorAll(".fila-pago");
        const pagos = [];
        filas.forEach(f => {
            const metodo = f.querySelector(".selMetodo").value;     // 'efectivo', 'nequi', 'transferencia', 'tarjeta', etc.
            const monto  = parseInt((f.querySelector(".inpMonto").value || "0").replace(/\D/g,""), 10) || 0;
            const ref    = (f.querySelector(".inpRef")?.value || "").trim();
            if (metodo && monto > 0) {
            pagos.push({ metodo, monto, referencia: ref });
            }
        });
        return pagos;
        }

        // Al confirmar:
        const pagos = colectarPagosDesdeUI();
        bodyData.pagos = pagos;

        // si no usas pagos mixtos, puedes no enviar bodyData.pagos y seguir con 'metodo_pago' clásico
        // si hay varios pagos, puedes marcar:
        bodyData.metodo_pago = (pagos && pagos.length > 1) ? 'mixto' : (pagos[0]?.metodo || metodoPago.value);

        // carrito.js
        fetch("registrar_venta.php", {
        method: "POST",
        headers: { "Content-Type": "application/json", "Accept": "application/json" },
        credentials: "include",
        body: JSON.stringify(bodyData)
        })


        .then(async (resp) => {
        const status = resp.status;
        const statusText = resp.statusText;
        const ct = resp.headers.get("content-type") || "";
        const raw = await resp.text(); // leemos SIEMPRE el cuerpo como texto

        let data = null;
        if (ct.includes("application/json")) {
            try { data = JSON.parse(raw); } catch (e) {
            console.error("⚠️ JSON inválido en la respuesta:", e, "\nCuerpo crudo:", raw);
            }
        }

        console.log("HTTP", status, statusText);
        console.log("Content-Type:", ct);
        console.log("Cuerpo crudo:", raw);
        console.log("Objeto parseado:", data);

        if (!resp.ok) {
            // 4xx/5xx: muestra todo lo que vino
            alert(`❌ HTTP ${status} ${statusText}\n${raw.substring(0, 1000)}`);
            return;
        }

        if (!data) {
            alert("❌ Respuesta sin JSON válido. Revisa la consola (cuerpo crudo).");
            return;
        }

        // ------- A partir de aquí tu flujo normal ---------
        window._rv = data;
        sessionStorage.setItem("_rv", JSON.stringify(data));

        if (data.success) {
            const cv = data.cont_venta;
            const f  = data.factus || {};

            alert(
            `✅ Venta #${cv} OK\n` +
            `Factus ok: ${f.ok}\n` +
            `status: ${f.status}\n` +
            `Number: ${(f.data && f.data.number) || '(sin número)'}\n` +
            `URL: ${(f.data && f.data.public_url) || '(sin url)'}`
            );

            // NO redirijas mientras depuras
            // window.location.href = "ver_ventas.php";
        } else {
            alert(data.error || 'No se pudo registrar la venta.');
        }
        })
        .catch(error => {
        // Aquí sólo caes si fue error de red / CORS / abort / excepción
        console.error("Error en la solicitud:", error);
        alert("Error al procesar la venta. Por favor, intente nuevamente.");
        });

    });

    // =========================================================
    // 13) BOTONES MODALES VARIOS
    // =========================================================
    document.getElementById("btnClienteGenerico").addEventListener("click", function () {
        documentoCliente = "22222222222"; // Cliente genérico oficial
        alert("🧾 Se usará 'Consumidor Final' para esta venta.");
        document.getElementById("modalSeleccionCliente").style.display = "none";
    });

    document.getElementById("cerrarModalSeleccion").addEventListener("click", function () {
        document.getElementById("modalSeleccionCliente").style.display = "none";
    });

    document.getElementById("cerrarModalCredito").addEventListener("click", function () {
        document.getElementById("modalCreditoCliente").style.display = "none";
    });

    // =========================================================
    // 14) INICIO: pintar carrito y (opcional) abrir modal cliente
    // =========================================================
    actualizarCarrito();

    setTimeout(() => {
        if (!documentoCliente) {
            document.getElementById("modalSeleccionCliente").style.display = "block";
        }
    }, 100);
});

