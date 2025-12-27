<?php
// Verificar que el usuario esté logueado
if (!isset($_SESSION['id'])) {
    header('Location: ./?page=iniciar_sesion');
    exit();
}

$user_id = $_SESSION['id'];

// Consulta SQL adaptada a la nueva estructura de la base de datos
$order_query = "
SELECT 
    p.id AS order_id,
    p.fecha_creacion AS order_date,
    p.total,
    p.estado AS status,
    u.nombre_completo AS usuario_nombre,
    u.email AS usuario_email,
    pp.cantidad AS quantity,
    pp.precio_unitario AS unit_price,
    pp.talle AS size,
    pp.color,
    vp.foto_color AS color_image,
    pp.nombre_producto AS product_name,
    p.direccion,
    p.entre_calles,
    p.provincia,
    p.localidad AS ciudad,
    p.codigo_postal,
    p.departamento,
    NULL AS piso,  -- No existe en tu estructura
    p.telefono_cliente AS telefono,
    p.referencias AS observaciones
FROM pedidos p
JOIN usuarios u ON p.email_cliente = u.email
JOIN pedidos_productos pp ON p.id = pp.pedido_id
LEFT JOIN variantes_producto vp ON pp.variante_id = vp.id
WHERE u.id = ?
ORDER BY p.fecha_creacion DESC, p.id DESC, pp.nombre_producto ASC
";

