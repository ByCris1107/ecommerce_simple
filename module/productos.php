<?php
// Obtener filtros desde la URL
$filtro_genero = $_GET['genero'] ?? '';
$filtro_categoria = $_GET['categoria'] ?? '';
$filtro_subcategoria = $_GET['subcategoria'] ?? '';

// Inicializar variables para nombres
$nombre_categoria = '';
$nombre_subcategoria = '';

// Obtener nombres de categoría/subcategoría si existen
if (!empty($filtro_categoria)) {
    $stmt = $conexion->prepare("SELECT nombre FROM categorias WHERE id = ?");
    $stmt->bind_param("i", $filtro_categoria);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $nombre_categoria = $row['nombre'];
    }
}

if (!empty($filtro_subcategoria)) {
    $stmt = $conexion->prepare("SELECT nombre FROM subcategorias WHERE id = ?");
    $stmt->bind_param("i", $filtro_subcategoria);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $nombre_subcategoria = $row['nombre'];
    }
}

// Consulta para obtener productos con posibles descuentos
$query = "SELECT p.*, dp.porcentaje_descuento 
          FROM productos p 
          LEFT JOIN descuentos_productos dp ON p.id = dp.producto_id 
          WHERE 1=1";
$tipos = '';
$params = [];

if (!empty($filtro_genero)) {
    $query .= " AND p.genero = ?";
    $tipos .= 's';
    $params[] = $filtro_genero;
}

if (!empty($filtro_categoria)) {
    $query .= " AND p.categoria_id = ?";
    $tipos .= 'i';
    $params[] = $filtro_categoria;
}

if (!empty($filtro_subcategoria)) {
    $query .= " AND p.subcategoria_id = ?";
    $tipos .= 'i';
    $params[] = $filtro_subcategoria;
}

