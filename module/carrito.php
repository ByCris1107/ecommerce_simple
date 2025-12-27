<?php
ob_start(); // Evita errores con header()

$usuario_logueado = isset($_SESSION['id']);


// Función para obtener stock disponible
function obtenerStockVariante($conexion, $variante_id)
{
  $stmt = $conexion->prepare("SELECT stock FROM variantes_producto WHERE id = ?");
  $stmt->bind_param("i", $variante_id);
  $stmt->execute();
  $resultado = $stmt->get_result();
  return ($fila = $resultado->fetch_assoc()) ? $fila['stock'] : 0;
}

// Agregar producto al carrito
if (isset($_GET['add'], $_GET['producto'], $_GET['color'], $_GET['talle'])) {
  $producto_id = intval($_GET['producto']);
  $color = trim($_GET['color']);
  $talle = trim($_GET['talle']);

  $stmt = $conexion->prepare("
    SELECT p.*, 
           IFNULL(dp.porcentaje_descuento, 0) AS mayor_descuento
    FROM productos p
    LEFT JOIN descuentos_productos dp ON p.id = dp.producto_id
    WHERE p.id = ?
");

  $stmt->bind_param("i", $producto_id);
  $stmt->execute();
  $producto = $stmt->get_result()->fetch_assoc();

  if ($producto) {
    $stmtVariante = $conexion->prepare("SELECT id, foto_color FROM variantes_producto WHERE producto_id = ? AND color = ? AND talle = ?");
    $stmtVariante->bind_param("iss", $producto_id, $color, $talle);
    $stmtVariante->execute();
    $variante = $stmtVariante->get_result()->fetch_assoc();

    if ($variante) {
      $id_variante = $variante['id'];
      $foto_color = $variante['foto_color'] ?? $producto['imagen_portada'];
      $stock_disponible = obtenerStockVariante($conexion, $id_variante);

      if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];

      $ya_existe = false;
      foreach ($_SESSION['carrito'] as &$item) {
        if ($item['variante_id'] == $id_variante) {
          if ($item['cantidad'] < $stock_disponible) $item['cantidad']++;
          $ya_existe = true;
          break;
        }
      }

      if (!$ya_existe && $stock_disponible > 0) {
        $_SESSION['carrito'][] = [
          'producto_id' => $producto_id,
          'variante_id' => $id_variante,
          'titulo' => $producto['nombre'] ?? 'Producto sin título',
          'precio_original' => $producto['precio'],
          'precio' => ($producto['mayor_descuento'] > 0)
            ? $producto['precio'] * (1 - $producto['mayor_descuento'] / 100)
            : $producto['precio'],
          'descuento' => $producto['mayor_descuento'] ?? 0,
          'color' => $color,
          'talle' => $talle,
          'cantidad' => 1,
          'imagen' => $foto_color
        ];
      }
    }
  }

  header("Location: ./?page=carrito");
  exit;
}

// Eliminar producto
if (isset($_GET['eliminar'])) {
  $id_eliminar = intval($_GET['eliminar']);
  if (isset($_SESSION['carrito'][$id_eliminar])) {
    unset($_SESSION['carrito'][$id_eliminar]);
    $_SESSION['carrito'] = array_values($_SESSION['carrito']);
  }
  header("Location: ./?page=carrito");
  exit;
}

// Actualizar cantidades
if (isset($_POST['actualizar_cantidad'])) {
  foreach ($_POST['cantidad'] as $key => $cantidad) {
    $cantidad = intval($cantidad);
    if ($cantidad > 0 && isset($_SESSION['carrito'][$key])) {
      $_SESSION['carrito'][$key]['cantidad'] = $cantidad;
    } elseif ($cantidad <= 0 && isset($_SESSION['carrito'][$key])) {
      unset($_SESSION['carrito'][$key]);
      $_SESSION['carrito'] = array_values($_SESSION['carrito']);
    }
  }
  header("Location: ./?page=carrito");
  exit;
}

$total = array_reduce($_SESSION['carrito'] ?? [], function ($total, $item) {
  return $total + ($item['precio'] ?? 0) * ($item['cantidad'] ?? 0);
}, 0);