$stmt = $conexion->prepare($order_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result && $order_result->num_rows > 0) {
    // Agrupar los resultados por order_id
    $grouped_orders = [];
    while ($row = $order_result->fetch_assoc()) {
        $grouped_orders[$row['order_id']][] = $row;
    }
?>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4 md:mb-0">Mis Pedidos</h1>
            <div class="bg-blue-50 text-blue-800 px-4 py-2 rounded-full text-sm font-medium">
                <?php echo count($grouped_orders) > 1 ? count($grouped_orders) . ' pedidos realizados' : '1 pedido realizado'; ?>
            </div>
        </div>

        <div class="space-y-8">
            <?php foreach ($grouped_orders as $order_id => $order_items):
                $first_item = $order_items[0];
                $product_count = array_sum(array_column($order_items, 'quantity'));
                
                // Definir colores según el estado
                $status = $first_item['status'];
                $estado_clases = match ($status) {
                    'Pendiente'   => 'bg-yellow-100 text-yellow-800',
                    'En Camino'   => 'bg-purple-100 text-purple-800',
                    'Entregado'   => 'bg-green-100 text-green-800',
                    default       => 'bg-gray-100 text-gray-800',
                };
            ?>
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 hover:border-blue-200 transition-all duration-200">
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <span class="bg-blue-100 text-blue-800 p-2 rounded-lg mr-3">
                                       Numero de pedido: #<?php echo $order_id; ?>
                                    </span>
                                    <span class="text-gray-500 text-sm">
                                        <?php echo date('d M Y', strtotime($first_item['order_date'])); ?>
                                    </span>
                                </h2>
                            </div>
                            <div class="mt-3 md:mt-0">
                                <span class="inline-flex items-center px-4 py-1 rounded-full text-sm font-medium <?= $estado_clases ?>">
                                    Estado del envío: <?= htmlspecialchars($status) ?>
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium text-gray-700 mb-3 flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Dirección de envío
                                </h3>
                                <div class="text-sm text-gray-600 space-y-1">
                                    <div><?php echo htmlspecialchars($first_item['direccion']); ?></div>
                                    <?php if (!empty($first_item['entre_calles'])): ?>
                                        <div class="text-gray-500">Entre: <?php echo htmlspecialchars($first_item['entre_calles']); ?></div>
                                    <?php endif; ?>
                                    <div><?php echo htmlspecialchars($first_item['ciudad']) . ', ' . htmlspecialchars($first_item['provincia']); ?></div>
                                    <div>CP: <?php echo htmlspecialchars($first_item['codigo_postal']); ?></div>
                                    <?php if (!empty($first_item['departamento'])): ?>
                                        <div><?php echo htmlspecialchars($first_item['departamento']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium text-gray-700 mb-3 flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    Información de contacto
                                </h3>
                                <div class="text-sm text-gray-600 space-y-1">
                                    <div>Teléfono: <?php echo htmlspecialchars($first_item['telefono']); ?></div>
                                    <div>Email: <?php echo htmlspecialchars($first_item['usuario_email']); ?></div>
                                    <?php if (!empty($first_item['observaciones'])): ?>
                                        <div class="mt-2 pt-2 border-t border-gray-200">
                                            <span class="font-medium">Notas:</span>
                                            <?php echo htmlspecialchars($first_item['observaciones']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium text-gray-700 mb-3 flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    Resumen del pedido
                                </h3>
                                <div class="text-sm text-gray-600 space-y-2">
                                    <div class="flex justify-between">
                                        <span>Total productos:</span>
                                        <span><?php echo $product_count; ?></span>
                                    </div>
                                    <div class="flex justify-between font-medium border-t border-gray-200 pt-2">
                                        <span class="text-base">Total:</span>
                                        <span class="text-blue-600 text-base">$<?php echo number_format($first_item['total'], 2, ',', '.'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h3 class="font-medium text-gray-700 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            Productos
                        </h3>
                        <div class="space-y-4">
                            <?php foreach ($order_items as $item): ?>
                                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-150">
                                    <div class="flex-shrink-0">
                                        <?php if (!empty($item['color_image'])): ?>
                                            <img src="./admin/controllers/<?php echo htmlspecialchars($item['color_image']); ?>" class="w-20 h-20 object-cover rounded-lg border border-gray-200" alt="Producto">
                                        <?php else: ?>
                                            <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400">
                                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-gray-900 truncate"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            <div class="flex justify-between">
                                                <span>Cantidad: <?php echo $item['quantity']; ?></span>
                                                <span>Precio: $<?php echo number_format($item['unit_price'], 2, ',', '.'); ?></span>
                                            </div>
                                            <div class="flex justify-between font-medium mt-1">
                                                <span>Subtotal:</span>
                                                <span class="text-blue-600">$<?php echo number_format($item['unit_price'] * $item['quantity'], 2, ',', '.'); ?></span>
                                            </div>
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <?php if (!empty($item['size'])): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    Talle: <?php echo htmlspecialchars($item['size']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($item['color'])): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    Color: <?php echo ucfirst(htmlspecialchars($item['color'])); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Sección de contacto de la tienda -->
        <div class="mt-10 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="mb-4 md:mb-0">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">¿Necesitas ayuda con tus pedidos?</h3>
                    <p class="text-gray-600 max-w-2xl">Nuestro equipo de atención al cliente está disponible para ayudarte con cualquier pregunta o inconveniente que tengas con tus pedidos.</p>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 w-full md:w-auto">
                    <div class="flex flex-col space-y-3">
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-2 rounded-full mr-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Teléfono</div>
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($store_contact); ?></div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-2 rounded-full mr-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Email</div>
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($store_email); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
} else {
    // Mostrar mensaje cuando no hay pedidos
?>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h2 class="mt-2 text-2xl font-bold text-gray-900">No tienes pedidos aún</h2>
            <p class="mt-2 text-gray-600">Aún no has realizado ningún pedido en nuestra tienda.</p>
            <div class="mt-6">
                <a href="./" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    Ir a la tienda
                </a>
            </div>

            <!-- Sección de contacto cuando no hay pedidos -->
            <div class="mt-10 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100 max-w-2xl mx-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">¿Tienes preguntas?</h3>
                <p class="text-gray-600 mb-4">Estamos aquí para ayudarte. Contáctanos para cualquier consulta.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <div class="flex items-center justify-center bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                        <div class="bg-blue-100 p-2 rounded-full mr-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Teléfono</div>
                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($store_contact); ?></div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center bg-white p-3 rounded-lg shadow-sm border border-gray-200">
                        <div class="bg-blue-100 p-2 rounded-full mr-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs text-gray-500">Email</div>
                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($store_email); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>