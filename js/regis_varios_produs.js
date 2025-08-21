document.querySelector("form").addEventListener("submit", function(event) {
    let productos = document.querySelectorAll(".producto");
    let error = false;

    productos.forEach(producto => {
        let precioVentaInput = producto.querySelector(".precio_venta");

        // 🔹 Obtener el valor sin símbolos y convertirlo correctamente
        let precioSugerido = precioVentaInput.value.trim(); // Eliminar espacios extra
        precioSugerido = precioSugerido.replace(/[^\d,]/g, "").replace(",", "."); // Mantener solo números y coma decimal
        precioSugerido = parseFloat(precioSugerido); // Convertir a número real

        if (isNaN(precioSugerido) || precioSugerido <= 0) {
            /* console.warn(`⚠️ Precio sugerido vacío o inválido en el producto ${producto.id}`); */
            error = true;
            precioVentaInput.style.border = "2px solid red"; // Resaltar en rojo
        } else {
            precioVentaInput.style.border = ""; // Resetear estilo
        }

        // ✅ **Asegurar que el valor numérico limpio se asigne antes de enviar**
        precioVentaInput.value = precioSugerido;
    });

    if (error) {
        event.preventDefault(); // Evita el envío si hay errores
        alert("⚠️ Hay productos sin precio sugerido válido. Corrige antes de enviar.");
    }
});


document.addEventListener("DOMContentLoaded", function () {
    // **Delegación de eventos para productos existentes y dinámicos**
    document.addEventListener("input", function (event) {
        if (event.target.matches(".precio_compra, .porcentaje_ganancia")) {
            /* console.log(`🔍 Evento detectado en: ${event.target.className}, valor: ${event.target.value}`); */
            actualizarPrecioSugerido(event.target);
        }
    });

    document.addEventListener("change", function (event) {
        if (event.target.matches(".con_iva")) {
            /* console.log(`🔍 Evento detectado en: ${event.target.className}, estado: ${event.target.checked}`); */
            actualizarPrecioSugerido(event.target);
        }
    });
});

// **Función para actualizar el precio sugerido**
function actualizarPrecioSugerido(input) {
    /* console.log("📌 Ejecutando actualizarPrecioSugerido..."); */

    // **Buscar el contenedor del producto donde ocurrió el cambio**
    let producto = input.closest(".producto");

    if (!producto) {
        /* console.error("⚠️ No se encontró el contenedor del producto."); */
        return;
    }

    /* console.log("✅ Producto encontrado:", producto); */

    // **Obtener los elementos dentro del producto**
    let precioCompraInput = producto.querySelector(".precio_compra");
    let conIVAElement = producto.querySelector(".con_iva");
    let porcentajeGananciaInput = producto.querySelector(".porcentaje_ganancia");
    let inputPrecioVenta = producto.querySelector(".precio_venta");

    // **Verificar si los elementos existen**
    if (!precioCompraInput || !inputPrecioVenta || !porcentajeGananciaInput || !conIVAElement) {
        /* console.warn("⚠️ Uno o más elementos no están disponibles en el DOM."); */
        return;
    }

    /* console.log(`📊 Datos Capturados:
        🔹 Precio Compra: ${precioCompraInput.value}
        🔹 Con IVA: ${conIVAElement ? conIVAElement.checked : "No existe"}
        🔹 Porcentaje Ganancia: ${porcentajeGananciaInput ? porcentajeGananciaInput.value : "No existe"}`); */

    // **Obtener valores numéricos correctos**
    let precioCompra = convertirNumero(precioCompraInput.value);
    let porcentajeGanancia = porcentajeGananciaInput ? convertirNumero(porcentajeGananciaInput.value) : 0;
    let conIVA = (conIVAElement && conIVAElement.checked) ? 1.19 : 1; // Si está marcado, aplica 19% de IVA

    // **Validar valores**
    if (precioCompra === 0 || porcentajeGanancia >= 100) {
        /* console.warn("⚠️ El precio de compra es 0 o el porcentaje de ganancia es inválido."); */
        inputPrecioVenta.value = ""; // Dejar vacío si no hay un cálculo válido
        return;
    }

    // **Calcular el precio con IVA si aplica**
    let precioCompraFinal = precioCompra * conIVA;

    // **Calcular el precio de venta sugerido con la fórmula correcta**
    let precioSugerido = precioCompraFinal / ((100 - porcentajeGanancia) / 100);

    // **Formatear el resultado a COP**
    inputPrecioVenta.value = formatearNumeroCOP(precioSugerido);

    /* console.log(`✅ Precio sugerido actualizado: ${inputPrecioVenta.value}`); */
}

