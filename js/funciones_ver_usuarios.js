$(document).ready(function() {
    // Cuando se haga clic en el ícono para ver más detalles
    $('.verMasUsuario').on('click', function(e) {
        e.preventDefault();
        
        // Obtiene el contador del proveedor
        var contadorUsuarios = $(this).data('id');
        
        // Hace la solicitud AJAX al servidor
        $.ajax({
            url: 'detalle_usuario.php',
            type: 'POST',
            data: { contador_usuarios: contadorUsuarios },
            success: function(data) {
                // Parsear el JSON recibido
                var usuario = JSON.parse(data);
                
                // Mostrar los datos en el modal
                $('#tipo_doc').text(usuario.tipo_doc);
                $('#documento').text(usuario.documento);
                $('#nombre').text(usuario.nombre);
                $('#apellido').text(usuario.apellido);
                $('#fecha_nacimiento').text(usuario.fecha_nacimiento);
                $('#correo').text(usuario.correo);
                $('#celular').text(usuario.celular);
                $('#direccion').text(usuario.direccion);
                $('#ciudad').text(usuario.ciudad);
                
                // Mostrar el modal
                $('#modalUsuario').fadeIn();
            }
        });
    });

    // Cerrar el modal al hacer clic en la 'X'
    $('#cerrarModal').on('click', function() {
        $('#modalUsuario').fadeOut();
    });

    // Cerrar el modal al hacer clic fuera del contenido
    $(document).on('click', function(event) {
        var modal = $('#modalUsuario'); 
        if (event.target === modal[0]) { // Si se hace clic en el fondo del modal
            modal.fadeOut();
        }
    });
});

/* Este bloque controla la funcionalidad del buscador, mostrar sugerencias */
$(document).ready(function() {

    // 1. Búsqueda dinámica
    $("#buscarUsuario").on("keyup", function() {
        let query = $(this).val().trim();
        
        if (query.length >= 3) {
            $.ajax({
                url: "buscar_usuarios.php",
                type: "GET",
                data: { q: query },
                dataType: "json",
                success: function(data) {
                    let tablaUsuarios = $("#tablaUsuarios");
                    tablaUsuarios.empty();
                    
                    if (data.length > 0) {
                        data.forEach(cat => {
                            tablaUsuarios.append(`
                                <tr id="fila_${cat.contador_usuarios}">
                                    <td>${cat.nombre}</td>
                                    
                                    <td>${cat.rol}</td>
                                    <td>
                                        <a href="editar_usuario.php?contador_usuarios=${cat.contador_usuarios}">
                                            <i class="lni lni-pencil"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="eliminar_usuario.php?contador_usuarios=${cat.contador_usuarios}" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este cliente?');">
                                            <i class="lni lni-trash-can"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="#" class="verMasUsuario" data-id="${cat.contador_usuarios}">
                                            <i class='bx bx-plus-circle'></i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tablaUsuarios.append('<tr><td colspan="6">No se encontraron usuarios</td></tr>');
                    }
                }
            });
        } else if (query.length === 0) {
            $.ajax({
                url: "buscar_usuarios.php",
                type: "GET",
                data: { q: "" },
                dataType: "json",
                success: function(data) {
                    const tablaUsuarios = $("#tablaUsuarios");
                    tablaUsuarios.empty();

                    if (data.length > 0) {
                        data.forEach(cat => {
                            tablaUsuarios.append(`
                                <tr id="fila_${cat.contador_usuarios}">
                                    <td>${cat.nombre}</td>
                                    
                                    <td>${cat.rol}</td>
                                    <td>
                                        <a href="editar_usuario.php?contador_usuarios=${cat.contador_usuarios}">
                                            <i class="lni lni-pencil"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="eliminar_usuario.php?contador_usuarios=${cat.contador_usuarios}" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                            <i class="lni lni-trash-can"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="#" class="verMasUsario" data-id="${cat.contador_usuarios}">
                                            <i class='bx bx-plus-circle'></i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tablaUsuarios.append('<tr><td colspan="6">No se encontraron usuarios</td></tr>');
                    }
                }
            });
        }

    });

    // 2. Delegar el click para ver más (modal)
    $(document).on("click", ".verMasUsuario", function(e) {
        e.preventDefault();
        const usuarioID = $(this).data("id");
        /* console.log("Abrir modal para usuario:", usuarioID); */
        $("#miModal").show(); // Aquí muestras el modal que ya tienes en HTML
    });

});