
document.addEventListener("DOMContentLoaded", function () {
    fetch("obtener_rol.php") // Llamamos al archivo PHP para obtener el rol
        .then(response => response.json()) // Convertimos la respuesta a JSON
        .then(data => {
            /* console.log("Rol del usuario desde AJAX:", data.rol); */ // 🔥 Verificar en consola
            let menuUsuarios = document.getElementById("menuUsuarios");

            if (data.rol !== "superadmin" && data.rol !== "administrador") {
                menuUsuarios.style.display = "none"; // Oculta el botón si no es superadmin o admin
            }
        })
        .catch(error => console.error("Error obteniendo el rol:", error));
});
  