// **Función para convertir valores de string a número correctamente**
function convertirNumero(valor) {
    if (!valor) return 0; // Si está vacío, retorna 0
    let numero = parseFloat(valor.replace(/\./g, "").replace(",", ".")); // Convierte puntos de miles a nada y comas a punto decimal
    return isNaN(numero) ? 0 : numero; // Si no es número, retorna 0
}

// **Función para formatear el número en pesos colombianos (COP)**
function formatearNumeroCOP(numero) {
    return new Intl.NumberFormat("es-CO", {
        style: "currency",
        currency: "COP",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(numero);
}

// **Manejo de productos dinámicos** 
let productoCount = 1;

document.getElementById('agregar_producto').addEventListener('click', function (event) {
    event.preventDefault(); // Evita el comportamiento por defecto del botón
    agregarOtroProducto();
});

function agregarOtroProducto() {
    productoCount++;

    const nuevoProducto = generarProductoHTML(productoCount);
    document.getElementById('productos').prepend(nuevoProducto); // Agregar al inicio

    asignarEventosDinamicos(nuevoProducto);

    actualizarBotones(); // Actualizar visibilidad de botones
}

function asignarEventosDinamicos(producto) {
    producto.querySelector(".precio_compra").addEventListener("input", function () {
        actualizarPrecioSugerido(this);
    });

    producto.querySelector(".porcentaje_ganancia").addEventListener("input", function () {
        actualizarPrecioSugerido(this);
    });

    producto.querySelector(".con_iva").addEventListener("change", function () {
        actualizarPrecioSugerido(this);
    });
}

function generarProductoHTML(id) {
    const div = document.createElement('div');
    div.classList.add('producto');
    div.id = `producto_${id}`;
    div.innerHTML = `
        <h3>Producto ${id}</h3>

        <div class="campos">
            <input type="text" id="codigo_producto_${id}" name="codigo_producto[]" required placeholder="Código">
            <input type="text" id="referencia_${id}" name="referencia[]" required placeholder="Referencia">
        </div>

        <div class="campos">
            <input type="text" id="nombre_${id}" name="nombre[]" required placeholder="Nombre">
            <textarea id="descripcion_${id}" name="descripcion[]" placeholder="Descripción"></textarea>
        </div>

        <div class="campos" id="campo_categoria">
            <input 
                type="text" 
                id="categoria_${id}" 
                name="categoria[]" 
                class="campo_categoria" 
                placeholder="Busca la categoría..." 
                required 
                oninput="buscarCategorias('categoria_${id}', 'sugerencias_categoria_${id}')"
            />
            <input type="hidden" id="categoria_${id}-hidden" name="contador_categoria[]" />
            <div id="sugerencias_categoria_${id}" class="sugerencias"></div>
            <button type="button" class="btn_categoria" data-bs-toggle="modal" data-bs-target="#nuevaCategoriaModal">
                Nueva Categoría
            </button>
        </div>

        <div class="campos">
            <input type="text" id="precio_compra_${id}" name="precio_compra[]" class="precio_compra" placeholder="$ compra">
            <div id="campo_iva">
                <label>IVA:</label>
                <input type="checkbox" id="con_iva_${id}" name="con_iva[]" class="con_iva"> 
            </div>
            <input type="number" id="porcentaje_ganancia_${id}" name="porcentaje_ganancia[]" class="porcentaje_ganancia" placeholder="% ganancia">
        </div>

        <div class="campos">
            <input type="text" id="precio_venta_${id}" name="precio_venta[]" class="precio_venta" required placeholder="Precio sugerido">
        </div>

        <div class="campos">
            <input type="number" id="cantidad_${id}" name="cantidad[]" required placeholder="Cantidad">
            <input type="number" id="cantidad_minima_${id}" name="cantidad_minima[]" required placeholder="Cantidad mínima">
        </div>
        <hr>
    `;
    return div;
}

function eliminarProducto(id) {
    const producto = document.getElementById(`producto_${id}`);
    if (producto) {
        producto.remove();
        productoCount--;
    }

    actualizarBotones();
}

function actualizarBotones() {
    const productos = document.querySelectorAll(".producto");
    const productoHTML = document.querySelector(".producto:first-child"); // Producto original en el HTML
    const ultimoProducto = productos[0]; // Último producto agregado (arriba)
    const botonesHTML = document.getElementById("botones_html"); // Contenedor de botones en el HTML

    // **🔹 1️⃣ Ocultar los botones del HTML si hay más de un producto**
    if (productos.length > 1) {
        if (botonesHTML) botonesHTML.style.display = "none"; // Oculta los botones del HTML
    } else {
        if (botonesHTML) botonesHTML.style.display = "block"; // Muestra los botones si solo hay un producto
    }

    // **🔹 2️⃣ Si hay más de un producto, eliminar los botones en el producto HTML**
    if (productos.length > 1 && productoHTML) {
        productoHTML.querySelector("#agregar_producto")?.remove();
        productoHTML.querySelector("#registrar_productos")?.remove();
    }

    // **🔹 3️⃣ Eliminar botones en todos los productos excepto el último**
    productos.forEach((producto, index) => {
        if (index !== 0) { // Evita el primer producto (HTML)
            producto.querySelector("#agregar_producto")?.remove();
            producto.querySelector("#registrar_productos")?.remove();
            producto.querySelector(".eliminar")?.remove();
        }
    });

    // **🔹 4️⃣ Agregar botones SOLO en el último producto dinámico**
    if (productos.length > 1) { // Solo agregar botones si hay más de un producto
        if (!ultimoProducto.querySelector("#agregar_producto")) {
            const btnAgregar = document.createElement("button");
            btnAgregar.type = "button";
            btnAgregar.id = "agregar_producto";
            btnAgregar.innerHTML = "<i class='bx bx-plus-circle'></i> Nuevo producto";
            btnAgregar.onclick = agregarOtroProducto;
            ultimoProducto.appendChild(btnAgregar);
        }

        if (!ultimoProducto.querySelector("#registrar_productos")) {
            const btnRegistrar = document.createElement("button");
            btnRegistrar.type = "submit";
            btnRegistrar.id = "registrar_productos";
            btnRegistrar.textContent = "Registrar";
            ultimoProducto.appendChild(btnRegistrar);
        }

        if (!ultimoProducto.querySelector(".eliminar") && productos.length > 1) {
            const btnEliminar = document.createElement("button");
            btnEliminar.type = "button";
            btnEliminar.classList.add("eliminar");
            btnEliminar.innerHTML = "<i class='bx bx-trash'></i> Eliminar";
            btnEliminar.onclick = function () {
                eliminarProducto(ultimoProducto.id.replace("producto_", ""));
            };
            ultimoProducto.appendChild(btnEliminar);
        }
    }
}

// **Ejecutar la función al cargar la página para asignar correctamente los botones al producto inicial**
document.addEventListener("DOMContentLoaded", function () {
    actualizarBotones();
});

// **Ejecutar la función al cargar la página para asignar correctamente los botones al producto inicial**
document.addEventListener("DOMContentLoaded", function () {
    actualizarBotones();
});



// Función para obtener las categorías desde el backend y actualizar el select
async function buscarCategorias(inputId, sugerenciasId) {
    const input = document.getElementById(inputId);
    const query = input.value.trim();
    const sugerencias = document.getElementById(sugerenciasId);

    // Solo buscar si el usuario ha ingresado al menos 3 caracteres
    if (query.length < 3) {
        sugerencias.innerHTML = ''; // Limpiar sugerencias si hay menos de 3 caracteres
        sugerencias.classList.remove('mostrar'); // Ocultar contenedor
        return;
    }

    try {
        const response = await fetch(`obtener_categorias.php?query=${encodeURIComponent(query)}`);
        
        if (!response.ok) throw new Error('Error al obtener las categorías');
        
        const categorias = await response.json();
        sugerencias.innerHTML = ''; // Limpiar sugerencias previas

        if (categorias.length > 0) {
            categorias.forEach(categoria => {
                const div = document.createElement('div');
                div.classList.add('sugerencia');
                div.style.cursor = 'pointer';
                div.textContent = categoria.nombre;
                div.onclick = () => seleccionarCategoria(inputId, categoria.nombre, categoria.contador_categoria, sugerenciasId);
                sugerencias.appendChild(div);
            });
            sugerencias.classList.add('mostrar'); // Mostrar el contenedor si hay resultados
        } else {
            sugerencias.innerHTML = '<div class="sin-resultados">No se encontraron categorías</div>';
            sugerencias.classList.add('mostrar'); // Mostrar el contenedor con el mensaje
        }
    } catch (error) {
        /* console.error('Error:', error); */
        sugerencias.innerHTML = '<div class="error">Error al buscar categorías</div>';
        sugerencias.classList.add('mostrar'); // Mostrar el contenedor con el mensaje de error
    }
}

// Función para manejar la selección de una categoría
function seleccionarCategoria(inputId, categoriaNombre, contadorCategoria, sugerenciasId) {
    const input = document.getElementById(inputId);
    const hiddenInput = document.getElementById(`${inputId}-hidden`);
    const sugerencias = document.getElementById(sugerenciasId);

    if (!hiddenInput) {
        /* console.error('No se encontró el campo oculto'); */
        return;
    }

     // Asignar los valores de la categoría seleccionada
     input.value = categoriaNombre;
     hiddenInput.value = contadorCategoria;
 
     // Limpiar y ocultar las sugerencias después de seleccionar
     sugerencias.innerHTML = '';
     sugerencias.classList.remove('mostrar'); // Ocultar con la clase
     sugerencias.style.display = 'none'; // Asegurar que desaparezca completamente
}



// Función para manejar la selección de una categoría
function seleccionarCategoria(inputId, categoriaNombre, contadorCategoria, sugerenciasId) {
    const input = document.getElementById(inputId);
    const hiddenInputId = `${inputId}-hidden`; 
    const hiddenInput = document.getElementById(hiddenInputId);

    if (!hiddenInput) {
        /* console.error('No se encontró el campo oculto con id:', hiddenInputId); */
        return;
    }

    input.value = categoriaNombre; 
    hiddenInput.value = contadorCategoria; 

   /*  console.log('Campo visible actualizado con:', categoriaNombre);
    console.log('Campo oculto actualizado con:', hiddenInput.value); */

    const sugerencias = document.getElementById(sugerenciasId);
    sugerencias.innerHTML = ''; 
};


/* ----- Script para registrar categoria dinamicamente o desde el html ---- */

    document.getElementById('registrar_categoria_ajax').addEventListener('click', (e) => {
    const boton = e.target;
    boton.disabled = true; // Desactiva el botón temporalmente

    const codigo = document.querySelector('input[name="codigo"]').value.trim();
    const nombre = document.querySelector('input[name="nombre"]').value.trim();

    if (codigo && nombre) {
        fetch('registrar_nueva_categoria.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({ codigo, nombre }),
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message); // Mostrar mensaje de éxito
                    const modal = bootstrap.Modal.getInstance(document.getElementById('nuevaCategoriaModal'));
                    modal.hide(); // Cierra el modal

                    // Limpia los campos del modal después del registro
                    document.querySelector('input[name="codigo"]').value = '';
                    document.querySelector('input[name="nombre"]').value = '';
                } else {
                    alert(data.message); // Mostrar mensaje de error
                }
            })
            .catch(error => {
                /* console.error('Error:', error); */
                alert('Ocurrió un error al registrar la categoría.');
            })
            .finally(() => {
                boton.disabled = false; // Reactiva el botón después de la solicitud
            });
    } else {
        alert('Por favor, completa todos los campos.');
        boton.disabled = false; // Reactiva el botón si los campos están vacíos
    }
});

