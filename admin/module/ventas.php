<?php

// Inicializar array de estadísticas
$estadisticas = [];
$mas_vendido = null;
$menos_vendido = null;
$result_ventas = null;

// Consulta para obtener estadísticas generales
$query_estadisticas = "
    SELECT 
        (SELECT COUNT(*) FROM productos) as total_productos_catalogo,
        (SELECT COUNT(DISTINCT producto_id) FROM pedidos_productos pp JOIN pedidos p ON pp.pedido_id = p.id WHERE p.estado = 'Entregado') as total_productos_vendidos,
        (SELECT SUM(cantidad) FROM pedidos_productos pp JOIN pedidos p ON pp.pedido_id = p.id WHERE p.estado = 'Entregado') as total_unidades_vendidas,
        (SELECT SUM(total) FROM pedidos WHERE estado = 'Entregado') as ventas_totales
";

$result = $conexion->query($query_estadisticas);
if ($result && $result->num_rows > 0) {
    $estadisticas = $result->fetch_assoc();
}

// Consulta para obtener productos más y menos vendidos
$query_top_productos = "
    SELECT 
        pp.nombre_producto as producto,
        SUM(pp.cantidad) as unidades_vendidas,
        SUM(pp.precio_unitario * pp.cantidad) as total_ventas
    FROM pedidos_productos pp
    JOIN pedidos p ON pp.pedido_id = p.id
    WHERE p.estado = 'Entregado'
    GROUP BY pp.nombre_producto
    ORDER BY unidades_vendidas DESC
";

$result_top = $conexion->query($query_top_productos);
if ($result_top && $result_top->num_rows > 0) {
    $productos_vendidos = $result_top->fetch_all(MYSQLI_ASSOC);
    
    // Producto más vendido (primero en la lista ordenada DESC)
    $mas_vendido = $productos_vendidos[0];
    
    // Producto menos vendido (último en la lista)
    $menos_vendido = end($productos_vendidos);
}

// Consulta detallada de ventas por producto
$query_ventas_detalle = "
    SELECT 
        pp.nombre_producto as producto,
        c.nombre as categoria,
        pp.color,
        pp.talle,
        SUM(pp.cantidad) as unidades_vendidas,
        SUM(pp.precio_unitario * pp.cantidad) as total_ventas,
        COUNT(DISTINCT pp.pedido_id) as veces_vendido
    FROM pedidos_productos pp
    JOIN pedidos p ON pp.pedido_id = p.id
    LEFT JOIN productos pr ON pp.producto_id = pr.id
    LEFT JOIN categorias c ON pr.categoria_id = c.id
    WHERE p.estado = 'Entregado'
    GROUP BY pp.nombre_producto, pp.color, pp.talle
    ORDER BY unidades_vendidas DESC
";

$result_ventas = $conexion->query($query_ventas_detalle);
?>

<!-- Aquí iría tu código HTML que ya tienes -->

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Análisis de Ventas (Pedidos Entregados)</h1>
        
        <!-- Estadísticas Resumidas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Productos en Catálogo -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                        <i class="fas fa-box-open text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Productos en Catálogo</p>
                        <p class="text-2xl font-bold"><?= $estadisticas['total_productos_catalogo'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Total Productos Vendidos -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                        <i class="fas fa-tags text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Productos Vendidos</p>
                        <p class="text-2xl font-bold"><?= $estadisticas['total_productos_vendidos'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Total Unidades Vendidas -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                        <i class="fas fa-shopping-cart text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Unidades Vendidas</p>
                        <p class="text-2xl font-bold"><?= $estadisticas['total_unidades_vendidas'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Total Ventas -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                        <i class="fas fa-dollar-sign text-xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Ventas Totales</p>
                        <p class="text-2xl font-bold">$<?= isset($estadisticas['ventas_totales']) ? number_format($estadisticas['ventas_totales'], 2, ',', '.') : '0,00' ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Productos Destacados -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Producto Más Vendido -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                    Producto Más Vendido
                </h2>
                <?php if (isset($mas_vendido) && $mas_vendido): ?>
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-full mr-4">
                        <i class="fas fa-fire text-green-600"></i>
                    </div>
                    <div>
                        <p class="font-bold text-lg"><?= $mas_vendido['producto'] ?></p>
                        <p class="text-gray-600"><?= $mas_vendido['unidades_vendidas'] ?> unidades vendidas</p>
                        <p class="text-green-600 font-medium">$<?= number_format($mas_vendido['total_ventas'], 2, ',', '.') ?> en ventas</p>
                    </div>
                </div>
                <?php else: ?>
                <p class="text-gray-500">No hay productos vendidos aún</p>
                <?php endif; ?>
            </div>
            
            <!-- Producto Menos Vendido -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                    Producto Menos Vendido
                </h2>
                <?php if (isset($menos_vendido) && $menos_vendido): ?>
                <div class="flex items-center">
                    <div class="bg-red-100 p-3 rounded-full mr-4">
                        <i class="fas fa-sad-tear text-red-600"></i>
                    </div>
                    <div>
                        <p class="font-bold text-lg"><?= $menos_vendido['producto'] ?></p>
                        <p class="text-gray-600"><?= $menos_vendido['unidades_vendidas'] ?> unidades vendidas</p>
                        <p class="text-red-600 font-medium">$<?= number_format($menos_vendido['total_ventas'], 2, ',', '.') ?> en ventas</p>
                    </div>
                </div>
                <?php else: ?>
                <p class="text-gray-500">No hay productos vendidos aún</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Tabla de Ventas por Producto -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-table mr-2"></i>
                    Ventas por Producto (Pedidos Entregados)
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variante</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidades Vendidas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Ventas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Veces Vendido</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($result_ventas && $result_ventas->num_rows > 0): ?>
                            <?php while($producto = $result_ventas->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?= $producto['producto'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500"><?= $producto['categoria'] ?? 'Sin categoría' ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= $producto['color'] ?> / <?= $producto['talle'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500"><?= $producto['unidades_vendidas'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500">$<?= number_format($producto['total_ventas'], 2, ',', '.') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-500"><?= $producto['veces_vendido'] ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No hay datos de ventas disponibles</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>