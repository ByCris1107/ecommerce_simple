<?php
$order_query = "
SELECT 
    pedidos.id AS order_id,
    pedidos.fecha_creacion AS order_date,
    pedidos.total AS total_amount,
    pedidos.estado AS status,
    usuarios.nombre_completo AS usuario_nombre,
    usuarios.email AS usuario_email,
    pedidos.dni AS dni_cliente,
    pedidos_productos.cantidad AS quantity,
    pedidos_productos.precio_unitario AS unit_price,
    pedidos_productos.talle AS size,
    pedidos_productos.color AS color,
    variantes_producto.foto_color AS color_image,
    pedidos_productos.nombre_producto AS product_name,
    pedidos.direccion AS direccion,
    pedidos.entre_calles AS entre_calles,
    pedidos.provincia AS provincia,
    pedidos.localidad AS ciudad,
    pedidos.codigo_postal AS codigo_postal,
    pedidos.departamento AS departamento,
    pedidos.departamento AS piso,
    pedidos.telefono_cliente AS telefono,
    pedidos.referencias AS observaciones
FROM pedidos
JOIN usuarios ON pedidos.email_cliente = usuarios.email
JOIN pedidos_productos ON pedidos.id = pedidos_productos.pedido_id
LEFT JOIN variantes_producto ON pedidos_productos.variante_id = variantes_producto.id
ORDER BY pedidos.id DESC, pedidos_productos.nombre_producto ASC
";

$order_result = $conexion->query($order_query);

