/* Este bloque controla el abrir, mostrar y cerra model de evr mas info cliente */
    $(document).ready(function() {

        // Usar delegación de eventos para que funcione con HTML y AJAX
        $(document).on('click', '.verMasCliente', function(e) {
            e.preventDefault();
            
            var contadorCliente = $(this).data('id');

            $.ajax({
                url: 'detalle_cliente.php',
                type: 'POST',
                data: { contador_clientes: contadorCliente },
                success: function(data) {
                    var cliente = JSON.parse(data);
                    
                    $('#tipo_persona').text(cliente.tipo_persona);
                    $('#tipo_documento').text(cliente.tipo_documento);
                    $('#documento').text(cliente.documento);
                    $('#nombre').text(cliente.nombre);
                    $('#apellido').text(cliente.apellido);
                    $('#correo').text(cliente.correo);
                    $('#fecha_nacimiento').text(cliente.fecha_nacimiento);
                    $('#ciudad').text(cliente.ciudad);
                    $('#direccion').text(cliente.direccion);
                    $('#nom_comercial').text(cliente.nom_comercial || 'Sin información');
                    
                    $('#modalCliente').show();
                }
            });
        });

        // Cerrar modal al hacer clic fuera del contenido del modal
        $(document).on("click", function(event) {
            const modal = $("#modalCliente");
            const contenido = $("#contenidoModal");

            // Si el modal está visible y el clic fue fuera del contenido
            if (modal.is(":visible") && !contenido.is(event.target) && contenido.has(event.target).length === 0) {
                modal.hide();
            }
        });

        // Cerrar modal al hacer clic en la X
        $('#cerrarModal').on('click', function() {
            $('#modalCliente').hide();
        });

    });

/* Este bloque controla la funcionalidad del buscador, mostrar sugerencias */
$(document).ready(function() {

    // 1. Búsqueda dinámica
    $("#buscarCliente").on("keyup", function() {
        let query = $(this).val().trim();
        
        if (query.length >= 3) {
            $.ajax({
                url: "buscar_clientes.php",
                type: "GET",
                data: { q: query },
                dataType: "json",
                success: function(data) {
                    let tablaClientes = $("#tablaClientes");
                    tablaClientes.empty();
                    
                    if (data.length > 0) {
                        data.forEach(cat => {
                            tablaClientes.append(`
                                <tr id="fila_${cat.contador_clientes}">
                                    <td>${cat.nombre}</td>
                                    <td>${cat.celular}</td>
                                    <td>${cat.correo}</td>
                                    <td>
                                        <a href="editar_cliente.php?contador_clientes=${cat.contador_clientes}">
                                            <i class="lni lni-pencil"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="eliminar_cliente.php?contador_clientes=${cat.contador_clientes}" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este cliente?');">
                                            <i class="lni lni-trash-can"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="#" class="verMasCliente" data-id="${cat.contador_clientes}">
                                            <i class='bx bx-plus-circle'></i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tablaClientes.append('<tr><td colspan="6">No se encontraron clientes</td></tr>');
                    }
                }
            });
        } else if (query.length === 0) {
            $.ajax({
                url: "buscar_clientes.php",
                type: "GET",
                data: { q: "" },
                dataType: "json",
                success: function(data) {
                    const tablaClientes = $("#tablaClientes");
                    tablaClientes.empty();

                    if (data.length > 0) {
                        data.forEach(cat => {
                            tablaClientes.append(`
                                <tr id="fila_${cat.contador_clientes}">
                                    <td>${cat.nombre}</td>
                                    <td>${cat.celular}</td>
                                    <td>${cat.correo}</td>
                                    <td>
                                        <a href="editar_cliente.php?contador_clientes=${cat.contador_clientes}">
                                            <i class="lni lni-pencil"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="eliminar_cliente.php?contador_clientes=${cat.contador_clientes}" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este cliente?');">
                                            <i class="lni lni-trash-can"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="#" class="verMasCliente" data-id="${cat.contador_clientes}">
                                            <i class='bx bx-plus-circle'></i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tablaClientes.append('<tr><td colspan="6">No se encontraron clientes</td></tr>');
                    }
                }
            });
        }

    });

    // 2. Delegar el click para ver más (modal)
    $(document).on("click", ".verMasCliente", function(e) {
        e.preventDefault();
        const clienteID = $(this).data("id");
        /* console.log("Abrir modal para cliente:", clienteID); */
        $("#miModal").show(); // Aquí muestras el modal que ya tienes en HTML
    });

});
