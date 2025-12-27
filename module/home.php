<?php
// Configuración Ultra Comercial
define('DEFAULT_COVER_IMAGE', 'portada-predeterminada.jpg');
define('DEFAULT_STORE_IMAGE', 'imagen-predeterminada.jpg');
define('TIENDA_NOMBRE', $store_name);
define('COLOR_PRINCIPAL', '#FF3D00'); // Naranja eléctrico para mayor impacto
define('COLOR_SECUNDARIO', '#00C853'); // Verde vibrante
define('TIENDA_SLOGAN', '¡Las MEJORES OFERTAS del mercado!');
define('TIENDA_CTA', 'COMPRA AHORA'); // Texto para botones
define('TIEMPO_OFERTA', date('Y-m-d H:i:s', strtotime('+3 days'))); // Oferta válida por 3 días

$portada_tienda_celular = getUltraImage($conexion, 'portada_tienda_celular', DEFAULT_COVER_IMAGE);

/**
 * Función ultra comercial para consultas
 */
function getUltraProductos($conexion, $limit = 12)
{
    $productos = [];

    // Productos con descuento (prioridad)
    $sql = "SELECT p.*, dp.porcentaje_descuento 
           FROM productos p 
           JOIN descuentos_productos dp ON p.id = dp.producto_id
           ORDER BY dp.porcentaje_descuento DESC 
           LIMIT $limit";

    if ($result = $conexion->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $row['tipo'] = 'oferta';
            $productos[] = $row;
        }
    }

    // Completar con productos normales si no hay suficientes ofertas
    if (count($productos) < $limit) {
        $remaining = $limit - count($productos);
        $sql = "SELECT * FROM productos 
        WHERE id NOT IN (SELECT producto_id FROM descuentos_productos) 
        ORDER BY RAND() 
        LIMIT $remaining";


        if ($result = $conexion->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $row['tipo'] = 'normal';
                $productos[] = $row;
            }
        }
    }

    shuffle($productos); // Mezclar para mejor presentación
    return $productos;
}

/**
 * Obtener imágenes ultra comerciales
 */
function getUltraImage($conexion, $campo, $default)
{
    $sql = "SELECT $campo FROM personalizaciones_tienda WHERE id = 1 LIMIT 1";
    $result = $conexion->query($sql);
    return ($result && $row = $result->fetch_assoc()) ? ($row[$campo] ?? $default) : $default;
}

// Obtener datos comerciales
$portada_tienda = getUltraImage($conexion, 'portada_tienda', DEFAULT_COVER_IMAGE);

// Obtener productos ultra comerciales
$productos_ultra = getUltraProductos($conexion, 16);

$productos_destacados = array_filter($productos_ultra, function ($producto) {
    return isset($producto['tipo']) && $producto['tipo'] === 'normal';
});

// Tomar los primeros 8 destacados sin descuento
$productos_destacados = array_slice($productos_destacados, 0, 8);


$productos_oferta = array_filter($productos_ultra, function ($p) {
    return $p['tipo'] === 'oferta';
});
$productos_normales = array_filter($productos_ultra, function ($p) {
    return $p['tipo'] === 'normal';
});

// Obtener categorías comerciales
$categorias = [];
$sql_cats = "SELECT id, nombre, imagen FROM categorias ORDER BY RAND() LIMIT 6";
if ($result = $conexion->query($sql_cats)) {
    $categorias = $result->fetch_all(MYSQLI_ASSOC);
}

// Obtener mega ofertas para el popup
$mega_ofertas = [];
$sql_ofertas = "SELECT p.*, dp.porcentaje_descuento, dp.imagen_descuento 
               FROM productos p 
               JOIN descuentos_productos dp ON p.id = dp.producto_id 
               WHERE dp.imagen_descuento IS NOT NULL 
               ORDER BY dp.porcentaje_descuento DESC 
               LIMIT 3";
