/* document.addEventListener("DOMContentLoaded", function () {
      const aside = document.getElementById("aside");
      const menuButton = document.getElementById("menu");
  
      menuButton.addEventListener("click", function () {
          aside.classList.toggle("active");
      });
  });
   */


  document.addEventListener("DOMContentLoaded", function () {
    const aside = document.getElementById("aside");
    const menuButton = document.getElementById("menu");

    // Alternar la clase active al hacer clic en el botón del menú
    menuButton.addEventListener("click", function () {
        aside.classList.toggle("active");
    });
});
