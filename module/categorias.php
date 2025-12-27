<?php

// Obtener todas las categorías
$sql = "SELECT id, nombre, imagen FROM categorias ORDER BY nombre ASC";
$resultado = $conexion->query($sql);
$categorias = [];

if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $categorias[] = $fila;
    }
}
?>

<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-3">
                TODAS LAS <span class="text-red-600">CATEGORÍAS</span>
            </h2>
            <div class="w-24 h-1 bg-red-600 mx-auto rounded-full"></div>
            <p class="text-gray-600 mt-4 max-w-2xl mx-auto">
                Descubrí todas nuestras categorías disponibles para encontrar exactamente lo que buscás.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
            <?php if (empty($categorias)): ?>
                <div class="col-span-full text-center text-gray-500">No hay categorías disponibles.</div>
            <?php else: ?>
                <?php foreach ($categorias as $categoria): ?>
<a href="./?page=subcategoria&categoria=<?php echo $categoria['id']; ?>" class="block group bg-white rounded-xl shadow-md hover:shadow-xl transform transition hover:scale-105 overflow-hidden">
    <img src="./admin/<?php echo htmlspecialchars($categoria['imagen']); ?>" alt="<?php echo htmlspecialchars($categoria['nombre']); ?>" class="w-full h-48 object-cover">
    <div class="p-4 text-center">
        <h3 class="text-lg font-semibold text-gray-800 group-hover:text-red-600">
            <?php echo htmlspecialchars($categoria['nombre']); ?>
        </h3>
    </div>
</a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