if ($result = $conexion->query($sql_ofertas)) {
    $mega_ofertas = $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Renderizar producto ULTRA COMERCIAL
 */
function renderUltraProducto($producto)
{
    $es_oferta = ($producto['tipo'] ?? '') === 'oferta';
    $precio_original = (float)$producto['precio'];
    $descuento = $es_oferta ? (int)$producto['porcentaje_descuento'] : 0;
    $precio_final = $es_oferta ? $precio_original * (1 - $descuento / 100) : $precio_original;
    $ahorro = $precio_original - $precio_final;
    $url = "./?page=detalles_producto&producto=" . (int)$producto['id'];
    $img = "./admin/controllers/" . htmlspecialchars($producto['imagen_portada']);
    $nombre = htmlspecialchars($producto['nombre']);
    $es_nuevo = strtotime($producto['fecha_creacion'] ?? 'now') > strtotime('-7 days');
?>
    <div class="producto-ultra group relative bg-white rounded-xl shadow-lg overflow-hidden transition-all duration-500 hover:shadow-2xl hover:-translate-y-2 border-2 <?= $es_oferta ? 'border-red-500' : 'border-gray-200' ?>">
        <?php if ($es_oferta): ?>
            <div class="absolute top-3 left-3 z-20 bg-red-600 text-white text-sm font-bold px-3 py-1 rounded-full shadow-xl animate-pulse">
                -<?= $descuento ?>% OFF
            </div>
        <?php elseif ($es_nuevo): ?>
            <div class="absolute top-3 left-3 z-20 bg-green-600 text-white text-sm font-bold px-3 py-1 rounded-full shadow-xl">
                ¡NUEVO!
            </div>
        <?php endif; ?>

        <!-- Contador de tiempo para ofertas -->
        <?php if ($es_oferta): ?>
            <div class="absolute top-3 right-3 z-20 bg-black text-white text-xs font-bold px-2 py-1 rounded">
                <i class="fas fa-clock mr-1"></i> <?= date('H:i', strtotime(TIEMPO_OFERTA)) ?>
            </div>
        <?php endif; ?>

        <a href="<?= $url ?>" class="block relative aspect-square overflow-hidden">
            <img src="<?= $img ?>" alt="<?= $nombre ?>"
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                loading="lazy">

            <!-- Overlay ultra comercial -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex flex-col justify-between p-4">
                <div class="text-right">
                    <span class="inline-block bg-white text-black text-xs font-bold px-2 py-1 rounded-full">
                        <i class="fas fa-eye mr-1"></i> Ver detalles
                    </span>
                </div>
                <div class="text-center">
                    <span class="inline-block bg-white text-black text-sm font-bold px-4 py-2 rounded-full shadow-lg transform group-hover:scale-110 transition-transform">
                        <?= TIENDA_CTA ?>
                    </span>
                </div>
            </div>
        </a>

        <div class="p-4">
            <h3 class="text-lg font-bold text-gray-900 mb-1 line-clamp-2"><?= $nombre ?></h3>

            <div class="flex justify-between items-center mt-3">
                <div>
                    <?php if ($es_oferta): ?>
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

            <?php if ($es_oferta): ?>
                <div class="mt-3">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-red-500 h-2 rounded-full" style="width: <?= min(100, $descuento * 1.5) ?>%"></div>
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
    <title><?= htmlspecialchars(TIENDA_NOMBRE) ?> | <?= htmlspecialchars(TIENDA_SLOGAN) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --color-principal: <?= COLOR_PRINCIPAL ?>;
            --color-secundario: <?= COLOR_SECUNDARIO ?>;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }

        .bg-ultra {
            background: linear-gradient(135deg, var(--color-principal) 0%, var(--color-secundario) 100%);
        }

        .text-ultra {
            color: var(--color-principal);
        }

        .border-ultra {
            border-color: var(--color-principal);
        }

        .producto-ultra:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .animate-pulse {
            animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-15px);
            }
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>

