<?php
// Realizar la consulta para obtener el nombre de la tienda y el logo
$query = "SELECT nombre_tienda, logo_tienda, contacto_tienda as store_contact FROM personalizaciones_tienda WHERE id = 1"; 
$result = mysqli_query($conexion, $query);

// Verificar si se obtuvo un resultado
if ($row = mysqli_fetch_assoc($result)) {
  $store_name = $row['nombre_tienda'];
  $store_logo = $row['logo_tienda'];
  $store_contact = $row['store_contact'];
} else {
  $store_name = "Nombre de la tienda no disponible";
  $store_logo = "images/logo.png";
  $store_contact = "1234567890";
}
?>

<style>
  .navbar-container {
    background: rgba(255, 255, 255, 0.98);
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.3);
  }
  .nav-link {
    position: relative;
    padding-bottom: 8px;
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  }
  .nav-link:hover {
    transform: translateY(-2px);
  }
  .nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 3px;
    background: linear-gradient(90deg, #3B82F6, #8B5CF6);
    transition: width 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border-radius: 2px;
  }
  .nav-link:hover::after {
    width: 100%;
  }
  .dropdown-menu {
    min-width: 240px;
    border-radius: 14px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.15);
    overflow: hidden;
  }
  .icon-container {
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  }
  .icon-container:hover {
    transform: scale(1.1);
  }
  .cart-badge {
    box-shadow: 0 0 0 2px white;
  }
  .hamburger-line {
    transition: all 0.3s ease;
  }
</style>

<!-- Banner promocional ultra premium -->
<div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 px-4 shadow-lg">
  <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-center gap-4 md:gap-8">
    <!-- WhatsApp con icono circular perfecto -->
    <div class="flex items-center group cursor-pointer" onclick="window.open('https://wa.me/<?= preg_replace('/[^0-9]/', '', htmlspecialchars($store_contact)) ?>', '_blank')">
      <div class="relative mr-3">
        <!-- Efecto de halo exterior -->
        <div class="absolute inset-0 bg-green-400/40 rounded-full blur-md group-hover:blur-lg transition-all duration-300"></div>
        
        <!-- Contenedor circular perfecto -->
        <div class="relative flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-green-600 shadow-md overflow-hidden">
          <!-- Icono WhatsApp centrado perfectamente -->
          <i class="fab fa-whatsapp text-white text-lg absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></i>
          
          <!-- Efecto de reflejo sutil -->
          <div class="absolute top-0 left-0 w-full h-1/2 bg-white/10"></div>
        </div>
      </div>
      <span class="font-medium text-sm md:text-base">WHATSAPP: <?= htmlspecialchars($store_contact) ?></span>
      <i class="fas fa-external-link-alt ml-2 text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
    </div>

    <!-- Envío gratis -->
    <div class="flex items-center group">
      <div class="relative mr-3">
        <div class="absolute inset-0 bg-white/20 rounded-full blur-md group-hover:blur-lg transition-all duration-300"></div>
        <div class="relative bg-white/10 p-2 rounded-full backdrop-blur-sm">
          <i class="fas fa-shipping-fast text-yellow-300 text-lg"></i>
        </div>
      </div>
      <span class="font-bold tracking-wider text-sm md:text-base">ENVÍOS GRATIS</span>
    </div>
  </div>
</div>

<nav class="navbar-container sticky top-0 z-50" x-data="{ mobileMenu: false, userMenu: false, searchActive: false }">
  <div class="max-w-7xl mx-auto px-6">
    <div class="flex justify-between items-center h-20">
