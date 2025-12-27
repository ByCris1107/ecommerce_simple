<?php
// Formato de precio
function format_price_ar($precio) {
    return '$' . number_format($precio, 2, ',', '.');
}

if (!isset($_GET['producto']) || empty($_GET['producto'])) {
    echo "Producto no encontrado.";
    exit;
}

$id_producto = intval($_GET['producto']);

// Consulta principal del producto con descuentos
$sql = "
SELECT 
    p.*, 
    c.nombre AS nombre_categoria,
    s.nombre AS nombre_subcategoria,
    d.porcentaje_descuento AS descuento_porcentaje,
    d.imagen_descuento
FROM productos p
JOIN categorias c ON p.categoria_id = c.id
JOIN subcategorias s ON p.subcategoria_id = s.id
LEFT JOIN descuentos_productos d ON p.id = d.producto_id
WHERE p.id = ?";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_producto);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$producto = mysqli_fetch_assoc($resultado);

if (!$producto) {
    die('Producto no encontrado.');
}

// Verificar si tiene descuento activo
$tiene_descuento = !empty($producto['descuento_porcentaje']);
$precio_final = $tiene_descuento ? 
    $producto['precio'] * (1 - $producto['descuento_porcentaje'] / 100) : 
    $producto['precio'];

// Variantes del producto (usando la tabla variantes_producto)
$consulta_variantes = "SELECT color, talle, foto_color FROM variantes_producto WHERE producto_id = ?";
$stmt = mysqli_prepare($conexion, $consulta_variantes);
mysqli_stmt_bind_param($stmt, 'i', $id_producto);
mysqli_stmt_execute($stmt);
$resultado_variantes = mysqli_stmt_get_result($stmt);
$variantes = mysqli_fetch_all($resultado_variantes, MYSQLI_ASSOC);

// Colores y talles únicos
$colores_disponibles = array_unique(array_column($variantes, 'color'));
$talles_disponibles = array_unique(array_column($variantes, 'talle'));

// Selección del usuario
$colorSeleccionado = $_GET['color'] ?? '';
$talleSeleccionado = $_GET['talle'] ?? '';