<!-- Hero Section Ultra Comercial - Versión Mejorada -->
<section class="relative w-full h-screen max-h-[90vh] overflow-hidden">
    <!-- Contenedor de imagen con object-contain -->
    <div class="absolute inset-0">
        <!-- Imagen para escritorio -->
        <img src="./admin/controllers/uploads/tienda_imagenes/<?= htmlspecialchars($portada_tienda) ?>"
            alt="<?= htmlspecialchars(TIENDA_NOMBRE) ?>"
            class="hidden md:block w-full h-full object-contain object-center bg-black">

        <!-- Imagen para celular -->
        <img src="./admin/controllers/uploads/tienda_imagenes/<?= htmlspecialchars($portada_tienda_celular) ?>"
            alt="<?= htmlspecialchars(TIENDA_NOMBRE) ?> versión móvil"
            class="block md:hidden w-full h-full object-contain object-center bg-black">
    </div>

    <!-- Overlay con gradiente comercial mejorado -->
    <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/30 to-transparent"></div>

    <!-- Contenido hero ultra comercial -->
    <div class="relative h-full flex items-center">
        <div class="container mx-auto px-6 z-10">
            <div class="max-w-2xl text-white">
                <h1 class="text-4xl md:text-6xl font-extrabold mb-4 leading-tight animate-fadeInUp">
                    <?= htmlspecialchars(TIENDA_NOMBRE) ?>
                </h1>
                <p class="text-xl md:text-2xl mb-8 font-medium opacity-90 animate-fadeInUp delay-100">
                    <?= htmlspecialchars(TIENDA_SLOGAN) ?>
                </p>
                <div class="flex flex-wrap gap-4 animate-fadeInUp delay-200">
                    <a href="#ofertas"
                        class="bg-white text-black font-bold py-3 px-6 md:py-4 md:px-8 rounded-full hover:bg-gray-100 transition-all duration-300 inline-flex items-center shadow-lg transform hover:scale-105">
                        <span class="mr-2">🔥</span> VER OFERTAS <i class="fas fa-arrow-down ml-2"></i>
                    </a>
                    <a href="./?page=productos"
                        class="bg-transparent border-2 border-white text-white font-bold py-3 px-6 md:py-4 md:px-8 rounded-full hover:bg-white hover:text-black transition-all duration-300 inline-flex items-center shadow-lg transform hover:scale-105">
                        EXPLORAR TIENDA <i class="fas fa-store ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flecha indicadora mejorada -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-10 animate-bounce">
        <a href="#destacados" class="text-white text-4xl hover:text-red-300 transition-colors">
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>

    <!-- Efecto de degradado inferior -->
    <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-black/30 to-transparent"></div>
</section>