// Limpia los campos del modal al abrirlo
document.getElementById('nuevaCategoriaModal').addEventListener('show.bs.modal', () => {
    document.querySelector('input[name="codigo"]').value = '';
    document.querySelector('input[name="nombre"]').value = '';
}); /* --- Funcion para formatear con el punto ( . ) en el campo precio_compra ------- */
function formatearPrecio(input) {
    // Obtener el valor del campo sin caracteres no numéricos
    let valor = input.value.replace(/\D/g, '');

    // Convertir a número entero para evitar errores
    const valorNumerico = parseInt(valor, 10);

    // Verificar si el campo está vacío o tiene solo ceros
    if (isNaN(valorNumerico) || valorNumerico === 0) {
        input.value = '';
        return;
    }

    // Formatear el número con puntos de miles
    const valorFormateado = valorNumerico.toLocaleString('es-CO');

    // Actualizar el valor del campo con el formato
    input.value = valorFormateado;
};

document.querySelector('form').addEventListener('submit', function () {
    document.querySelectorAll('.precio_compra').forEach(input => {
        // Remover los puntos y actualizar el valor real para envío
        input.value = input.value.replace(/\./g, '');
    });
});

/* ---- Aca va la funcionalidad para el precio sugerido con iva o si n iva ------ */
document.addEventListener("DOMContentLoaded", function () {
    // **Delegación de eventos para los inputs dinámicos**
    document.addEventListener("input", function (event) {
        if (event.target.matches(".precio_compra, .porcentaje_ganancia")) {
            /* console.log(`🔍 Evento detectado en: ${event.target.className}, valor: ${event.target.value}`); */
            actualizarPrecioSugerido(event.target);
        }
    });

    document.addEventListener("change", function (event) {
        if (event.target.matches(".con_iva")) {
            /* console.log(`🔍 Evento detectado en: ${event.target.className}, estado: ${event.target.checked}`); */
            actualizarPrecioSugerido(event.target);
        }
    });

    // **Delegar formateo en tiempo real para los inputs precio_compra**
    document.addEventListener("input", function (event) {
        if (event.target.matches(".precio_compra")) {
            event.target.value = formatearInput(event.target.value);
        }
    });
});

