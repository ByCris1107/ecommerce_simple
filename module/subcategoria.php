<?php


$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;

// Obtener información de la categoría padre
$sql_categoria = "SELECT nombre FROM categorias WHERE id = $categoria_id";
$result_categoria = $conexion->query($sql_categoria);
$categoria_padre = $result_categoria->fetch_assoc();

// Obtener subcategorías
$sql_sub = "SELECT id, nombre, imagen FROM subcategorias WHERE categoria_id = $categoria_id ORDER BY nombre ASC";
$resultado_sub = $conexion->query($sql_sub);
$subcategorias = [];

if ($resultado_sub && $resultado_sub->num_rows > 0) {
    while ($fila = $resultado_sub->fetch_assoc()) {
        $subcategorias[] = $fila;
    }
}
?>

<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-3">
                SUBCATEGORÍAS DE <span class="text-red-600"><?= strtoupper($categoria_padre['nombre']) ?></span>
            </h2>
            <div class="w-24 h-1 bg-red-600 mx-auto rounded-full"></div>
            <p class="text-gray-600 mt-4 max-w-2xl mx-auto">
                Selecciona una subcategoría para ver los productos disponibles.
            </p>
            <a href="./?page=productos" class="inline-block bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-full transition-all duration-300 mt-4">
                ← Volver a categorías
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            <?php if (empty($subcategorias)): ?>
                <div class="col-span-full text-center text-gray-500">No hay subcategorías disponibles.</div>
            <?php else: ?>
                <?php foreach ($subcategorias as $subcat): ?>
                    <a href="./?page=productos&subcategoria=<?= $subcat['id'] ?>" class="block group bg-white rounded-xl shadow-md hover:shadow-xl transform transition hover:scale-105 overflow-hidden">
                        <?php if (!empty($subcat['imagen'])): ?>
                            <img src="./admin/<?= htmlspecialchars($subcat['imagen']) ?>" alt="<?= htmlspecialchars($subcat['nombre']) ?>" class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                <span class="text-gray-500">Sin imagen</span>
                            </div>
                        <?php endif; ?>
                        <div class="p-4 text-center">
                            <h3 class="text-lg font-semibold text-gray-800 group-hover:text-red-600">
                                <?= htmlspecialchars($subcat['nombre']) ?>
                            </h3>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>