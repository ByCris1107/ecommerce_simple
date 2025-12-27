<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Abrir y cerrar sidebar
        document.getElementById("openSidebar").addEventListener("click", () => {
            document.getElementById("adminSidebar").classList.remove("-translate-x-full");
        });

        document.getElementById("closeSidebar").addEventListener("click", () => {
            document.getElementById("adminSidebar").classList.add("-translate-x-full");
        });

        // Función para asignar el toggle a cualquier submenú
        function setupToggle(menuId, submenuId) {
            const menu = document.getElementById(menuId);
            const submenu = document.getElementById(submenuId);
            if (menu && submenu) {
                menu.addEventListener("click", function (e) {
                    e.preventDefault();
                    submenu.classList.toggle("hidden");
                });
            }
        }

        // Aplicar a todos los submenús
        setupToggle("manage-products", "submenu-products");
        setupToggle("manage-categories", "submenu-categories");
        setupToggle("manage-discount", "submenu-discount");
        setupToggle("manage-store", "submenu-store");
    });
</script>



</body>

</html>