<style>
    /* Animaciones para el hero */
    .animate-fadeInUp {
        animation: fadeInUp 1s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
    }

    .animate-fadeInUp.delay-100 {
        animation-delay: 0.3s;
    }

    .animate-fadeInUp.delay-200 {
        animation-delay: 0.6s;
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Asegurar que la imagen no se corte */
    @media (max-aspect-ratio: 16/9) {
        .hero-image-container img {
            object-fit: contain;
            width: auto;
            max-width: none;
            height: 100%;
            margin: 0 auto;
        }
    }
</style>



<!-- Sección Ofertas Ultra Comerciales -->
<section id="ofertas" class="py-16 bg-white relative overflow-hidden">
    <!-- Efecto de fondo -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute top-0 left-0 w-64 h-64 bg-ultra rounded-full filter blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-64 h-64 bg-ultra rounded-full filter blur-3xl translate-x-1/2 translate-y-1/2"></div>
    </div>

    <div class="container mx-auto px-4 relative">
        <div class="text-center mb-12">
            <div class="inline-block bg-red-600 text-white text-sm font-bold px-4 py-1 rounded-full mb-4 shadow-md">
                ¡OFERTAS LIMITADAS!
            </div>
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-3">
                <span class="text-red-600">SUPER</span> DESCUENTOS
            </h2>
            <div class="w-24 h-1 bg-red-600 mx-auto rounded-full"></div>
            <p class="text-gray-600 mt-4 max-w-2xl mx-auto">
                Aprovecha estas increíbles ofertas por tiempo limitado
            </p>
        </div>

        <?php if (!empty($productos_oferta)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($productos_oferta as $producto): ?>
                    <?php renderUltraProducto($producto); ?>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-12">
                <a href="./?page=ofertas"
                    class="inline-flex items-center bg-red-600 text-white font-bold py-4 px-10 rounded-full hover:bg-red-700 transition-all duration-300 shadow-lg transform hover:scale-105">
                    VER TODAS LAS OFERTAS <i class="fas fa-fire ml-3"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="text-center py-12 bg-gray-50 rounded-xl">
                <p class="text-gray-500">Pronto tendremos nuevas ofertas para ti</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Sección Carrusel Automático de Categorías -->
<section class="relative py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <!-- Encabezado -->
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">
                EXPLORA <span class="text-orange-500">CATEGORÍAS</span>
            </h2>
            <div class="w-24 h-1 bg-orange-500 mx-auto rounded-full"></div>
            <p class="text-gray-600 mt-4 max-w-2xl mx-auto">Descubre nuestras categorías destacadas</p>
        </div>

        <!-- Carrusel automático -->
        <div class="relative h-64 md:h-80 rounded-xl overflow-hidden shadow-xl">
            <div class="swiper-categorias-banner swiper-container h-full">
                <div class="swiper-wrapper">
                    <?php foreach ($categorias as $categoria): ?>
                        <div class="swiper-slide">
                            <div class="relative h-full w-full">
                                <!-- Imagen de categoría -->
                                <img src="./admin/<?= htmlspecialchars($categoria['imagen']) ?>"
                                    alt="<?= htmlspecialchars($categoria['nombre']) ?>"
                                    class="absolute inset-0 w-full h-full object-cover object-center">

                                <!-- Overlay y texto -->
                                <div class="absolute inset-0 bg-black/50 flex items-center justify-center p-6 text-center">
                                    <div class="max-w-md">
                                        <h3 class="text-white font-bold text-2xl md:text-3xl mb-3">
                                            <?= htmlspecialchars($categoria['nombre']) ?>
                                        </h3>
                                        <a href="./?page=productos&categoria=<?= (int)$categoria['id'] ?>"
                                            class="inline-block bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-full transition-all duration-300 mt-4">
                                            VER PRODUCTOS
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Paginación -->
                <div class="swiper-pagination-categorias-banner absolute bottom-4 left-0 right-0 flex justify-center gap-2 z-10"></div>

                <!-- Flechas de navegación -->
                <div class="swiper-button-prev-categorias-banner absolute left-4 top-1/2 -translate-y-1/2 z-10 bg-white/80 text-gray-800 w-10 h-10 rounded-full flex items-center justify-center shadow-md hover:bg-white transition-colors">
                    <i class="fas fa-chevron-left"></i>
                </div>
                <div class="swiper-button-next-categorias-banner absolute right-4 top-1/2 -translate-y-1/2 z-10 bg-white/80 text-gray-800 w-10 h-10 rounded-full flex items-center justify-center shadow-md hover:bg-white transition-colors">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </div>

        <!-- Botón ver todas -->
        <div class="text-center mt-12">
            <a href="./?page=categorias"
                class="inline-flex items-center bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-full transition-all duration-300 shadow-lg hover:shadow-xl">
                VER TODAS LAS CATEGORÍAS <i class="fas fa-chevron-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Incluir Swiper JS -->
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

<!-- Inicializar Swiper -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const swiper = new Swiper('.swiper-categorias-banner', {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            speed: 800,
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            pagination: {
                el: '.swiper-pagination-categorias-banner',
                clickable: true,
                renderBullet: function(index, className) {
                    return `<span class="${className} w-3 h-3 rounded-full bg-white/50 border border-white/80 transition-all duration-300 hover:bg-white"></span>`;
                },
            },
            navigation: {
                nextEl: '.swiper-button-next-categorias-banner',
                prevEl: '.swiper-button-prev-categorias-banner',
            },
        });
    });
</script>

