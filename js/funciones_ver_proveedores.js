$(document).ready(function () {

    // =========================
    // 🔍 Búsqueda dinámica
    // =========================
    $("#buscarProveedor").on("keyup", function () {
        let query = $(this).val().trim();

        $.ajax({
            url: "buscar_proveedores.php",
            type: "GET",
            data: { q: query },
            dataType: "json",
            success: function (data) {
                const tablaProveedores = $("#tablaProveedores");
                tablaProveedores.empty();

                if (data.length > 0) {
                    data.forEach(provee => {
                        tablaProveedores.append(`
                            <tr id="fila_${provee.cont_provee}">
                                <td>${provee.nombre}</td>
                                <td>${provee.celular}</td>
                                <td>${provee.correo}</td>
                                <td>
                                    <a href="editar_proveedor.php?cont_provee=${provee.cont_provee}">
                                        <i class="lni lni-pencil"></i>
                                    </a>
                                </td>
                                <td>
                                    <a href="eliminar_proveedor.php?cont_provee=${provee.cont_provee}" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este proveedor?');">
                                        <i class="lni lni-trash-can"></i>
                                    </a>
                                </td>
                                <td>
                                    <a href="#" class="verMasProveedor" data-id="${provee.cont_provee}">
                                        <i class='bx bx-plus-circle'></i>
                                    </a>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    tablaProveedores.append('<tr><td colspan="6">No se encontraron proveedores</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error("❌ Error al buscar proveedores:", error);
            }
        });
    });

    // =========================
    // 📄 Ver más proveedor (modal)
    // =========================
    $(document).on('click', '.verMasProveedor', function (e) {
        e.preventDefault();

        const contProvee = $(this).data('id');

        $.ajax({
            url: 'detalle_proveedor.php',
            type: 'POST',
            data: { cont_provee: contProvee },
            dataType: 'json',
            success: function (proveedor) {
                if (proveedor.error) {
                    alert("⚠️ " + proveedor.error);
                    return;
                }

                // Rellenar los campos del modal
                $('#nom_comercial').text(proveedor.nom_comercial);
                $('#tipo_persona').text(proveedor.tipo_persona);
                $('#tipo_documento').text(proveedor.tipo_documento);
                $('#doc_proveedor').text(proveedor.doc_proveedor);
                $('#nom_representante').text(proveedor.nom_representante);
                $('#ape_representante').text(proveedor.ape_representante);
                $('#correo').text(proveedor.correo);
                $('#celular').text(proveedor.celular);
                $('#tel_fijo').text(proveedor.tel_fijo);
                $('#ciudad').text(proveedor.ciudad);
                $('#direccion').text(proveedor.direccion);

                // Mostrar el modal
                $('#modalProveedor').show();
            },
            error: function (xhr, status, error) {
                console.error("❌ Error al cargar detalles del proveedor:", error);
            }
        });
    });

    // =========================
    // ❌ Cerrar modal con botón X
    // =========================
    $('#cerrarModal').on('click', function () {
        $('#modalProveedor').hide();
    });

    // =========================
    // ❌ Cerrar modal al hacer clic fuera
    // =========================
    $(document).on('click', function (event) {
        const modal = $('#modalProveedor');
        const contenido = $('#contenidoModal');

        if (modal.is(':visible') && !contenido.is(event.target) && contenido.has(event.target).length === 0) {
            modal.hide();
        }
    });

});
