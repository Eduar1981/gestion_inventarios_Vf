document.addEventListener("DOMContentLoaded", function() {
    // Función para verificar si hay productos con stock bajo
    function checkLowStock() {
        // Hacer una solicitud al servidor para obtener productos con stock bajo
        fetch('alerts_service.php')
        .then(response => response.json()) // Convertir la respuesta a formato JSON
        .then(data => {
            // Si hay productos con stock bajo, mostrar la alerta
            if (data.length > 0) {
                Swal.fire({
                    title: '⚠️ ¡Alerta de Stock Bajo!', // Título de la alerta
                    html: createAlertMessage(data), // Llamada a la función que genera el mensaje de alerta
                    icon: 'warning', // Icono de advertencia
                    confirmButtonText: 'Aceptar', // Botón de confirmación
                    showCancelButton: true, // Mostrar botón de cancelación
                    cancelButtonText: 'No mostrar por 6h' // Texto del botón de cancelación
                }).then((result) => {
                    // Si el usuario presiona "No mostrar por 6h", guardar el tiempo en localStorage
                    if (result.dismiss === 'cancel') {
                        localStorage.setItem('lastAlertTime', Date.now());
                    }
                });
            }
        });
    }

    // Función para generar el mensaje de alerta con los productos de stock bajo
    function createAlertMessage(products) {
        let message = '<ul style="list-style: none; padding: 0;">'; // Lista sin estilos para mostrar los productos

        // Recorrer cada producto y agregarlo al mensaje
        products.forEach(product => {
            message += `<li><strong>Producto:</strong> ${product.nombre}<br>
                       <strong>Cantidad actual:</strong> ${product.cantidad}<br>
                       <strong>Cantidad mínima:</strong> ${product.cantidad_minima}</li><br>`;
        });

        message += '</ul>'; // Cerrar la lista
        return message; // Devolver el mensaje formateado
    }

    // 📌 Verificar si la alerta se ha mostrado en las últimas 6 horas
    const lastAlertTime = localStorage.getItem('lastAlertTime'); // Obtener el último tiempo guardado en localStorage
    const NOW = Date.now(); // Obtener la fecha y hora actual en milisegundos
    const HOURS_24 = 6 * 60 * 60 * 1000; // 6 horas en milisegundos

    // Si no hay registro en localStorage o ya pasaron más de 6 horas, mostrar la alerta
    if (!lastAlertTime || (NOW - lastAlertTime) > HOURS_24) {
        checkLowStock(); // Llamar a la función para verificar el stock
    }
});