<style>
    /* Efecto fade entre slides */
    .swiper-categorias-banner .swiper-slide {
        opacity: 0 !important;
        transition: opacity 1s ease;
    }

    .swiper-categorias-banner .swiper-slide-active,
    .swiper-categorias-banner .swiper-slide-duplicate-active {
        opacity: 1 !important;
    }

    /* Estilo para las flechas de navegación */
    .swiper-button-prev-categorias-banner,
    .swiper-button-next-categorias-banner {
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .swiper-categorias-banner:hover .swiper-button-prev-categorias-banner,
    .swiper-categorias-banner:hover .swiper-button-next-categorias-banner {
        opacity: 1;
    }
</style>


<!-- Sección Destacados -->
<section id="destacados" class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-3">
                <span class="text-ultra">PRODUCTOS</span> DESTACADOS
            </h2>
            <div class="w-24 h-1 bg-ultra mx-auto rounded-full"></div>
            <p class="text-gray-600 mt-4 max-w-2xl mx-auto">
                Los productos más vendidos y mejor valorados por nuestros clientes
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($productos_destacados as $producto): ?>
                <?php renderUltraProducto($producto); ?>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-12">
            <a href="./?page=productos"
                class="inline-flex items-center bg-ultra text-white font-bold py-4 px-10 rounded-full hover:bg-opacity-90 transition-all duration-300 shadow-lg transform hover:scale-105">
                VER TODOS LOS PRODUCTOS <i class="fas fa-arrow-right ml-3"></i>
            </a>
        </div>
    </div>
</section>


<!-- Sección Banner Comercial -->
<section class="py-16 bg-ultra text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-extrabold mb-6">
            ¿POR QUÉ ELEGIR <span class="text-yellow-300"><?= strtoupper(htmlspecialchars(TIENDA_NOMBRE)) ?></span>?
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto mt-12">
            <div class="feature-box p-6 bg-white/10 rounded-xl backdrop-blur-sm border border-white/20">
                <div class="text-4xl mb-4 text-yellow-300">
                    <i class="fas fa-truck"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Envíos Rápidos</h3>
                <p class="text-white/80">Recibe tus productos en 24-48hs en CABA y GBA</p>
            </div>

            <div class="feature-box p-6 bg-white/10 rounded-xl backdrop-blur-sm border border-white/20">
                <div class="text-4xl mb-4 text-yellow-300">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Compra Segura</h3>
                <p class="text-white/80">Pago seguro con MercadoPago y transferencia</p>
            </div>

            <div class="feature-box p-6 bg-white/10 rounded-xl backdrop-blur-sm border border-white/20">
                <div class="text-4xl mb-4 text-yellow-300">
                    <i class="fas fa-headset"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Soporte Premium</h3>
                <p class="text-white/80">Atención personalizada por WhatsApp y email</p>
            </div>
        </div>
    </div>
</section>

<!-- Sección Newsletter Ultra Comercial -->
<section class="py-16 bg-gray-900 text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto bg-gray-800 rounded-2xl overflow-hidden shadow-2xl">
            <div class="md:flex">
                <div class="md:w-1/2 bg-ultra flex items-center justify-center p-8">
                    <div class="text-center">
                        <div class="text-5xl mb-4 animate-float">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">¡No te pierdas nada!</h3>
                        <p class="opacity-90">Recibe nuestras mejores ofertas antes que nadie</p>
                    </div>
                </div>

                <div class="md:w-1/2 p-8">
                    <h3 class="text-2xl font-bold mb-4">Suscríbete a nuestro Newsletter</h3>
                    <p class="text-gray-300 mb-6">Recibe descuentos exclusivos y novedades directamente en tu email.</p>

                    <form id="form-newsletter" class="space-y-4">
                        <div>
                            <input type="email" id="email-newsletter" name="email" required placeholder="Tu email"
                                class="w-full px-4 py-3 rounded-lg bg-gray-700 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-ultra text-white">
                        </div>
                        <button type="submit"
                            class="w-full bg-ultra text-white font-bold py-3 px-6 rounded-lg hover:bg-opacity-90 transition-all duration-300 shadow-lg">
                            SUSCRIBIRME <i class="fas fa-paper-plane ml-2"></i>
                        </button>
                    </form>


                    <p class="text-xs text-gray-400 mt-4">
                        Al suscribirte aceptas recibir emails promocionales. Puedes darte de baja en cualquier momento.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal de Mega Ofertas - Versión Mejorada -->
<?php if (!empty($mega_ofertas)): ?>
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />

    <div id="megaOfertasModal" class="fixed inset-0 bg-black/90 z-[100] flex items-center justify-center p-4 hidden animate-fadeIn">
        <div class="relative w-full max-w-4xl xl:max-w-5xl bg-white rounded-2xl overflow-hidden shadow-2xl">
            <!-- Botón de cerrar mejorado -->
            <button id="closeMegaOfertas" class="absolute top-4 right-4 z-50 text-gray-600 hover:text-red-500 transition-colors bg-white/90 rounded-full w-10 h-10 flex items-center justify-center shadow-md">
                <i class="fas fa-times text-xl"></i>
            </button>

            <div class="flex flex-col md:flex-row">
                <!-- Sección de encabezado -->
                <div class="w-full md:w-2/5 bg-gradient-to-br from-red-600 to-red-500 p-6 md:p-8 text-white text-center flex flex-col justify-center">
                    <div class="mb-4">
                        <i class="fas fa-bolt text-5xl animate-pulse"></i>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-bold mb-2">OFERTAS EXCLUSIVAS</h3>
                    <p class="text-lg opacity-90 mb-6">Solo por tiempo limitado</p>

                    <!-- Contador regresivo mejorado -->
                    <div class="bg-black/20 rounded-lg p-3 inline-block mb-6">
                        <div class="flex items-center justify-center space-x-1 font-mono">
                            <span class="bg-white/20 px-2 py-1 rounded" id="countdown-hours">05</span>:
                            <span class="bg-white/20 px-2 py-1 rounded" id="countdown-minutes">00</span>:
                            <span class="bg-white/20 px-2 py-1 rounded" id="countdown-seconds">00</span>
                        </div>
                        <div class="text-xs mt-1 opacity-80">Termina en</div>
                    </div>

                    <!-- Indicadores de paginación -->
                    <div class="flex justify-center space-x-2">
                        <?php foreach ($mega_ofertas as $index => $oferta): ?>
                            <button class="swiper-pagination-bullet w-2.5 h-2.5 rounded-full bg-white/30 transition-all duration-300 <?= $index === 0 ? '!bg-white !w-4' : '' ?>"
                                data-index="<?= $index ?>"></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Carrusel de productos -->
                <div class="w-full md:w-3/5 p-6">
                    <div class="swiper h-full">
                        <div class="swiper-wrapper">
                            <?php foreach ($mega_ofertas as $oferta): ?>
                                <div class="swiper-slide">
                                    <div class="flex flex-col h-full">
                                        <!-- Imagen del producto -->
                                        <div class="relative h-48 md:h-56 bg-gray-50 rounded-xl overflow-hidden mb-4 flex items-center justify-center p-4">
                                            <img src="./admin/controllers/uploads/descuentos/<?= htmlspecialchars($oferta['imagen_descuento']) ?>"
                                                alt="<?= htmlspecialchars($oferta['nombre']) ?>"
                                                class="max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-105">
                                            <div class="absolute top-3 left-3 bg-red-600 text-white text-sm font-bold px-2.5 py-1 rounded-full shadow-md">
                                                -<?= $oferta['porcentaje_descuento'] ?>%
                                            </div>
                                        </div>

                                        <!-- Detalles del producto -->
                                        <div class="flex-grow">
                                            <h4 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">
                                                <?= htmlspecialchars($oferta['nombre']) ?>
                                            </h4>

                                            <div class="flex items-baseline gap-3 mb-3">
                                                <span class="text-2xl font-bold text-red-600">
                                                    $<?= number_format($oferta['precio'] * (1 - $oferta['porcentaje_descuento'] / 100), 2, ',', '.') ?>
                                                </span>
                                                <span class="text-lg text-gray-400 line-through">
                                                    $<?= number_format($oferta['precio'], 2, ',', '.') ?>
                                                </span>
                                            </div>

                                            <!-- Ahorro destacado -->
                                            <div class="bg-yellow-50 border border-yellow-100 rounded-lg p-3 mb-4">
                                                <div class="flex items-start">
                                                    <div class="text-yellow-500 mr-2 mt-0.5">
                                                        <i class="fas fa-bolt"></i>
                                                    </div>
                                                    <div class="text-sm">
                                                        <span class="block font-bold text-yellow-800">¡AHORRÁ $<?= number_format($oferta['precio'] * ($oferta['porcentaje_descuento'] / 100), 2, ',', '.') ?>!</span>
                                                        <span class="text-yellow-600">Oferta por tiempo limitado</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Botón de compra -->
                                        <div class="mt-2">
                                            <a href="./?page=producto&id=<?= (int)$oferta['id'] ?>"
                                                class="block w-full bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white font-semibold py-3 px-4 rounded-lg text-center transition-all shadow-md hover:shadow-lg transform hover:scale-[1.02]">
                                                <i class="fas fa-cart-plus mr-2"></i> COMPRAR AHORA
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Flechas de navegación -->
                        <div class="swiper-button-next !hidden md:!flex !w-10 !h-10 !bg-white !rounded-full !shadow-md after:!text-sm after:!font-bold"></div>
                        <div class="swiper-button-prev !hidden md:!flex !w-10 !h-10 !bg-white !rounded-full !shadow-md after:!text-sm after:!font-bold"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
    <script>
        // Inicializar Swiper con mejores parámetros
        const offerSwiper = new Swiper('.swiper', {
            loop: true,
            speed: 600,
            grabCursor: true,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true
            },
            effect: 'slide',
            on: {
                slideChange: function() {
                    // Actualizar bullets activos
                    document.querySelectorAll('.swiper-pagination-bullet').forEach((bullet, index) => {
                        const realIndex = this.realIndex % <?= count($mega_ofertas) ?>;
                        if (index === realIndex) {
                            bullet.classList.add('!bg-white', '!w-4');
                            bullet.classList.remove('!bg-white/30');
                        } else {
                            bullet.classList.remove('!bg-white', '!w-4');
                            bullet.classList.add('!bg-white/30');
                        }
                    });
                }
            }
        });

        // Hacer los bullets clickeables
        document.querySelectorAll('.swiper-pagination-bullet').forEach(bullet => {
            bullet.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                offerSwiper.slideTo(index);
            });
        });

        // Contador regresivo mejorado
        function startCountdown() {
            let hours = 5;
            let minutes = 0;
            let seconds = 0;

            const hoursElement = document.getElementById('countdown-hours');
            const minutesElement = document.getElementById('countdown-minutes');
            const secondsElement = document.getElementById('countdown-seconds');

            const timer = setInterval(() => {
                if (seconds === 0) {
                    if (minutes === 0) {
                        if (hours === 0) {
                            clearInterval(timer);
                            return;
                        }
                        hours--;
                        minutes = 59;
                    } else {
                        minutes--;
                    }
                    seconds = 59;
                } else {
                    seconds--;
                }

                hoursElement.textContent = hours.toString().padStart(2, '0');
                minutesElement.textContent = minutes.toString().padStart(2, '0');
                secondsElement.textContent = seconds.toString().padStart(2, '0');
            }, 1000);
        }

        // Mostrar modal
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('megaOfertasModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
                startCountdown();
            }, 3000);

            // Cerrar modal
            document.getElementById('closeMegaOfertas').addEventListener('click', function() {
                document.getElementById('megaOfertasModal').style.display = 'none';
                document.body.style.overflow = '';
            });
        });
    </script>

    <style>
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .swiper {
            height: 100%;
            padding: 0 10px 40px 10px;
        }

        .swiper-slide {
            height: auto;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .swiper-slide-active {
            opacity: 1;
        }

        .swiper-button-next:after,
        .swiper-button-prev:after {
            font-size: 14px;
            font-weight: 900;
            color: #ef4444;
        }

        .swiper-pagination-bullet {
            cursor: pointer;
            transition: all 0.3s ease;
        }
    </style>
<?php endif; ?>

<!-- Scripts Ultra Comerciales -->
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script>
    // Carrusel de Mega Ofertas
    <?php if (!empty($mega_ofertas)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar modal después de 3 segundos
            setTimeout(function() {
                document.getElementById('megaOfertasModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';

                // Inicializar Swiper
                new Swiper('.swiper-container', {
                    loop: true,
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false,
                    },
                });
            }, 3000);

            // Cerrar modal
            document.getElementById('closeMegaOfertas').addEventListener('click', function() {
                document.getElementById('megaOfertasModal').style.display = 'none';
                document.body.style.overflow = '';
            });
        });
    <?php endif; ?>

    // Animación para botones "Añadir al carrito"
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-id');

            // Animación
            this.innerHTML = '<i class="fas fa-check"></i>';
            this.classList.remove('bg-red-600', 'bg-gray-600');
            this.classList.add('bg-green-500');

            // Aquí iría la lógica AJAX para añadir al carrito
            console.log('Producto añadido al carrito:', productId);

            // Restaurar después de 2 segundos
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-shopping-cart"></i>';
                this.classList.add('bg-red-600');
                this.classList.remove('bg-green-500');
            }, 2000);
        });
    });

    // Smooth scrolling para anclas
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });



document.getElementById('form-newsletter').addEventListener('submit', function(e) {
    e.preventDefault();

    const email = document.getElementById('email-newsletter').value.trim();

    if (email === '') {
        Swal.fire('Oops', 'Por favor ingresá un email.', 'warning');
        return;
    }

    fetch('suscribirse.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire('¡Listo!', data.message, 'success');
            document.getElementById('form-newsletter').reset();
        } else {
            Swal.fire('Atención', data.message, 'info');
        }
    })
    .catch(() => {
        Swal.fire('Error', 'Ocurrió un error. Intentá más tarde.', 'error');
    });
});

    
</script>
</body>

</html>