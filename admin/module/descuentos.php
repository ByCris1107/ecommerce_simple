<?php

// Obtener los descuentos de productos con género
$sql_productos = "SELECT dp.id, p.nombre AS nombre_producto, dp.porcentaje_descuento, dp.imagen_descuento, p.genero
                  FROM descuentos_productos dp
                  INNER JOIN productos p ON dp.producto_id = p.id";
$resultado_productos = $conexion->query($sql_productos);

?>

<div class="container mx-auto mt-8 px-4">
    <!-- Descuentos por Producto -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Descuentos</h3>
        <table class="min-w-full table-auto border-collapse border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Producto</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Género</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Porcentaje</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Imagen</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-sm font-medium text-gray-700">
                <?php if ($resultado_productos->num_rows > 0): ?>
                    <?php while ($row = $resultado_productos->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="px-6 py-3"><?php echo htmlspecialchars($row['nombre_producto']); ?></td>
                            <td class="px-6 py-3"><?php echo htmlspecialchars($row['genero']); ?></td>
                            <td class="px-6 py-3"><?php echo $row['porcentaje_descuento']; ?>%</td>
                            <td class="px-6 py-3">
                                <?php if (!empty($row['imagen_descuento'])): ?>
                                    <img src="./controllers/uploads/descuentos/<?php echo $row['imagen_descuento']; ?>" alt="Imagen descuento" class="w-16 h-16 object-cover rounded-lg">
                                <?php else: ?>
                                    <span class="text-gray-500">Sin imagen</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3 space-y-2">
                                <button onclick="editarDescuento(<?php echo $row['id']; ?>, 'producto')">Modificar</button>
                                <button onclick="eliminarDescuento(<?php echo $row['id']; ?>, '<?php echo $row['imagen_descuento']; ?>', 'producto')" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Eliminar</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-3 text-center text-gray-500">No hay descuentos por producto</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>


<script>
function editarDescuento(id, tipo) {
    window.location.href = './?module=editar_descuento&id=' + id + '&tipo=' + tipo;
}

function eliminarDescuento(id, imagen, tipo) {
    if (confirm('¿Estás seguro que querés eliminar este descuento?')) {
        window.location.href = './controllers/eliminar_descuento.php?id=' + id + '&imagen=' + imagen + '&tipo=' + tipo;
    }
}
</script>