// Función para validar un cupón
function validarCupon($conexion, $codigo)
{
  $stmt = $conexion->prepare("SELECT * FROM cupones_descuento 
                               WHERE codigo = ? 
                               AND estado = 'activo'
                               AND fecha_inicio <= CURDATE() 
                               AND fecha_fin >= CURDATE()
                               AND usos_restantes > 0");
  $stmt->bind_param("s", $codigo);
  $stmt->execute();
  $resultado = $stmt->get_result();
  return $resultado->fetch_assoc();
}

// Función para aplicar un cupón
function aplicarCuponACarrito($conexion, $codigo)
{
  $cupon = validarCupon($conexion, $codigo);

  if ($cupon) {
    $_SESSION['cupon_aplicado'] = [
      'id' => $cupon['id'],
      'codigo' => $cupon['codigo'],
      'descuento' => $cupon['descuento']
    ];
    return true;
  }

  return false;
}

// Función para registrar el uso de un cupón
function registrarUsoCupon($conexion, $cupon_id)
{
  $stmt = $conexion->prepare("UPDATE cupones_descuento 
                               SET usos_restantes = usos_restantes - 1 
                               WHERE id = ? AND usos_restantes > 0");
  $stmt->bind_param("i", $cupon_id);
  $stmt->execute();

  // Verificar si se agotaron los usos
  $stmt = $conexion->prepare("UPDATE cupones_descuento 
                               SET estado = 'inactivo' 
                               WHERE id = ? AND usos_restantes <= 0");
  $stmt->bind_param("i", $cupon_id);
  $stmt->execute();
}


?>

<!-- Banner promocional superior -->
<div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-4 shadow-md">
  <div class="max-w-7xl mx-auto flex justify-between items-center">
    <div class="flex items-center space-x-2">
      <i class="fas fa-truck text-yellow-300"></i>
      <span class="font-medium">ENVÍOS GRATIS</span>
    </div>
    <div class="hidden md:flex items-center space-x-4">
      <div class="flex items-center">
        <i class="fas fa-shield-alt text-blue-200 mr-2"></i>
        <span class="text-sm">COMPRA SEGURA</span>
      </div>
      <div class="flex items-center">
        <i class="fas fa-undo text-blue-200 mr-2"></i>
        <span class="text-sm">DEVOLUCIONES FÁCILES</span>
      </div>
    </div>
  </div>
</div>

<!-- Contenido principal del carrito -->
<div class="max-w-7xl mx-auto px-4 py-8">
  <?php if (!empty($_SESSION['carrito'])): ?>
    <form id="carritoForm" method="post" action="./?page=carrito" class="bg-white rounded-xl shadow-sm">
      <!-- Encabezado -->
      <div class="border-b border-gray-200 px-6 py-4">
        <div class="flex justify-between items-center">
          <h1 class="text-2xl font-light text-gray-900 flex items-center">
            <i class="fas fa-shopping-cart text-blue-500 mr-3"></i>
            Tu Carrito de Compras
            <span class="ml-3 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
              <?= count($_SESSION['carrito']) ?> <?= count($_SESSION['carrito']) === 1 ? 'artículo' : 'artículos' ?>
            </span>
          </h1>
          <a href="./" class="text-sm text-gray-500 hover:text-blue-600 transition flex items-center">
            <i class="fas fa-chevron-left mr-1 text-xs"></i>
            Continuar comprando
          </a>
        </div>
      </div>

      <div class="flex flex-col lg:flex-row gap-8 p-6">
        <!-- Lista de productos -->
        <div class="lg:w-2/3 space-y-6">
          <?php foreach ($_SESSION['carrito'] as $indice => $item):
            $subtotal = $item['precio'] * $item['cantidad'];
            $stock = obtenerStockVariante($conexion, $item['variante_id']);
          ?>
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 group">
              <div class="flex flex-col sm:flex-row gap-5">
                <!-- Imagen con efecto hover -->
                <div class="sm:w-1/4 relative">
                  <div class="aspect-w-1 aspect-h-1 bg-gray-50 rounded-lg overflow-hidden">
                    <img src="./admin/controllers/<?= htmlspecialchars($item['imagen']) ?>"
                      alt="<?= htmlspecialchars($item['titulo']) ?>"
                      class="w-full h-full object-contain p-4 transition-transform duration-300 group-hover:scale-105">
                  </div>
                  <span class="absolute top-3 right-3 bg-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold shadow-lg border border-gray-200">
                    <?= htmlspecialchars($item['talle']) ?>
                  </span>
                </div>

                <!-- Detalles del producto -->
                <div class="sm:w-3/4 flex flex-col justify-between">
                  <div>
                    <div class="flex justify-between items-start">
                      <h3 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($item['titulo']) ?></h3>
                      <button type="button" onclick="if(confirm('¿Eliminar este producto del carrito?')) window.location.href='./?page=carrito&eliminar=<?= $indice ?>'"
                        class="text-gray-400 hover:text-red-500 transition-colors">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </div>

                    <!-- Color y disponibilidad -->
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                      <div class="flex items-center">
                        <span class="inline-block w-4 h-4 rounded-full mr-2 border border-gray-200" style="background-color: <?= htmlspecialchars($item['color']) ?>"></span>
                        <span class="text-sm text-gray-600"><?= ucfirst(htmlspecialchars($item['color'])) ?></span>
                      </div>
                      <div class="text-xs px-2 py-1 rounded-full <?= $stock > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                        <?= $stock > 0 ? "Disponible ($stock)" : "Agotado" ?>
                      </div>
                    </div>

                    <!-- Precio y cantidad -->
                    <div class="mt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                      <!-- Selector de cantidad mejorado -->
                      <div class="flex items-center border border-gray-200 rounded-lg w-fit">
                        <button type="button"
                          class="decrement-btn px-3 py-2 text-gray-500 hover:bg-gray-50 transition rounded-l-lg"
                          data-index="<?= $indice ?>">
                          <i class="fas fa-minus text-xs"></i>
                        </button>
                        <input type="number" name="cantidad[<?= $indice ?>]"
                          value="<?= $item['cantidad'] ?>"
                          min="1"
                          max="<?= $stock ?>"
                          class="w-12 text-center border-x border-gray-200 focus:ring-2 focus:ring-blue-300 focus:border-blue-300 bg-white text-gray-700">
                        <button type="button"
                          class="increment-btn px-3 py-2 text-gray-500 hover:bg-gray-50 transition rounded-r-lg"
                          data-index="<?= $indice ?>"
                          data-max="<?= $stock ?>">
                          <i class="fas fa-plus text-xs"></i>
                        </button>
                      </div>

                      <!-- Precio con descuento -->
                      <div class="text-right">
                        <?php if ($item['descuento'] > 0): ?>
                          <div class="text-sm text-gray-400 line-through">$<?= number_format($item['precio_original'], 2, ',', '.') ?></div>
                          <div class="flex items-center justify-end">
                            <span class="text-lg font-bold text-gray-900">$<?= number_format($item['precio'], 2, ',', '.') ?></span>
                            <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded-full font-medium"><?= $item['descuento'] ?>% OFF</span>
                          </div>
                        <?php else: ?>
                          <div class="text-lg font-bold text-gray-900">$<?= number_format($item['precio'], 2, ',', '.') ?></div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Subtotal -->
              <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                <span class="text-sm text-gray-500">Subtotal por <?= $item['cantidad'] ?> <?= $item['cantidad'] === 1 ? 'unidad' : 'unidades' ?></span>
                <span class="font-bold text-gray-900">$<?= number_format($subtotal, 2, ',', '.') ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

<!-- Resumen del pedido - Sticky -->
<div class="lg:w-1/3 lg:sticky lg:top-8 h-fit">
  <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
    <h2 class="text-xl font-semibold text-gray-900 mb-6 flex items-center">
      <i class="fas fa-receipt text-blue-500 mr-2"></i>
      Resumen del Pedido
    </h2>

    <?php
    // Cálculos previos (igual que antes)
    $subtotal = array_reduce($_SESSION['carrito'] ?? [], function($total, $item) {
      return $total + ($item['precio_original'] * $item['cantidad']);
    }, 0);
    
    $descuento_productos = array_reduce($_SESSION['carrito'] ?? [], function($total, $item) {
      return $total + (($item['precio_original'] - $item['precio']) * $item['cantidad']);
    }, 0);
    
    $subtotal_con_descuentos = $subtotal - $descuento_productos;
    
    $descuento_cupon = 0;
    $codigo_cupon = '';
    if (isset($_SESSION['cupon_aplicado'])) {
      $descuento_cupon = $subtotal_con_descuentos * ($_SESSION['cupon_aplicado']['descuento'] / 100);
      $codigo_cupon = $_SESSION['cupon_aplicado']['codigo'];
    }
    
    $total_compra = $subtotal_con_descuentos - $descuento_cupon;
    $cantidad_items = array_reduce($_SESSION['carrito'] ?? [], function($total, $item) {
      return $total + $item['cantidad'];
    }, 0);
    
    // Calculamos el ahorro total
    $ahorro_total = $descuento_productos + $descuento_cupon;
    $porcentaje_ahorro = $subtotal > 0 ? round(($ahorro_total / $subtotal) * 100) : 0;
    ?>

    <!-- Sección de ahorro destacada -->
    <?php if ($ahorro_total > 0): ?>
    <div class="mb-4 p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg border border-green-200 shadow-sm">
      <div class="flex items-center justify-between">
        <div>
          <span class="block text-sm font-medium text-green-800">¡Estás ahorrando!</span>
          <span class="block text-2xl font-bold text-green-600">$<?= number_format($ahorro_total, 2, ',', '.') ?></span>
          <?php if ($porcentaje_ahorro > 0): ?>
            <span class="block text-xs text-green-700">(<?= $porcentaje_ahorro ?>% de descuento total)</span>
          <?php endif; ?>
        </div>
        <div class="bg-green-100 p-2 rounded-full">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
          </svg>
        </div>
      </div>
      
      <!-- Desglose de ahorros -->
      <div class="mt-3 space-y-1">
        <?php if ($descuento_productos > 0): ?>
        <div class="flex justify-between text-sm text-green-700">
          <span class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
            Descuentos en productos
          </span>
          <span class="font-medium">-$<?= number_format($descuento_productos, 2, ',', '.') ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($descuento_cupon > 0): ?>
        <div class="flex justify-between text-sm text-green-700">
          <span class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Cupón "<?= $codigo_cupon ?>"
          </span>
          <span class="font-medium">-$<?= number_format($descuento_cupon, 2, ',', '.') ?></span>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Resumen compacto -->
    <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-100">
      <div class="flex justify-between items-center mb-2">
        <span class="text-gray-600"><?= $cantidad_items ?> <?= $cantidad_items === 1 ? 'artículo' : 'artículos' ?></span>
        <span class="font-medium">$<?= number_format($subtotal_con_descuentos, 2, ',', '.') ?></span>
      </div>
    </div>

    <!-- Desglose detallado -->
    <div class="space-y-3 mb-6">
      <!-- Subtotal -->
      <div class="flex justify-between text-sm">
        <span class="text-gray-500">Subtotal (<?= $cantidad_items ?> <?= $cantidad_items === 1 ? 'artículo' : 'artículos' ?>)</span>
        <span>$<?= number_format($subtotal, 2, ',', '.') ?></span>
      </div>

      <!-- Descuentos -->
      <?php if ($descuento_productos > 0): ?>
        <div class="flex justify-between text-sm text-green-600">
          <span>Descuento en productos</span>
          <span>-$<?= number_format($descuento_productos, 2, ',', '.') ?></span>
        </div>
      <?php endif; ?>

      <!-- Envío -->
      <div class="flex justify-between text-sm">
        <span class="text-gray-500">Envío</span>
        <span class="text-green-600 font-medium">Gratis</span>
      </div>

      <!-- Cupón (si aplica) -->
      <?php if ($descuento_cupon > 0): ?>
        <div class="flex justify-between text-sm text-green-600">
          <div class="flex items-center">
            <span>Cupón "<?= $codigo_cupon ?>"</span>
            <button onclick="removerCupon()" class="ml-2 text-red-500 hover:text-red-700" title="Quitar cupón">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
          <span>-$<?= number_format($descuento_cupon, 2, ',', '.') ?></span>
        </div>
      <?php endif; ?>

      <!-- Total -->
      <div class="border-t border-gray-200 pt-3 mt-3">
        <div class="flex justify-between font-medium">
          <span>Total</span>
          <div class="text-right">
            <div class="text-lg text-gray-900">$<?= number_format($total_compra, 2, ',', '.') ?></div>
            <?php if ($ahorro_total > 0): ?>
              <div class="text-xs text-gray-500 line-through">$<?= number_format($subtotal, 2, ',', '.') ?></div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Resto del código permanece igual -->
    <!-- Campo para cupón -->
    <?php if (!isset($_SESSION['cupon_aplicado'])): ?>
      <div class="mt-4" id="campo-cupon">
        <label for="cupon" class="sr-only">Cupón de descuento</label>
        <div class="flex">
          <input type="text" id="cupon" name="cupon"
            class="flex-1 border border-gray-300 rounded-l-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Código de cupón">
          <button type="button" onclick="aplicarCupon()"
            class="px-4 py-2 bg-blue-600 text-white rounded-r-lg hover:bg-blue-700 transition-colors">
            Aplicar
          </button>
        </div>
        <p id="cupon-mensaje" class="mt-1 text-sm hidden"></p>
      </div>
    <?php endif; ?>
    <br>
    <!-- Botón de compra -->
    <button type="button"
      onclick="<?php echo $usuario_logueado ? 'abrirModalCheckout()' : 'mostrarMensajeLogin()'; ?>"
      class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white py-3 px-4 rounded-xl font-bold shadow-md hover:shadow-lg transition-all duration-300 flex items-center justify-center space-x-2">
      <i class="fas fa-lock"></i>
      <span>FINALIZAR COMPRA</span>
    </button>

    <!-- Medios de pago -->
    <div class="mt-6 pt-4 border-t border-gray-100">
      <p class="text-xs text-gray-500 mb-3 text-center">Métodos de pago aceptados:</p>
      <div class="flex flex-wrap justify-center gap-3">
        <img src="https://http2.mlstatic.com/storage/logos-api-admin/a5f047d0-9be0-11ec-aad4-c3381f368aaf-m.svg" class="h-6" alt="Visa" loading="lazy" title="Visa">
        <img src="https://http2.mlstatic.com/storage/logos-api-admin/b2c93a40-f3be-11eb-9984-b7076edb0bb7-m.svg" class="h-6" alt="Mastercard" loading="lazy" title="Mastercard">
        <img src="https://http2.mlstatic.com/storage/logos-api-admin/992bc350-f3be-11eb-826e-6db365b9e0dd-m.svg" class="h-6" alt="American Express" loading="lazy" title="American Express">
        <img src="https://http2.mlstatic.com/storage/logos-api-admin/aa2b8f70-5c85-11ec-ae75-df2bef173be2-m.svg" class="h-6" alt="Mercado Pago" loading="lazy" title="Mercado Pago">
      </div>
      <p class="text-xs text-gray-400 mt-3 text-center">
        Aceptamos todas las tarjetas de crédito, débito a través de MercadoPago
      </p>
    </div>
  </div>
</div>
      </div>


          <!-- Garantías -->
          <div class="mt-4 bg-blue-50 p-4 rounded-xl border border-blue-100">
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0 text-blue-500">
                <i class="fas fa-shield-alt text-xl"></i>
              </div>
              <div>
                <h4 class="font-medium text-gray-900 mb-1">Compra Protegida</h4>
                <p class="text-xs text-gray-600">Recibí el producto que esperabas o te devolvemos tu dinero.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  <?php else: ?>
    <!-- Estado vacío mejorado -->
    <div class="text-center py-20 max-w-md mx-auto">
      <div class="relative inline-block mb-6">
        <div class="absolute inset-0 bg-blue-100 rounded-full blur-md opacity-50"></div>
        <div class="relative bg-white p-6 rounded-full border-8 border-blue-50 shadow-sm">
          <i class="fas fa-shopping-cart text-blue-500 text-4xl"></i>
        </div>
      </div>
      <h2 class="mt-4 text-2xl font-light text-gray-900">Tu carrito está vacío</h2>
      <p class="mt-2 text-gray-500">Agrega productos para comenzar tu compra</p>
      <a href="./" class="mt-6 inline-block px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 font-medium">
        <i class="fas fa-chevron-left mr-2"></i>
        Ver productos
      </a>
    </div>
  <?php endif; ?>
</div>

<!-- Modal de Checkout - Versión final integrada con MercadoPago -->
<div id="checkoutModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
  <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <!-- Fondo oscuro -->
    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
      <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <!-- Contenido del modal -->
    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
      <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <div class="sm:flex sm:items-start">
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4 flex items-center justify-between">
              <span>
                <i class="fas fa-truck text-blue-500 mr-2"></i>
                Información de Envío
              </span>
              <button onclick="cerrarModalCheckout()" class="text-gray-400 hover:text-gray-500">
                <i class="fas fa-times"></i>
              </button>
            </h3>

            <!-- Formulario de envío -->
            <form id="formEnvio" class="space-y-4" action="./procesar_pago_mercadopago" method="POST">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nombre -->
                <div>
                  <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre completo*</label>
                  <input type="text" id="nombre" name="nombre" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                  <p id="error-nombre" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                <!-- Teléfono -->
                <div>
                  <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono*</label>
                  <input type="tel" id="telefono" name="telefono" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    pattern="[0-9]{10,15}">
                  <p id="error-telefono" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>
              </div>

              <!-- Email -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email*</label>
                <input type="email" id="email" name="email" required
                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <p id="error-email" class="mt-1 text-sm text-red-600 hidden"></p>
              </div>

              <!-- DNI -->
              <div>
                <label for="dni" class="block text-sm font-medium text-gray-700">DNI*</label>
                <input type="text" id="dni" name="dni" placeholder="Ingrese su DNI" required maxlength="8"
                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <p id="error-dni" class="mt-1 text-sm text-red-600 hidden"></p>
              </div>

              <!-- Dirección -->
              <div>
                <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección*</label>
                <input type="text" id="direccion" name="direccion" required
                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <p id="error-direccion" class="mt-1 text-sm text-red-600 hidden"></p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Entre calles -->
                <div>
                  <label for="entre_calles" class="block text-sm font-medium text-gray-700">Entre calles*</label>
                  <input type="text" id="entre_calles" name="entre_calles" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                  <p id="error-entre_calles" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                <!-- Código Postal -->
                <div>
                  <label for="codigo_postal" class="block text-sm font-medium text-gray-700">Código Postal*</label>
                  <input type="text" id="codigo_postal" name="codigo_postal" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    pattern="[0-9]{4,8}">
                  <p id="error-codigo_postal" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Localidad -->
                <div>
                  <label for="localidad" class="block text-sm font-medium text-gray-700">Localidad*</label>
                  <input type="text" id="localidad" name="localidad" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                  <p id="error-localidad" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                <!-- Provincia -->
                <div>
                  <label for="provincia" class="block text-sm font-medium text-gray-700">Provincia*</label>
                  <select id="provincia" name="provincia" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Seleccionar...</option>
                    <option value="Buenos Aires">Buenos Aires</option>
                    <option value="Capital Federal">Capital Federal</option>
                    <option value="Catamarca">Catamarca</option>
                    <option value="Chaco">Chaco</option>
                    <option value="Chubut">Chubut</option>
                    <option value="Córdoba">Córdoba</option>
                    <option value="Corrientes">Corrientes</option>
                    <option value="Entre Ríos">Entre Ríos</option>
                    <option value="Formosa">Formosa</option>
                    <option value="Jujuy">Jujuy</option>
                    <option value="La Pampa">La Pampa</option>
                    <option value="La Rioja">La Rioja</option>
                    <option value="Mendoza">Mendoza</option>
                    <option value="Misiones">Misiones</option>
                    <option value="Neuquén">Neuquén</option>
                    <option value="Río Negro">Río Negro</option>
                    <option value="Salta">Salta</option>
                    <option value="San Juan">San Juan</option>
                    <option value="San Luis">San Luis</option>
                    <option value="Santa Cruz">Santa Cruz</option>
                    <option value="Santa Fe">Santa Fe</option>
                    <option value="Santiago del Estero">Santiago del Estero</option>
                    <option value="Tierra del Fuego">Tierra del Fuego</option>
                    <option value="Tucumán">Tucumán</option>
                  </select>
                  <p id="error-provincia" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                <!-- Departamento/Piso -->
                <div>
                  <label for="departamento" class="block text-sm font-medium text-gray-700">Departamento/Piso (opcional)</label>
                  <input type="text" id="departamento" name="departamento"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
              </div>

              <!-- Referencias -->
              <div>
                <label for="referencias" class="block text-sm font-medium text-gray-700">Referencias adicionales (opcional)</label>
                <textarea id="referencias" name="referencias" rows="2"
                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Ej: Casa con rejas negras, timbre a nombre de..."></textarea>
              </div>

              <!-- Método de envío -->
              <div class="border-t border-gray-200 pt-4">
                <h4 class="text-md font-medium text-gray-900 mb-3">Método de envío</h4>
                <div class="space-y-2">
                  <div class="flex items-center">
                    <input id="envio_estandar" name="metodo_envio" type="radio" value="estandar" checked
                      class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                    <label for="envio_estandar" class="ml-3 block text-sm font-medium text-gray-700">
                      Envío estándar - Gratis
                    </label>
                  </div>
                </div>
              </div>

              <!-- Input hidden con los datos del carrito -->
              <?php if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])): ?>
                <input type="hidden" name="carrito_json" value="<?= htmlspecialchars(json_encode($_SESSION['carrito'])) ?>">
              <?php endif; ?>
            </form>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
        <button type="button" onclick="validarYContinuar()"
          class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
          Continuar al pago
        </button>
        <button type="button" onclick="cerrarModalCheckout()"
          class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
          Cancelar
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  function mostrarMensajeLogin() {
    alert("Debes iniciar sesión para finalizar la compra.");
    // También podés redirigir automáticamente si querés:
    window.location.href = "./?page=iniciar_sesion";
  }


  // Abrir el modal de checkout
  function abrirModalCheckout() {
    // Verificar que hay productos en el carrito
    <?php if (empty($_SESSION['carrito'])): ?>
      alert('El carrito está vacío');
      return;
    <?php endif; ?>

    const modal = document.getElementById("checkoutModal");
    if (modal) {
      modal.classList.remove("hidden");
      document.body.classList.add("overflow-hidden");
    }
  }

  // Cerrar el modal de checkout
  function cerrarModalCheckout() {
    const modal = document.getElementById("checkoutModal");
    if (modal) {
      modal.classList.add("hidden");
      document.body.classList.remove("overflow-hidden");
    }
  }

  // Validar el formulario de envío
  function validarFormularioEnvio() {
    let valido = true;
    const campos = ['nombre', 'telefono', 'email', 'dni', 'direccion', 'entre_calles', 'codigo_postal', 'localidad', 'provincia'];

    // Resetear errores
    campos.forEach(campo => {
      document.getElementById(`error-${campo}`).classList.add('hidden');
    });

    // Validar cada campo
    for (const campo of campos) {
      const input = document.getElementById(campo);
      const valor = input.value.trim();
      const errorElement = document.getElementById(`error-${campo}`);

      if (!valor) {
        errorElement.textContent = `Este campo es obligatorio`;
        errorElement.classList.remove('hidden');
        input.focus();
        valido = false;
        break;
      }

      // Validaciones específicas
      if (campo === 'email' && !validarEmail(valor)) {
        errorElement.textContent = `Ingrese un email válido`;
        errorElement.classList.remove('hidden');
        input.focus();
        valido = false;
        break;
      }



      if (campo === 'telefono' && !validarTelefono(valor)) {
        errorElement.textContent = `Ingrese un teléfono válido (10-15 dígitos)`;
        errorElement.classList.remove('hidden');
        input.focus();
        valido = false;
        break;
      }

      if (campo === 'codigo_postal' && !validarCodigoPostal(valor)) {
        errorElement.textContent = `Ingrese un código postal válido`;
        errorElement.classList.remove('hidden');
        input.focus();
        valido = false;
        break;
      }
    }

    return valido;
  }

  // Funciones de validación
  function validarEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }


  function validarDni(inputElement) {
    if (inputElement) {
      inputElement.addEventListener('input', function() {
        // Eliminar cualquier carácter no numérico y asegurar que no tenga más de 8 dígitos
        this.value = this.value.replace(/\D/g, '').slice(0, 8);

        // Mostrar mensaje de error si el valor excede los 8 dígitos
        const errorDni = document.getElementById('error-dni');
        if (this.value.length > 8) {
          errorDni.textContent = 'El DNI no puede tener más de 8 dígitos.';
          errorDni.classList.remove('hidden');
        } else {
          errorDni.classList.add('hidden');
        }
      });
    } else {
      console.warn("Elemento DNI no encontrado.");
    }
  }

  // Asegurarse de que el DOM esté completamente cargado
  document.addEventListener('DOMContentLoaded', function() {
    const dniInput = document.getElementById('dni');
    if (dniInput) {
      validarDni(dniInput);
    }
  });

  function validarTelefono(telefono) {
    const re = /^[0-9]{10,15}$/;
    return re.test(telefono);
  }

  function validarCodigoPostal(cp) {
    const re = /^[0-9]{4,8}$/;
    return re.test(cp);
  }

  // Validar y continuar al pago
  function validarYContinuar() {
    if (validarFormularioEnvio()) {
      // Enviar el formulario directamente al servidor
      document.getElementById('formEnvio').submit();
    }
  }

  // Inicialización
  document.addEventListener('DOMContentLoaded', function() {
    // Agregar evento al botón de finalizar compra
    const btnCheckout = document.querySelector('button[onclick="abrirModalCheckout()"]');
    if (btnCheckout) {
      btnCheckout.addEventListener('click', abrirModalCheckout);
    }

    // Manejar los botones de incremento/decremento de cantidad
    document.querySelectorAll('.increment-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const index = this.getAttribute('data-index');
        const max = parseInt(this.getAttribute('data-max'));
        const input = document.querySelector(`input[name="cantidad[${index}]"]`);
        let value = parseInt(input.value);
        if (value < max) {
          input.value = value + 1;
        }
      });
    });

    document.querySelectorAll('.decrement-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const index = this.getAttribute('data-index');
        const input = document.querySelector(`input[name="cantidad[${index}]"]`);
        let value = parseInt(input.value);
        if (value > 1) {
          input.value = value - 1;
        }
      });
    });
  });

  // Función para actualizar automáticamente el carrito
  function actualizarCarrito() {
    // Crear un formulario virtual con los datos actuales
    const form = document.createElement('form');
    form.method = 'post';
    form.action = './?page=carrito';

    // Agregar campo oculto para indicar la actualización
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'actualizar_cantidad';
    input.value = '1';
    form.appendChild(input);

    // Agregar todas las cantidades actuales
    document.querySelectorAll('input[name^="cantidad["]').forEach(input => {
      const clone = input.cloneNode();
      clone.name = input.name;
      clone.value = input.value;
      form.appendChild(clone);
    });

    // Enviar el formulario
    document.body.appendChild(form);
    form.submit();
  }

  // Configurar eventos para los botones de incremento/decremento
  function configurarEventosCantidad() {
    document.querySelectorAll('.increment-btn, .decrement-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        setTimeout(actualizarCarrito, 300); // Pequeño retraso para mejor experiencia
      });
    });

    document.querySelectorAll('input[name^="cantidad["]').forEach(input => {
      input.addEventListener('change', actualizarCarrito);
    });
  }

  // Inicialización cuando el DOM esté listo
  document.addEventListener('DOMContentLoaded', function() {
    configurarEventosCantidad();
  });

  // Modifica la función actualizarCarrito
  function actualizarCarrito() {
    // Mostrar spinner de carga
    const spinner = document.createElement('div');
    spinner.className = 'fixed top-0 left-0 right-0 bottom-0 bg-black bg-opacity-30 flex items-center justify-center z-50';
    spinner.innerHTML = '<div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-blue-500"></div>';
    document.body.appendChild(spinner);

    // Crear y enviar formulario
    const form = document.createElement('form');
    form.method = 'post';
    form.action = './?page=carrito';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'actualizar_cantidad';
    input.value = '1';
    form.appendChild(input);

    document.querySelectorAll('input[name^="cantidad["]').forEach(input => {
      const clone = input.cloneNode();
      clone.name = input.name;
      clone.value = input.value;
      form.appendChild(clone);
    });

    document.body.appendChild(form);
    form.submit();
  }

  function aplicarCupon() {
    const codigo = document.getElementById('cupon').value.trim();
    const mensaje = document.getElementById('cupon-mensaje');

    if (!codigo) {
      mostrarMensajeCupon('Por favor ingresa un código', 'error');
      return;
    }

    mostrarMensajeCupon('Validando cupón...', 'carga');

    fetch('./aplicar_cupon.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `codigo=${encodeURIComponent(codigo)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.exito) {
          mostrarMensajeCupon(`Cupón aplicado: ${data.descuento}% de descuento`, 'exito');
          // Ocultar el campo de cupón
          document.getElementById('campo-cupon').style.display = 'none';
          // Recargar la página para actualizar totales
          setTimeout(() => window.location.reload(), 1000);
        } else {
          mostrarMensajeCupon(data.mensaje || 'Cupón no válido', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        mostrarMensajeCupon('Error al validar el cupón', 'error');
      });
  }

  function mostrarMensajeCupon(mensaje, tipo) {
    const mensajeElement = document.getElementById('cupon-mensaje');
    if (!mensajeElement) return;

    mensajeElement.textContent = mensaje;
    mensajeElement.className = 'text-sm'; // Resetear clases

    switch (tipo) {
      case 'error':
        mensajeElement.classList.add('text-red-600');
        break;
      case 'exito':
        mensajeElement.classList.add('text-green-600');
        break;
      case 'carga':
        mensajeElement.classList.add('text-blue-600');
        break;
    }

    mensajeElement.classList.remove('hidden');
  }

  function removerCupon() {
    // Mostrar carga
    const mensajeElement = document.getElementById('cupon-mensaje');
    if (mensajeElement) {
      mensajeElement.textContent = 'Eliminando cupón...';
      mensajeElement.className = 'text-sm text-blue-600';
      mensajeElement.classList.remove('hidden');
    }

    fetch('./remover_cupon.php', {
        method: 'POST'
      })
      .then(response => response.json())
      .then(data => {
        if (data.exito) {
          // Recargar la página para actualizar los totales
          window.location.reload();
        } else {
          if (mensajeElement) {
            mensajeElement.textContent = data.mensaje || 'Error al eliminar cupón';
            mensajeElement.className = 'text-sm text-red-600';
          }
        }
      })
      .catch(error => {
        console.error('Error:', error);
        if (mensajeElement) {
          mensajeElement.textContent = 'Error al conectar con el servidor';
          mensajeElement.className = 'text-sm text-red-600';
        }
      });
  }
</script>