<!-- Logo y Nombre con efecto -->
<div class="flex items-center space-x-4">
  <a href="./" class="flex items-center group transform transition-all duration-300 hover:scale-[1.02]">
    <div class="relative">
      <div class="absolute inset-0 bg-blue-100/30 rounded-lg blur-md group-hover:blur-lg transition-all duration-300"></div>
      <img src="./admin/controllers/uploads/tienda_imagenes/<?php echo htmlspecialchars($store_logo); ?>" 
           alt="<?php echo htmlspecialchars($store_name); ?>" 
           class="relative h-10 transition-all duration-300 group-hover:opacity-90">
    </div>
    <span class="text-xl font-semibold text-gray-800 hidden md:block ml-3">
      <?php echo htmlspecialchars($store_name); ?>
    </span>
  </a>
</div>


      <!-- Menú de categorías premium -->
      <div class="hidden md:flex items-center space-x-8 ml-6">
        <?php
        $categorias = ['Hombre', 'Mujer', 'Unisex', 'Niños', 'Niñas', 'Bebés', 'Accesorios'];
        foreach ($categorias as $cat) {
          echo '<a href="./?page=productos&genero='.$cat.'" class="nav-link text-gray-700 font-medium px-1">'.$cat.'</a>';
        }
        ?>
      </div>

      <!-- Iconos de acción con efectos mejorados -->
      <div class="flex items-center space-x-6">
        <!-- Búsqueda premium -->
        <div class="relative">
          <button @click="searchActive = !searchActive" class="icon-container p-2 text-gray-600 hover:text-blue-500">
            <div class="relative">
              <div class="absolute inset-0 bg-blue-100 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
              <i class="fas fa-search text-lg relative"></i>
            </div>
          </button>
          
          <div class="absolute right-0 mt-2 bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-100"
               x-show="searchActive"
               @click.away="searchActive = false"
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="opacity-0 translate-y-1"
               x-transition:enter-end="opacity-100 translate-y-0"
               x-transition:leave="transition ease-in duration-150"
               x-transition:leave-start="opacity-100 translate-y-0"
               x-transition:leave-end="opacity-0 translate-y-1">
            <form action="./" method="GET" class="flex items-center p-3">
              <input type="hidden" name="page" value="productos">
              <div class="relative w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="buscar" placeholder="Buscar productos..." 
                       class="w-full pl-10 pr-4 py-2.5 text-gray-700 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-300 focus:border-blue-300 transition-all">
              </div>
            </form>
          </div>
        </div>

        <!-- Carrito con efecto glow -->
        <a href="./?page=carrito" class="icon-container relative p-2 text-gray-600 hover:text-blue-500">
          <div class="relative">
            <div class="absolute inset-0 bg-blue-100 rounded-full opacity-0 hover:opacity-100 transition-opacity"></div>
            <i class="fas fa-shopping-cart text-lg relative"></i>
            <span class="absolute -top-1.5 -right-1.5 bg-gradient-to-br from-blue-500 to-blue-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center cart-badge">
              <?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : '0'; ?>
            </span>
          </div>
        </a>

        <!-- Usuario con efectos mejorados -->
        <?php if (isset($_SESSION['nombre'])): ?>
          <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none group">
              <div class="flex items-center space-x-1">
                <div class="relative">
                  <div class="absolute inset-0 bg-blue-100 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                  <span class="relative text-gray-700 font-medium group-hover:text-blue-500 transition-colors">
                    ¡Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!
                  </span>
                </div>
                <svg class="w-4 h-4 text-gray-500 transform transition-transform duration-300 group-hover:text-blue-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </div>
            </button>
            
            <div class="absolute right-0 mt-2 w-60 bg-white rounded-xl shadow-2xl dropdown-menu z-50 border border-gray-100"
                 x-show="open"
                 @click.away="open = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-1">
              <div class="py-2">
                <a href="./?page=perfil" class="block px-5 py-3 text-sm text-gray-700 hover:bg-blue-50 transition flex items-center">
                  <i class="fas fa-user-circle text-blue-400 mr-3"></i>
                  <span>Mi perfil</span>
                </a>
                <a href="./?page=mis_pedidos" class="block px-5 py-3 text-sm text-gray-700 hover:bg-blue-50 transition flex items-center">
                  <i class="fas fa-box-open text-blue-400 mr-3"></i>
                  <span>Mis pedidos</span>
                </a>
                <div class="border-t border-gray-100 mx-3 my-1"></div>
                <a href="./?page=salir" class="block px-5 py-3 text-sm text-red-500 hover:bg-red-50 transition flex items-center">
                  <i class="fas fa-sign-out-alt text-red-400 mr-3"></i>
                  <span>Cerrar sesión</span>
                </a>
              </div>
            </div>
          </div>
        <?php else: ?>
          <a href="./?page=iniciar_sesion" class="icon-container flex items-center space-x-1 text-gray-600 hover:text-blue-500">
            <div class="relative">
              <div class="absolute inset-0 bg-blue-100 rounded-full opacity-0 hover:opacity-100 transition-opacity"></div>
              <i class="fas fa-user text-lg relative"></i>
            </div>
            <span class="hidden md:inline font-medium">Ingresar</span>
          </a>
        <?php endif; ?>
      </div>

      <!-- Botón hamburguesa mejorado -->
      <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2 focus:outline-none group">
        <div class="space-y-2">
          <span class="block w-6 h-0.5 bg-gray-600 transition transform origin-center hamburger-line" 
                :class="{ 'rotate-45 translate-y-2': mobileMenu, 'group-hover:bg-blue-500': !mobileMenu }"></span>
          <span class="block w-6 h-0.5 bg-gray-600 transition hamburger-line" 
                :class="{ 'opacity-0': mobileMenu, 'group-hover:bg-blue-500': !mobileMenu }"></span>
          <span class="block w-6 h-0.5 bg-gray-600 transition transform origin-center hamburger-line" 
                :class="{ '-rotate-45 -translate-y-2': mobileMenu, 'group-hover:bg-blue-500': !mobileMenu }"></span>
        </div>
      </button>
    </div>
  </div>

  <!-- Menú móvil premium -->
  <div class="md:hidden bg-white border-t border-gray-100 mobile-menu shadow-lg"
       x-show="mobileMenu"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 -translate-y-4"
       x-transition:enter-end="opacity-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 translate-y-0"
       x-transition:leave-end="opacity-0 -translate-y-4">
    <div class="px-6 py-4 space-y-3">
      <?php
      $categorias = ['Hombre', 'Mujer', 'Unisex', 'Niños', 'Niñas', 'Bebés', 'Accesorios'];
      foreach ($categorias as $cat) {
        echo '<a href="./?page=productos&gender='.$cat.'" class="block py-3 px-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition flex items-center">
                <i class="fas fa-chevron-right text-blue-400 text-xs mr-3"></i>
                <span>'.$cat.'</span>
              </a>';
      }
      ?>
      
      <div class="pt-4 mt-2 border-t border-gray-100 flex justify-around">
        <a href="./?page=carrito" class="flex flex-col items-center text-gray-600 hover:text-blue-500 transition p-2 rounded-lg hover:bg-blue-50">
          <div class="relative">
            <i class="fas fa-shopping-cart text-xl"></i>
            <span class="absolute -top-2 -right-2 bg-blue-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center cart-badge">
              <?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : '0'; ?>
            </span>
          </div>
          <span class="text-xs mt-1">Carrito</span>
        </a>
        
        <?php if (isset($_SESSION['id'])): ?>
          <a href="./?page=salir" class="flex flex-col items-center text-gray-600 hover:text-red-500 transition p-2 rounded-lg hover:bg-red-50">
            <i class="fas fa-sign-out-alt text-xl"></i>
            <span class="text-xs mt-1">Salir</span>
          </a>
        <?php else: ?>
          <a href="./?page=iniciar_sesion" class="flex flex-col items-center text-gray-600 hover:text-blue-500 transition p-2 rounded-lg hover:bg-blue-50">
            <i class="fas fa-user text-xl"></i>
            <span class="text-xs mt-1">Ingresar</span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