// Obtener imagen del color seleccionado (si existe)
$imagenColorSeleccionado = '';
if ($colorSeleccionado) {
    foreach ($variantes as $variante) {
        if ($variante['color'] === $colorSeleccionado) {
            $imagenColorSeleccionado = $variante['foto_color'];
            break;
        }
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow-lg flex flex-col md:flex-row">
        <div class="md:w-1/2 flex flex-col items-center">
<div class="relative w-full h-96 mb-4 overflow-hidden rounded-lg bg-gray-100">
    <!-- Imagen del producto -->
    <img id="product-image"
        src="./admin/controllers/<?= htmlspecialchars($imagenColorSeleccionado ?: $producto['imagen_portada']); ?>"
        class="absolute inset-0 w-full h-full object-contain transition-opacity duration-300 cursor-pointer hover:opacity-90"
        alt="<?= htmlspecialchars($producto['nombre']); ?>"
        onclick="openLightbox(this.src, this.alt)">
    
    <!-- Etiqueta de descuento -->
    <?php if ($tiene_descuento && !empty($producto['imagen_descuento'])): ?>
    <div class="absolute top-2 left-2">
        <img src="./admin/controllers/<?= htmlspecialchars($producto['imagen_descuento']); ?>" 
             alt="Descuento" class="h-16 w-16">
    </div>
    <?php elseif ($tiene_descuento): ?>
    <div class="absolute top-2 left-2 bg-red-600 text-white text-sm font-bold px-3 py-1 rounded-full shadow-lg">
        -<?= $producto['descuento_porcentaje'] ?>% OFF
    </div>
    <?php endif; ?>
</div>

<!-- Lightbox compacto -->
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center hidden p-4">
    <div class="relative flex flex-col items-center">
        <!-- Contenedor de imagen compacto -->
        <div class="relative w-[600px] h-[450px] bg-black bg-opacity-30 flex items-center justify-center mb-3 rounded-lg border border-gray-700 overflow-hidden">
            <img id="lightbox-image" 
                 class="max-w-[580px] max-h-[430px] object-contain" 
                 src="" 
                 alt="">
                 
            <!-- Botón de cierre compacto -->
            <button onclick="closeLightbox()" 
                    class="absolute top-2 right-2 text-white text-xl z-50 hover:text-gray-300 transition-colors p-1 bg-black bg-opacity-70 rounded-full w-7 h-7 flex items-center justify-center">
                &times;
            </button>
        </div>
        
        <!-- Descripción compacta -->
        <div class="w-[600px] p-2 bg-black bg-opacity-50 rounded-lg">
            <p id="lightbox-caption" class="text-white text-center text-sm"></p>
        </div>
    </div>
</div>

<script>
    // Función para abrir el lightbox
    function openLightbox(src, alt) {
        const lightbox = document.getElementById('lightbox');
        const lightboxImage = document.getElementById('lightbox-image');
        const lightboxCaption = document.getElementById('lightbox-caption');
        
        // Configuración de tamaño fijo
        lightboxImage.style.maxWidth = '580px';
        lightboxImage.style.maxHeight = '430px';
        lightboxImage.style.width = '';
        lightboxImage.style.height = '';
        lightboxImage.style.objectFit = 'contain';
        
        // Cargar contenido
        lightboxImage.src = src;
        lightboxCaption.textContent = alt;
        lightbox.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    // Función para cerrar el lightbox
    function closeLightbox() {
        document.getElementById('lightbox').classList.add('hidden');
        document.body.style.overflow = '';
    }
    
    // Cerrar al hacer clic fuera o con ESC
    document.getElementById('lightbox').addEventListener('click', function(e) {
        if (e.target === this) closeLightbox();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLightbox();
    });
</script>

<style>
    /* Transiciones suaves */
    #lightbox {
        transition: opacity 0.2s ease;
        backdrop-filter: blur(5px);
    }
    
    #lightbox:not(.hidden) {
        display: flex !important;
        opacity: 1;
    }
    
    #lightbox.hidden {
        opacity: 0;
        pointer-events: none;
    }
    
    /* Efecto hover para el botón de cierre */
    #lightbox button:hover {
        transform: scale(1.1);
        background-color: rgba(255,255,255,0.3);
    }
    
    /* Efecto de carga para la imagen */
    #lightbox-image:not([src]) {
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="%23ffffff50" stroke-width="8"><animateTransform attributeName="transform" type="rotate" from="0 50 50" to="360 50 50" dur="1s" repeatCount="indefinite"/></circle></svg>') no-repeat center;
        background-size: 40px;
        min-width: 580px;
        min-height: 430px;
    }
