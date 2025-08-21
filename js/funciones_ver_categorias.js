
$(document).ready(function() {
    $("#buscarCategoria").on("keyup", function() {
        let query = $(this).val().trim();
        
        if (query.length >= 3) {
            $.ajax({
                url: "buscar_categorias.php",
                type: "GET",
                data: { q: query },
                dataType: "json",
                success: function(data) {
                    let tablaCategorias = $("#tablaCategorias");
                    tablaCategorias.empty();
                    
                    if (data.length > 0) {
                        data.forEach(cat => {
                            tablaCategorias.append(`
                                <tr id="fila_${cat.contador_categoria}">
                                    <td>${cat.codigo}</td>
                                    <td>${cat.nombre}</td>
                                    <td>
                                        <a href="editar_categoria.php?contador_categoria=${cat.contador_categoria}">
                                            <i class="lni lni-pencil"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="eliminar_categoria.php?contador_categoria=${cat.contador_categoria}" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar esta categoría?');">
                                            <i class="lni lni-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tablaCategorias.append('<tr><td colspan="4">No se encontraron categorías</td></tr>');
                    }
                }
            });
        } else if (query.length === 0) {
            console.log("Campo vacío, recargando datos sin refrescar la página");
            $("#tablaCategorias").load(location.href + " #tablaCategorias > *"); // Recargar solo la tabla
        }
    });
});