$stmt = $conexion->prepare($query);
if (!empty($tipos)) {
    $stmt->bind_param($tipos, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
$productos = $res->fetch_all(MYSQLI_ASSOC);

/**
 * Renderizar producto con efectos ULTRA COMERCIALES
 */
function renderProductoConEfectos($producto, $index = 0) {
    $es_oferta = isset($producto['porcentaje_descuento']) && $producto['porcentaje_descuento'] > 0;
    $precio_original = (float)$producto['precio'];
    $descuento = $es_oferta ? (int)$producto['porcentaje_descuento'] : 0;
    $precio_final = $es_oferta ? $precio_original * (1 - $descuento/100) : $precio_original;
    $ahorro = $precio_original - $precio_final;
    $url = "./?page=detalles_producto&producto=".(int)$producto['id'];
    $img = "./admin/controllers/".htmlspecialchars($producto['imagen_portada']);
    $nombre = htmlspecialchars($producto['nombre']);
    $es_nuevo = strtotime($producto['fecha_creacion'] ?? 'now') > strtotime('-7 days');
    ?>
    <div class="producto-ultra group relative bg-white rounded-xl shadow-lg overflow-hidden transition-all duration-500 hover:shadow-2xl hover:-translate-y-2 border-2 <?= $es_oferta ? 'border-red-500' : 'border-gray-200' ?>"
         style="animation-delay: <?= $index * 0.1 ?>s;">
        <?php if($es_oferta): ?>
        <div class="absolute top-3 left-3 z-20 bg-red-600 text-white text-sm font-bold px-3 py-1 rounded-full shadow-xl animate-pulse">
            -<?= $descuento ?>% OFF
        </div>
        <?php elseif($es_nuevo): ?>
        <div class="absolute top-3 left-3 z-20 bg-green-600 text-white text-sm font-bold px-3 py-1 rounded-full shadow-xl">
            ¡NUEVO!
        </div>
        <?php endif; ?>
        
        <?php if($es_oferta): ?>
        <div class="absolute top-3 right-3 z-20 bg-black text-white text-xs font-bold px-2 py-1 rounded">
            <i class="fas fa-clock mr-1"></i> <?= date('H:i', strtotime('+3 days')) ?>
        </div>
        <?php endif; ?>
        
        <a href="<?= $url ?>" class="block relative aspect-square overflow-hidden">
            <img src="<?= $img ?>" alt="<?= $nombre ?>" 
                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" 
                 loading="lazy">
            
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex flex-col justify-between p-4">
                <div class="text-right">
                    <span class="inline-block bg-white text-black text-xs font-bold px-2 py-1 rounded-full">
                        <i class="fas fa-eye mr-1"></i> Ver detalles
                    </span>
                </div>
                <div class="text-center">
                    <span class="inline-block bg-white text-black text-sm font-bold px-4 py-2 rounded-full shadow-lg transform group-hover:scale-110 transition-transform">
                        COMPRA AHORA
                    </span>
                </div>
            </div>
        </a>
        
        <div class="p-4">
            <h3 class="text-lg font-bold text-gray-900 mb-1 line-clamp-2"><?= $nombre ?></h3>
            
            <div class="flex justify-between items-center mt-3">
                <div>
                    <?php if($es_oferta): ?>
                    <div class="flex items-baseline gap-2">
                        <span class="text-xl font-extrabold text-red-600">$<?= number_format($precio_final, 2, ',', '.') ?></span>
                        <span class="text-sm text-gray-400 line-through">$<?= number_format($precio_original, 2, ',', '.') ?></span>
                    </div>
                    <div class="text-xs text-green-600 font-bold mt-1">
                        <i class="fas fa-bolt"></i> Ahorrás $<?= number_format($ahorro, 2, ',', '.') ?>
                    </div>
                    <?php else: ?>
                    <span class="text-xl font-bold text-gray-900">$<?= number_format($precio_original, 2, ',', '.') ?></span>
                    <?php endif; ?>
                </div>
                
                <button class="add-to-cart bg-<?= $es_oferta ? 'red' : 'gray' ?>-600 text-white p-2 rounded-full hover:bg-<?= $es_oferta ? 'red' : 'gray' ?>-700 transition-colors" 
                        data-id="<?= (int)$producto['id'] ?>">
                    <i class="fas fa-shopping-cart"></i>
                </button>
            </div>
            
            <?php if($es_oferta): ?>
            <div class="mt-3">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-red-500 h-2 rounded-full" style="width: <?= min(100, $descuento*1.5) ?>%"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>¡Quedan pocas unidades!</span>
                    <span><?= rand(3, 10) ?> vendidos hoy</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - <?= htmlspecialchars($store_name ?? 'Tienda') ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Animaciones para las cartas */
        .producto-ultra {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Animación para el pulso de las ofertas */
        .animate-pulse {
            animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* Efectos hover para las tarjetas */
        .producto-ultra {
            transition: all 0.3s ease;
        }
        
        .producto-ultra:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Efecto para el botón de añadir al carrito */
        .add-to-cart {
            transition: all 0.3s ease;
        }
        
        .add-to-cart:hover {
            transform: scale(1.1);
        }
        
        /* Efecto para el texto que se corta */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-50">

<!-- Sección de Productos -->
<section class="w-full px-2 sm:px-4 md:px-6 py-12 bg-white">
    <div class="container mx-auto">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-3">
                <?php if (!empty($nombre_subcategoria)): ?>
                    <span class="text-orange-500"><?= htmlspecialchars($nombre_subcategoria) ?></span>
                <?php elseif (!empty($nombre_categoria)): ?>
                    <span class="text-orange-500"><?= htmlspecialchars($nombre_categoria) ?></span>
                <?php elseif (!empty($filtro_genero)): ?>
                    Productos para <span class="text-orange-500"><?= htmlspecialchars($filtro_genero) ?></span>
                <?php else: ?>
                    Todos nuestros <span class="text-orange-500">productos</span>
                <?php endif; ?>
            </h2>
            <div class="w-24 h-1 bg-orange-500 mx-auto rounded-full"></div>
            <p class="text-gray-600 mt-4 max-w-2xl mx-auto">
                Los mejores productos seleccionados especialmente para ti
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php if (empty($productos)): ?>
                <p class="text-center text-gray-500 col-span-full">No hay productos disponibles con los filtros aplicados.</p>
            <?php else: ?>
                <?php foreach ($productos as $index => $producto): ?>
                    <?php renderProductoConEfectos($producto, $index); ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
    // Animación para botones 'Añadir al carrito'
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-id');
                const esOferta = this.classList.contains('bg-red-600');
                
                // Animación
                this.innerHTML = '<i class="fas fa-check"></i>';
                this.classList.remove('bg-red-600', 'bg-gray-600');
                this.classList.add('bg-green-500');
                
                // Aquí iría la lógica AJAX para añadir al carrito
                console.log('Producto añadido al carrito:', productId);
                
                // Restaurar después de 2 segundos
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-shopping-cart"></i>';
                    this.classList.add(esOferta ? 'bg-red-600' : 'bg-gray-600');
                    this.classList.remove('bg-green-500');
                }, 2000);
            });
        });
    });
</script>

</body>
</html>