// **Función para actualizar el precio sugerido**
function actualizarPrecioSugerido(input) {
    /* console.log("📌 Ejecutando actualizarPrecioSugerido...");
 */
    // **Buscar el contenedor del producto donde ocurrió el cambio**
    let producto = input.closest(".producto");

    if (!producto) {
        /* console.error("⚠️ No se encontró el contenedor del producto."); */
        return;
    }

    /* console.log("✅ Producto encontrado:", producto);
 */
    // **Obtener los elementos dentro del producto**
    let precioCompraInput = producto.querySelector(".precio_compra");
    let conIVAElement = producto.querySelector(".con_iva");
    let porcentajeGananciaInput = producto.querySelector(".porcentaje_ganancia");
    let inputPrecioVenta = producto.querySelector(".precio_venta");

    // **Verificar si los elementos existen dentro de `.producto`**
    if (!precioCompraInput || !inputPrecioVenta || !porcentajeGananciaInput) {
       /*  console.warn("⚠️ Uno o más elementos no están disponibles en el DOM."); */
        return;
    }

    /* console.log(`📊 Datos Capturados:
        🔹 Precio Compra: ${precioCompraInput.value}
        🔹 Con IVA: ${conIVAElement ? conIVAElement.checked : "No existe"}
        🔹 Porcentaje Ganancia: ${porcentajeGananciaInput ? porcentajeGananciaInput.value : "No existe"}`); */

    // **Obtener valores numéricos correctos**
    let precioCompra = convertirNumero(precioCompraInput.value);
    let porcentajeGanancia = porcentajeGananciaInput ? convertirNumero(porcentajeGananciaInput.value) : 0;
    let conIVA = (conIVAElement && conIVAElement.checked) ? 1.19 : 1; // Si está marcado, aplica 19% de IVA

    // **Validar valores**
    if (precioCompra === 0 || porcentajeGanancia >= 100) {
        /* console.warn("⚠️ El precio de compra es 0 o el porcentaje de ganancia es inválido."); */
        inputPrecioVenta.value = ""; // Dejar vacío si no hay un cálculo válido
        return;
    }

    // **Calcular el precio con IVA si aplica**
    let precioCompraFinal = precioCompra * conIVA;

    // **Calcular el precio de venta sugerido con la fórmula correcta**
    let precioSugerido = precioCompraFinal / ((100 - porcentajeGanancia) / 100);

    // **Formatear el resultado a COP**
    inputPrecioVenta.value = formatearNumeroCOP(precioSugerido);

    /* console.log(`✅ Precio sugerido actualizado: ${inputPrecioVenta.value}`); */
}

