<?php
include("./conexion/base_de_datos.php");
include("./include/head.php");

$página = $_GET['page'] ?? 'inicio';

// **Procesar las acciones del carrito (adicionar, eliminar, actualizar) ANTES de la navbar**
if ($página === 'carrito' && (isset($_GET['add'], $_GET['producto'], $_GET['color'], $_GET['talle']) || isset($_GET['eliminar']) || isset($_POST['actualizar_cantidad']))) {
    include("./module/carrito.php");
    exit(); // Detener la ejecución después de realizar una acción en el carrito
}

// Navbar (excepto para ingresar y registrarse)
if (!in_array($página, ['ingresar', 'registrarse'])) {
    include("./include/navbar.php");
}

// Precios mínimo y máximo
$consulta_precio = "SELECT MIN(precio) AS min_precio, MAX(precio) AS max_precio FROM productos";
$resultado_precio = mysqli_query($conexion, $consulta_precio); // Cambié $conn por $conexion
$precio_minimo = 0;
$precio_maximo = 1000;

if ($resultado_precio && mysqli_num_rows($resultado_precio) > 0) {
    $fila = mysqli_fetch_assoc($resultado_precio);
    $precio_minimo = (int)($fila['min_precio'] ?? 0);
    $precio_maximo = (int)($fila['max_precio'] ?? 1000);
}

// Categorías
$resultado_categorías = mysqli_query($conexion, "SELECT id, nombre FROM categorias"); // Cambié $conn por $conexion

// Subcategorías (lo más vendido)
$resultado_subcategorías = mysqli_query($conexion, "SELECT id, nombre, categoria_id FROM subcategorias"); // Cambié $conn por $conexion
$subcategorías_aleatorias = [];

if ($resultado_subcategorías && mysqli_num_rows($resultado_subcategorías) > 0) {  // Aquí cambié $subcategorias_resultado por $resultado_subcategorías
    $todas_las_subcategorías = mysqli_fetch_all($resultado_subcategorías, MYSQLI_ASSOC);
    shuffle($todas_las_subcategorías);
    $subcategorías_aleatorias = array_slice($todas_las_subcategorías, 0, 5);
}
?>


<?php
$page = $_GET['page'] ?? 'home'; // Si no se define 'page' en la URL, se asigna 'home' por defecto
?>

<div class="p-4 md:p-6">
    <?php
    switch ($page) {
        case 'home':
            include './module/home.php';
            break;
        case 'iniciar_sesion':
            include './module/iniciar_sesion.php';
            break;
        case 'olvide_contrasena':
            include './module/olvide_contrasena.php';
            break;
        case 'productos':
            include './module/productos.php';
            break;
        case 'productos_en_descuento':
            include './module/productos_en_descuento.php';
            break;
        case 'detalles_producto_descuento':
            include './module/detalles_producto_descuento.php';
            break;
        case 'detalles_producto':
            include './module/detalles_producto.php';
            break;
        case 'categorias':
            include './module/categorias.php';
            break;
        case 'subcategoria':
            include './module/subcategoria.php';
            break;
        case 'registro':
            include './module/registro.php';
            break;
        case 'contacto':
            include './module/contacto.php';
            break;
        case 'carrito':
            include './module/carrito.php';
            break;
        case 'mis_pedidos':
            include './module/mis_pedidos.php';
            break;
        case 'salir':
            include './module/salir.php';
            break;
        default:
            include './module/home.php';
            break;
    }
    ?>
</div>
</div>

<?php include("./include/footer.php"); ?>

<!-- JS filtros -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.querySelector('aside');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const openSidebarBtn = document.getElementById('openSidebar');
        const closeSidebarBtn = document.getElementById('closeSidebar');

        function openSidebar() {
            sidebar.classList.remove('hidden');
            sidebarOverlay.classList.remove('hidden');
            sidebar.classList.add('fixed', 'top-0', 'left-0', 'z-50', 'h-full');
        }

        function closeSidebar() {
            sidebar.classList.add('hidden');
            sidebarOverlay.classList.add('hidden');
        }

        openSidebarBtn.addEventListener('click', openSidebar);
        closeSidebarBtn.addEventListener('click', closeSidebar);
        sidebarOverlay.addEventListener('click', closeSidebar);

        const priceRange = document.getElementById('price-range');
        const priceValue = document.getElementById('price-value');

        priceRange.addEventListener('input', function() {
            priceValue.textContent = Math.round(priceRange.value);
        });
    });
</script>