<?php
include("./conexion/base_de_datos.php");
include("./include/head.php");

// Realizar la consulta para obtener el nombre de la tienda
$consulta = "SELECT nombre_tienda FROM personalizaciones_tienda WHERE id = 1"; // Ajustá el ID según sea necesario
$resultado = mysqli_query($conexion, $consulta);


// Verificar si se obtuvo un resultado
if ($fila = mysqli_fetch_assoc($resultado)) {
    $nombre_tienda = $fila['nombre_tienda'];
} else {
    $nombre_tienda = "Nombre de la tienda no disponible"; // Si no se encuentra el registro
}
?>

<style>
    /* Scrollbar personalizado para sidebar */
    #adminSidebar::-webkit-scrollbar {
        width: 8px;
    }

    #adminSidebar::-webkit-scrollbar-track {
        background: #2d3748;
        /* gris oscuro */
    }

    #adminSidebar::-webkit-scrollbar-thumb {
        background-color: #4a5568;
        /* gris intermedio */
        border-radius: 4px;
        border: 2px solid transparent;
        background-clip: content-box;
    }

    #adminSidebar::-webkit-scrollbar-thumb:hover {
        background-color: #718096;
        /* más claro al pasar el mouse */
    }
</style>

<div class="relative min-h-screen md:flex">
    <!-- Botón hamburguesa (solo visible en móviles) -->
    <button class="md:hidden fixed top-4 left-4 z-50 text-white bg-gray-800 p-2 rounded focus:outline-none" id="openSidebar">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- Sidebar -->
    <aside class="bg-gray-800 text-white w-64 fixed inset-y-0 left-0 transform -translate-x-full md:translate-x-0 md:relative md:flex transition-transform duration-300 ease-in-out z-[999] overflow-y-auto md:overflow-y-visible" id="adminSidebar">


        <div class="p-6 w-full">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">Panel de Administrador</h2>
                <!-- Botón cerrar sidebar (solo móviles) -->
                <button class="text-white md:hidden" id="closeSidebar">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <ul class="space-y-3">
                <li><a href="./?module=dashboard" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded"><i class="fas fa-home mr-2"></i> Dashboard</a></li>

                <!-- Productos -->
                <li class="relative">
                    <a href="#" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded" id="manage-products">
                        <i class="fas fa-tshirt mr-2"></i> Gestionar Productos
                    </a>
                    <ul id="submenu-products" class="hidden pl-6 space-y-1">
                        <li><a href="./?module=subir_producto" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded">Subir Producto</a></li>
                        <li><a href="./?module=ver_producto" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded">Ver Productos</a></li>
                    </ul>
                </li>

                <!-- Categorías -->
                <li class="relative">
                    <a href="#" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded" id="manage-categories">
                        <i class="fas fa-layer-group mr-2"></i> Categorías
                    </a>
                    <ul id="submenu-categories" class="hidden pl-6 space-y-1">
                        <li><a href="./?module=subir_categoria" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded">Subir Categoría</a></li>
                        <li><a href="./?module=subir_sub_categoria" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded">Subir Subcategoría</a></li>
                        <li><a href="./?module=ver_categorias" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded">Ver Categorías</a></li>
                    </ul>
                </li>

                <!-- Pedidos -->
                <li><a href="./?module=pedidos" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded"><i class="fas fa-shopping-cart mr-2"></i> Gestionar Pedidos</a></li>

                <!-- Ventas -->
                <li><a href="./?module=ventas" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded"><i class="fas fa-receipt mr-2"></i> Mis ventas</a></li>


                <!-- Descuentos -->
                <li class="relative">
                    <a href="#" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded" id="manage-discount">
                        <i class="fas fa-tag mr-2"></i> Descuentos
                    </a>
                    <ul id="submenu-discount" class="hidden pl-6 space-y-1">
                        <li><a href="./?module=subir_descuento" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded">Activar Descuentos</a></li>
                        <li><a href="./?module=descuentos" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded">Ver Descuentos</a></li>
                    </ul>
                </li>
                <!-- Cupones -->
                <li>
                    <a href="./?module=cupones" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded">
                        <i class="fas fa-ticket-alt mr-2"></i> Cupónes
                    </a>
                </li>

                <!-- Tienda -->
                <li><a href="./?module=mi_tienda" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded"><i class="fas fa-bullhorn mr-2"></i> Mi tienda</a></li>

                <!-- Visitas -->
                <li><a href="./?module=visitas" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded"><i class="fas fa-eye mr-2"></i> Visitas</a></li>

                <!-- Usuarios -->
                <li><a href="./?module=usuarios" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded"><i class="fas fa-users mr-2"></i>Email Marketing</a></li>
                <!-- Salir -->
                <li><a href="./salir" class="block px-3 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition rounded"><i class="fas fa-sign-out-alt mr-2"></i> Salir</a></li>

            </ul>
        </div>
    </aside>


    <div class="flex-1">
        <nav class="bg-gray-800 p-4">
            <div class="container mx-auto flex items-center justify-between">
                <button class="text-white md:hidden" id="sidebarToggle">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <span class="text-white font-bold text-xl"><?php echo htmlspecialchars($nombre_tienda); ?></span>

                <?php if (isset($_SESSION['admin_nombre'])): ?>
                    <span class="text-white text-sm ml-4">Hola, <strong><?php echo htmlspecialchars($_SESSION['admin_nombre']); ?></strong> 👋</span>
                <?php endif; ?>
            </div>
        </nav>




        <?php

        if (isset($_GET['module'])) {
            $module = $_GET['module'];
            switch ($module) {
                case 'visitas':
                    include './module/visitas.php';
                    break;
                case 'subir_producto':
                    include './module/subir_producto.php';
                    break;
                case 'mi_tienda':
                    include './module/mi_tienda.php';
                    break;
                case 'modificar_producto':
                    include './module/modificar_producto.php';
                    break;
                case 'ver_producto':
                    include './module/ver_producto.php';
                    break;
                case 'subir_categoria':
                    include './module/subir_categoria.php';
                    break;
                case 'subir_sub_categoria':
                    include './module/subir_sub_categoria.php';
                    break;
                case 'ver_categorias':
                    include './module/ver_categorias.php';
                    break;
                case 'editar_categoria':
                    include './module/editar_categoria.php';
                    break;
                case 'pedidos':
                    include('./module/pedidos.php');
                    break;
                case 'descuentos':
                    include './module/descuentos.php';
                    break;
                case 'subir_descuento':
                    include './module/subir_descuento.php';
                    break;
                case 'editar_descuento':
                    include './module/editar_descuento.php';
                    break;
                case 'usuarios':
                    include './module/usuarios.php';
                    break;
                case 'ventas':
                    include './module/ventas.php';
                    break;
                case 'cupones':
                    include './module/cupones.php';
                    break;
                default:
                    include './module/dashboard.php';
                    break;
            }
        } else {

            include './module/dashboard.php';
        }
        ?>

        </main>
        <?php
        include("./include/footer.php");
        ?>
    </div>
</div>