</style>
            
            <?php if (!empty($colores_disponibles)): ?>
            <div class="flex gap-2 mb-2">
                <p class="text-sm text-gray-600">Color seleccionado: <span id="color-selected-text" class="font-semibold"><?= $colorSeleccionado ? ucfirst(htmlspecialchars($colorSeleccionado)) : 'Ninguno'; ?></span></p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="md:w-1/2 md:pl-6 flex flex-col justify-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-3"><?= htmlspecialchars($producto['nombre']); ?></h1>
            <p class="text-gray-600 mb-4"><?= nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
            
            <div class="flex gap-4 text-sm mb-4">
                <p class="text-gray-500">Categoría: <span class="font-semibold text-gray-700"><?= htmlspecialchars($producto['nombre_categoria']); ?></span></p>
                <p class="text-gray-500">Género: <span class="font-semibold text-gray-700"><?= ucfirst(htmlspecialchars($producto['genero'])); ?></span></p>
            </div>

            <form action="./" method="get" id="form-agregar-carrito" class="mt-4">
                <input type="hidden" name="page" value="carrito">
                <input type="hidden" name="add" value="<?= $producto['id']; ?>">
                <input type="hidden" name="producto" value="<?= $id_producto; ?>">
                <input type="hidden" name="imagen" value="<?= htmlspecialchars($producto['imagen_portada']); ?>">
                <input type="hidden" name="descuento" value="<?= $tiene_descuento ? $producto['descuento_porcentaje'] : 0; ?>">

                <?php if (!empty($colores_disponibles)): ?>
                <div id="colors-container" class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Color</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($colores_disponibles as $color): 
                            $isSelected = ($colorSeleccionado === $color);
                        ?>
                        <div class="relative">
                            <input type="radio" id="color-<?= htmlspecialchars($color); ?>" 
                                   name="color" value="<?= htmlspecialchars($color); ?>" 
                                   class="hidden peer" 
                                   <?= $isSelected ? 'checked' : ''; ?>>
                            <label for="color-<?= htmlspecialchars($color); ?>" 
                                   class="inline-flex items-center px-4 py-2 border-2 rounded-md cursor-pointer transition-all duration-200
                                   <?= $isSelected ? 'border-dashed border-blue-500 bg-blue-50 text-blue-700 font-semibold' : 'border-dashed border-gray-300 text-gray-700 hover:border-gray-400' ?>">
                                <?= htmlspecialchars(ucfirst($color)); ?>
                                <?php if ($isSelected): ?>
                                <span class="ml-2 text-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <?php endif; ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($talles_disponibles)): ?>
                <div id="sizes-container" class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-lg font-semibold text-gray-800">Talle</h3>
                        <p class="text-sm text-gray-500 hover:text-blue-500 cursor-pointer">Guía de talles</p>
                    </div>
                    
                    <div class="grid grid-cols-5 gap-2" id="size-options">
                        <?php
                        $tallesParaColor = $colorSeleccionado ? 
                            array_unique(array_column(array_filter($variantes, fn($v) => $v['color'] === $colorSeleccionado), 'talle')) : 
                            $talles_disponibles;

                        if (!empty($tallesParaColor)):
                            foreach ($tallesParaColor as $talle): 
                                $isSelected = ($talleSeleccionado === $talle);
                        ?>
                        <div class="relative">
                            <input type="radio" id="size-<?= htmlspecialchars($talle); ?>" 
                                   name="talle" value="<?= htmlspecialchars($talle); ?>" 
                                   class="hidden peer" 
                                   <?= $isSelected ? 'checked' : ''; ?>
                                   <?= empty($colorSeleccionado) ? 'disabled' : ''; ?>>
                            <label for="size-<?= htmlspecialchars($talle); ?>" 
                                   class="block w-full py-2 text-center border-2 rounded-md cursor-pointer transition-all duration-200 relative
                                   <?= $isSelected ? 'border-dashed border-blue-500 bg-blue-50 text-blue-700 font-semibold' : 'border-dashed border-gray-300 text-gray-700 hover:border-gray-400' ?>
                                   <?= empty($colorSeleccionado) ? 'opacity-50 cursor-not-allowed' : ''; ?>">
                                <?= htmlspecialchars($talle); ?>
                                <?php if ($isSelected): ?>
                                <span class="absolute -top-2 -right-2 bg-blue-500 text-white rounded-full p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <?php endif; ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <p class="text-gray-500 col-span-5">Selecciona un color para ver los talles disponibles</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="mb-4 border-t pt-4">
                    <div class="flex items-center gap-4 mb-4">
                        <?php if ($tiene_descuento): ?>
                            <div>
                                <p class="text-sm text-gray-500 line-through"><?= format_price_ar($producto['precio']); ?></p>
                                <p class="text-3xl font-bold text-green-600"><?= format_price_ar($precio_final); ?></p>
                            </div>
                            <div class="flex flex-col">
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                    <?= $producto['descuento_porcentaje']; ?>% OFF
                                </span>
                            </div>
                        <?php else: ?>
                            <p class="text-3xl font-bold text-gray-900"><?= format_price_ar($producto['precio']); ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($colores_disponibles) && !empty($talles_disponibles)): ?>
                        <button type="submit" id="btn-agregar" 
                                class="mt-2 inline-flex items-center justify-center bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-all text-lg font-semibold w-full disabled:bg-gray-400 disabled:cursor-not-allowed" 
                                <?= (empty($colorSeleccionado) || empty($talleSeleccionado)) ? 'disabled' : ''; ?>>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                            </svg>
                            <span id="btn-text">
                                <?= (empty($colorSeleccionado) || empty($talleSeleccionado)) ? 'Selecciona color y talle' : 'Agregar al carrito'; ?>
                            </span>
                        </button>
                    <?php else: ?>
                        <p class="text-gray-500 mt-2">Este producto no tiene variantes disponibles.</p>
                    <?php endif; ?>
                </div>
            </form>
            
            <?php if ($tiene_descuento): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            ¡Oferta especial! Ahorrás <?= format_price_ar($producto['precio'] - $precio_final); ?> (<?= $producto['descuento_porcentaje']; ?>% de descuento)
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const variantes = <?= json_encode($variantes); ?>;
    const imagenProducto = document.getElementById('product-image');
    const btnAgregar = document.getElementById('btn-agregar');
    const btnText = document.getElementById('btn-text');
    const sizeOptions = document.getElementById('size-options');
    const colorSelectedText = document.getElementById('color-selected-text');

    function actualizarImagen(color) {
        if (!color) return;
        
        const variante = variantes.find(v => v.color === color);
        if (variante && variante.foto_color) {
            // Efecto de transición
            imagenProducto.style.opacity = '0';
            setTimeout(() => {
                imagenProducto.src = './admin/controllers/' + variante.foto_color;
                imagenProducto.style.opacity = '1';
            }, 300);
        }
        
        // Actualizar texto del color seleccionado
        colorSelectedText.textContent = color.charAt(0).toUpperCase() + color.slice(1);
    }

    function actualizarEstadoBoton() {
        const color = document.querySelector('input[name="color"]:checked');
        const talle = document.querySelector('input[name="talle"]:checked');
        
        if (color && talle) {
            btnAgregar.disabled = false;
            btnText.textContent = "Agregar al carrito";
        } else {
            btnAgregar.disabled = true;
            if (!color) {
                btnText.textContent = "Selecciona un color";
            } else {
                btnText.textContent = "Selecciona un talle";
            }
        }
    }

    function filtrarTallesPorColor() {
        const colorSeleccionado = document.querySelector('input[name="color"]:checked')?.value;
        const tallesDisponibles = colorSeleccionado
            ? [...new Set(variantes.filter(v => v.color === colorSeleccionado).map(v => v.talle))]
            : <?= json_encode($talles_disponibles); ?>;
        
        // Actualizar todos los inputs de talles
        document.querySelectorAll('input[name="talle"]').forEach(input => {
            const talle = input.value;
            const label = document.querySelector(`label[for="size-${talle}"]`);
            
            if (tallesDisponibles.includes(talle)) {
                input.disabled = false;
                if (label) {
                    label.classList.remove('opacity-50', 'cursor-not-allowed');
                    label.classList.add('hover:border-blue-400');
                }
            } else {
                input.disabled = true;
                if (label) {
                    label.classList.add('opacity-50', 'cursor-not-allowed');
                    label.classList.remove('hover:border-blue-400');
                }
            }
        });
        
        actualizarEstadoBoton();
    }

    // Event listeners
    document.querySelectorAll('input[name="color"]').forEach(input => {
        input.addEventListener('change', () => {
            actualizarImagen(input.value);
            filtrarTallesPorColor();
            
            // Resetear selección de talle al cambiar color
            const talleSeleccionado = document.querySelector('input[name="talle"]:checked');
            if (talleSeleccionado) {
                talleSeleccionado.checked = false;
            }
            actualizarEstadoBoton();
        });
    });

    document.querySelectorAll('input[name="talle"]').forEach(input => {
        input.addEventListener('change', actualizarEstadoBoton);
    });

    // Inicialización
    document.addEventListener('DOMContentLoaded', () => {
        <?php if ($colorSeleccionado): ?>
            actualizarImagen('<?= $colorSeleccionado; ?>');
        <?php endif; ?>
        filtrarTallesPorColor();
        actualizarEstadoBoton();
    });
</script>