if ($order_result && $order_result->num_rows > 0) {
    // Almacenamos todos los resultados en un array para poder navegar fácilmente
    $orders = [];
    while ($row = $order_result->fetch_assoc()) {
        $orders[] = $row;
    }
?>

    <div class="max-w-7xl mx-auto mt-10 p-6 bg-white shadow-lg rounded-lg">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 text-center">Todas las Órdenes</h2>

        <table class="min-w-full bg-white border border-gray-300 rounded">
            <thead>
                <tr class="bg-gray-100">
                    <th class="py-2 px-4 border-b">Cliente</th>
                    <th class="py-2 px-4 border-b">DNI</th>
                    <th class="py-2 px-4 border-b">Fecha</th>
                    <th class="py-2 px-4 border-b">Estado</th>
                    <th class="py-2 px-4 border-b">Total</th>
                    <th class="py-2 px-4 border-b">Productos</th>
                    <th class="py-2 px-4 border-b">Dirección</th>
                    <th class="py-2 px-4 border-b">Contacto</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $current_order_id = null;
                $total_orders = count($orders);

                for ($i = 0; $i < $total_orders; $i++) {
                    $row = $orders[$i];

                    if ($current_order_id !== $row['order_id']) {
                        // Cerrar la fila anterior si existe
                        if ($current_order_id !== null) {
                            echo '</td></tr>';
                        }
                        // Nueva fila para la orden
                        $current_order_id = $row['order_id'];
                ?>
                        <tr>
                            <td class="py-2 px-4 border-b align-top">
                                <div class="flex flex-col">
                                    <span><?php echo ucfirst(htmlspecialchars($row['usuario_nombre'])); ?></span>
                                    <span class="text-xs text-gray-500"><?php echo htmlspecialchars($row['usuario_email']); ?></span>
                                </div>
                            </td>
                            <td class="py-2 px-4 border-b align-top">
                                <?php
                                if (!empty($row['dni_cliente'])) {
                                    echo htmlspecialchars($row['dni_cliente']);
                                } else {
                                    echo '<span class="text-gray-400 text-xs">No registrado</span>';
                                }
                                ?>
                            </td>
                            <td class="py-2 px-4 border-b align-top"><?php echo date('d/m/Y H:i', strtotime($row['order_date'])); ?></td>
                            <td class="py-2 px-4 border-b align-top">
                            <select
    data-order-id="<?= $row['order_id']; ?>"
    class="status-dropdown px-2 py-1 rounded-full text-xs appearance-none cursor-pointer 
    <?=
        $row['status'] === 'Pendiente' ? 'bg-yellow-100 text-yellow-800' : 
        ($row['status'] === 'En Camino' ? 'bg-blue-100 text-blue-800' : 
        'bg-green-100 text-green-800')
    ?>">
    <?php
    $statuses = ['Pendiente', 'En Camino', 'Entregado']; // Eliminado 'En camino'
    foreach ($statuses as $status) {
        echo '<option value="' . $status . '"' . ($row['status'] === $status ? ' selected' : '') . '>' . $status . '</option>';
    }
    ?>
</select>
                            </td>

                            <td class="py-2 px-4 border-b align-top">$<?php echo number_format($row['total_amount'], 2, ',', '.'); ?></td>
                            <td class="py-2 px-4 border-b">
                                <div class="space-y-2">
                                <?php } ?>
                                <!-- Producto dentro de la misma orden -->
                                <div class="flex items-start gap-3 pb-2 border-b border-gray-100 last:border-0">
                                    <?php if (!empty($row['color_image'])): ?>
                                        <img src="./controllers/<?php echo htmlspecialchars($row['color_image']); ?>" class="w-12 h-12 object-cover rounded" alt="Producto">
                                    <?php endif; ?>
                                    <div class="flex-1">
                                        <div class="font-medium"><?php echo htmlspecialchars($row['product_name']); ?></div>
                                        <div class="text-sm text-gray-600">
                                            <span>Cantidad: <?php echo $row['quantity']; ?></span>
                                            <span class="mx-2">|</span>
                                            <span>Precio: $<?php echo number_format($row['unit_price'], 2, ',', '.'); ?></span>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?php if (!empty($row['size']) || !empty($row['color'])): ?>
                                                <span>Talle: <?php echo htmlspecialchars($row['size']); ?></span>
                                                <span class="mx-1">|</span>
                                                <span>Color: <?php echo ucfirst(htmlspecialchars($row['color'])); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                // Si es el último registro o el siguiente es de otra orden, cerramos la fila
                                if ($i === $total_orders - 1 || $orders[$i + 1]['order_id'] !== $current_order_id) {
                                ?>
                                </div>
                            </td>
                            <td class="py-2 px-4 border-b align-top">
                                <div class="text-xs">
                                    <div><?php echo htmlspecialchars($row['direccion']); ?></div>
                                    <?php if (!empty($row['entre_calles'])): ?>
                                        <div class="text-gray-500">Entre: <?php echo htmlspecialchars($row['entre_calles']); ?></div>
                                    <?php endif; ?>
                                    <div><?php echo htmlspecialchars($row['ciudad']) . ', ' . htmlspecialchars($row['provincia']); ?></div>
                                    <div>CP: <?php echo htmlspecialchars($row['codigo_postal']); ?></div>
                                    <?php if (!empty($row['departamento'])): ?>
                                        <div><?php echo htmlspecialchars($row['departamento']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="py-2 px-4 border-b align-top">
                                <div class="text-xs">
                                    <div>Tel: <?php echo htmlspecialchars($row['telefono']); ?></div>
                                    <?php if (!empty($row['observaciones'])): ?>
                                        <div class="mt-1 p-1 bg-gray-50 rounded">
                                            <span class="font-semibold">Notas:</span>
                                            <?php echo htmlspecialchars($row['observaciones']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                <?php
                                }
                            }
                ?>
            </tbody>
        </table>
    </div>

<?php
} else {
    echo '<div class="text-center text-red-600 mt-10">No se encontraron órdenes.</div>';
}
?>

<script>
    document.querySelectorAll('.status-dropdown').forEach(select => {
        select.addEventListener('change', async function() {
            const orderId = this.dataset.orderId;
            const newStatus = this.value;

            const res = await fetch('./controllers/actualizar_estado.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `order_id=${orderId}&status=${encodeURIComponent(newStatus)}`
            });

            if (res.ok) {
                // Actualizamos la clase del <select> según el nuevo estado
                this.classList.remove('bg-yellow-100', 'text-yellow-800', 'bg-blue-100', 'text-blue-800', 'bg-purple-100', 'text-purple-800', 'bg-green-100', 'text-green-800');

                if (newStatus === 'Pendiente') {
                    this.classList.add('bg-blue-100', 'text-blue-800');
                } else if (newStatus === 'En Camino') {
                    this.classList.add('bg-purple-100', 'text-purple-800');
                } else if (newStatus === 'Entregado') {
                    this.classList.add('bg-green-100', 'text-green-800');
                }
            } else {
                alert('Ocurrió un error al actualizar el estado 😓');
            }
        });
    });
</script>