// **Función para convertir valores de string a número correctamente**
function convertirNumero(valor) {
    if (!valor) return 0; // Si está vacío, retorna 0
    let numero = parseFloat(valor.replace(/\./g, "").replace(",", ".")); // Convierte puntos de miles a nada y comas a punto decimal
    return isNaN(numero) ? 0 : numero; // Si no es número, retorna 0
}

// **Función para formatear el número en pesos colombianos (COP) con el formato correcto**
function formatearNumeroCOP(numero) {
    return new Intl.NumberFormat("es-CO", {
        style: "currency",
        currency: "COP",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(numero);
}

// **Función para formatear el input de precio_compra mientras el usuario escribe**
function formatearInput(valor) {
    valor = valor.replace(/\D/g, ""); // Elimina todo lo que no sea número
    return new Intl.NumberFormat("es-CO").format(valor);
};

/* ------- Buscar y mostrar proveedores ---- */
async function buscarProveedores(inputId, sugerenciasId) {
    const input = document.getElementById(inputId);
    const query = input.value.trim();
    const sugerencias = document.getElementById(sugerenciasId);

    if (query.length < 3) {
        sugerencias.innerHTML = '';
        sugerencias.classList.remove('mostrar');
        return;
    }

    try {
        const response = await fetch(`obtener_proveedores.php?query=${encodeURIComponent(query)}`);
        if (!response.ok) throw new Error('Error al obtener los proveedores');

        const proveedores = await response.json();
        sugerencias.innerHTML = '';

        if (proveedores.length > 0) {
            proveedores.forEach(proveedor => {
                const div = document.createElement('div');
                div.classList.add('sugerencia');
                div.style.cursor = 'pointer';
                div.innerHTML = `<strong>${proveedor.nom_comercial}</strong>`;
                div.onclick = () => seleccionarProveedor(inputId, proveedor.nom_comercial, proveedor.doc_proveedor, sugerenciasId);
                sugerencias.appendChild(div);
            });
            sugerencias.classList.add('mostrar');
        } else {
            sugerencias.innerHTML = '<div class="sin-resultados">No se encontraron proveedores</div>';
            sugerencias.classList.add('mostrar');
        }
    } catch (error) {
        sugerencias.innerHTML = '<div class="error">Error al buscar proveedores</div>';
        sugerencias.classList.add('mostrar');
    }
}

function seleccionarProveedor(inputId, nombreProveedor, docProveedor, sugerenciasId) {
    const input = document.getElementById(inputId);
    const hiddenInput = document.getElementById(`${inputId}-hidden`);
    const sugerencias = document.getElementById(sugerenciasId);

    input.value = nombreProveedor;
    hiddenInput.value = docProveedor;

    sugerencias.innerHTML = '';
    sugerencias.classList.remove('mostrar');
    sugerencias.style.display = 'none';
};

/* ----- Script para el modal de registrar nuevo Proveedor ------- */
document.addEventListener('DOMContentLoaded', function() {
    // Reiniciar el estado del modal al cargar la página
    const modal = document.getElementById('nuevoProveedorModal');
    if (modal) {
        modal.removeAttribute('style');
        modal.classList.add('fade');
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
    }
    
    // Añadir event listener al botón para registrar proveedor
    const btnRegistrar = document.getElementById('registrar_proveedor_ajax');
    if (btnRegistrar) {
        btnRegistrar.addEventListener('click', (e) => {
            e.preventDefault(); // Importante: prevenir el envío del formulario
            
            const boton = e.target;
            boton.disabled = true; // Desactiva el botón temporalmente

            const nom_comercial = document.querySelector('input[name="nom_comercial"]').value.trim();
            const tipo_persona = document.querySelector('select[name="tipo_persona"]').value.trim();
            const nom_representante = document.querySelector('input[name="nom_representante"]').value.trim();
            const ape_representante = document.querySelector('input[name="ape_representante"]').value.trim();
            const tipo_documento = document.querySelector('select[name="tipo_documento"]').value.trim();
            const doc_proveedor = document.querySelector('input[name="documento"]').value.trim();
            const ciudad = document.querySelector('input[name="ciudad"]').value.trim();
            const direccion = document.querySelector('input[name="direccion"]').value.trim();
            const celular = document.querySelector('input[name="celular"]').value.trim();
            const tel_fijo = document.querySelector('input[name="tel_fijo"]').value.trim();
            const correo = document.querySelector('input[name="correo"]').value.trim();

            if (nom_comercial && tipo_persona && nom_representante && ape_representante && tipo_documento && doc_proveedor && ciudad && direccion && celular && tel_fijo && correo) {
                // Realiza la solicitud AJAX para registrar el proveedor
                /* console.log("🟢 Datos que se enviarán al servidor:");
                console.log({
                    nom_comercial,
                    tipo_persona,
                    nom_representante,
                    ape_representante,
                    tipo_documento,
                    documento: doc_proveedor,
                    ciudad,
                    direccion,
                    celular,
                    tel_fijo,
                    correo
                }); */

                fetch('registrar_nuevo_proveedor.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({ 
                        nom_comercial, 
                        tipo_persona, 
                        nom_representante, 
                        ape_representante, 
                        tipo_documento, 
                        documento: doc_proveedor, 
                        ciudad, 
                        direccion, 
                        celular, 
                        tel_fijo, 
                        correo 
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message); // Mostrar mensaje de éxito
                        const modal = bootstrap.Modal.getInstance(document.getElementById('nuevoProveedorModal'));
                        modal.hide(); // Cierra el modal

                        // Limpia los campos del modal después del registro
                        document.querySelector('input[name="nom_comercial"]').value = '';
                        document.querySelector('select[name="tipo_persona"]').value = '';
                        document.querySelector('input[name="nom_representante"]').value = '';
                        document.querySelector('input[name="ape_representante"]').value = ''; 
                        document.querySelector('select[name="tipo_documento"]').value = '';
                        document.querySelector('input[name="documento"]').value = '';
                        document.querySelector('input[name="ciudad"]').value = '';
                        document.querySelector('input[name="direccion"]').value = '';
                        document.querySelector('input[name="celular"]').value = '';
                        document.querySelector('input[name="tel_fijo"]').value = '';
                        document.querySelector('input[name="correo"]').value = '';
                    } else {
                        alert(data.message); // Mostrar mensaje de error
                    }
                })
                .catch(error => {
                    console.error('Error:', error); 
                    alert('Ocurrió un error al registrar el proveedor.');
                })
                .finally(() => {
                    boton.disabled = false; // Reactiva el botón después de la solicitud
                });
            } else {
                alert('Por favor, completa todos los campos.');
                boton.disabled = false; // Reactiva el botón si los campos están vacíos
            }
        });
    }

    // Solución adicional si persiste el problema
    // Elimina esto si no es necesario
    const fixModal = () => {
        const modalContainer = document.getElementById('nuevoProveedorModal');
        if (modalContainer && modalContainer.hasAttribute('style') && 
            modalContainer.style.display === 'block' && 
            !modalContainer.classList.contains('show')) {
            
            modalContainer.removeAttribute('style');
            modalContainer.setAttribute('aria-hidden', 'true');
        }
    };
    
    // Ejecutar la corrección después de un breve retraso
    setTimeout(fixModal, 100);
});