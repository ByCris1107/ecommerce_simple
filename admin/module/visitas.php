<?php
// Total de visitas
$query_total = "SELECT SUM(contador) AS total_visitas FROM visitas";
$result_total = mysqli_query($conexion, $query_total);
$fila_total = mysqli_fetch_assoc($result_total);
$nuevo_contador = $fila_total['total_visitas'] ?? 0;

// Visitas diarias (últimos 7 días)
$query_diarias = "SELECT fecha, SUM(contador) AS visitas_diarias
                  FROM visitas
                  WHERE fecha BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE()
                  GROUP BY fecha
                  ORDER BY fecha DESC";
$result_diarias = mysqli_query($conexion, $query_diarias);
$fechas = [];
$contadores_diarios = [];
while ($row = mysqli_fetch_assoc($result_diarias)) {
    $fechas[] = date('d M', strtotime($row['fecha'])); // Formato más corto para las fechas
    $contadores_diarios[] = (int)$row['visitas_diarias'];
}

// Visitas mensuales (año actual)
$query_mensuales = "SELECT MONTH(fecha) AS mes, SUM(contador) AS visitas_mensuales
                    FROM visitas
                    WHERE YEAR(fecha) = YEAR(CURDATE())
                    GROUP BY mes
                    ORDER BY mes ASC";
$result_mensuales = mysqli_query($conexion, $query_mensuales);
$visitas_mensuales = [];
while ($row = mysqli_fetch_assoc($result_mensuales)) {
    $visitas_mensuales[] = [
        'mes' => (int)$row['mes'],
        'visitas_mensuales' => (int)$row['visitas_mensuales']
    ];
}

// URLs más visitadas
$query_urls = "SELECT url, SUM(contador) AS total_visitas 
               FROM visitas 
               GROUP BY url 
               ORDER BY total_visitas DESC 
               LIMIT 10";
$result_urls = mysqli_query($conexion, $query_urls);
$urls_data = [];
while ($row = mysqli_fetch_assoc($result_urls)) {
    $urls_data[] = [
        'url' => $row['url'],
        'visitas' => (int)$row['total_visitas']
    ];
}
?>

<!-- HTML Mejorado -->
<div class="container mx-auto p-4 md:p-8">
    <h1 class="text-3xl font-bold mb-8 text-center text-blue-800">Estadísticas de Visitas</h1>
    
    <!-- Grid de Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Visitas Totales -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl shadow-lg text-white">
            <h2 class="text-xl font-semibold mb-2">Visitas Totales</h2>
            <p class="text-5xl font-bold"><?php echo number_format($nuevo_contador); ?></p>
            <div class="mt-4 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                <span>Total de visitantes</span>
            </div>
        </div>
        
        <!-- Visitas Hoy -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl shadow-lg text-white">
            <h2 class="text-xl font-semibold mb-2">Visitas Hoy</h2>
            <p class="text-5xl font-bold"><?php echo isset($contadores_diarios[0]) ? number_format($contadores_diarios[0]) : '0'; ?></p>
            <div class="mt-4 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Visitas hoy</span>
            </div>
        </div>
        
        <!-- Mes Actual -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-xl shadow-lg text-white">
            <h2 class="text-xl font-semibold mb-2">Visitas este Mes</h2>
            <?php 
            $current_month = date('n');
            $monthly_visits = 0;
            foreach ($visitas_mensuales as $mes) {
                if ($mes['mes'] == $current_month) {
                    $monthly_visits = $mes['visitas_mensuales'];
                    break;
                }
            }
            ?>
            <p class="text-5xl font-bold"><?php echo number_format($monthly_visits); ?></p>
            <div class="mt-4 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>Visitas en <?php echo date('F'); ?></span>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Visitas Diarias -->
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Visitas de los Últimos 7 Días</h2>
            <canvas id="visitasDiariasChart" height="250"></canvas>
            <script>
                var ctx = document.getElementById('visitasDiariasChart').getContext('2d');
                var visitasDiariasChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($fechas); ?>,
                        datasets: [{
                            label: 'Visitas Diarias',
                            data: <?php echo json_encode($contadores_diarios); ?>,
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 2,
                            pointBackgroundColor: '#3B82F6',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1E293B',
                                titleFont: { size: 14 },
                                bodyFont: { size: 14 },
                                padding: 12,
                                displayColors: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            </script>
        </div>

        <!-- Visitas Mensuales -->
        <div class="bg-white p-6 rounded-xl shadow-md">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Visitas Mensuales</h2>
            <canvas id="visitasMensualesChart" height="250"></canvas>
            <script>
                var ctx = document.getElementById('visitasMensualesChart').getContext('2d');
                var months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                var visitasMensuales = <?php echo json_encode($visitas_mensuales); ?>;
                var visitasData = new Array(12).fill(0);
                for (var i = 0; i < visitasMensuales.length; i++) {
                    visitasData[visitasMensuales[i].mes - 1] = visitasMensuales[i].visitas_mensuales;
                }
                
                var visitasMensualesChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Visitas Mensuales',
                            data: visitasData,
                            backgroundColor: 'rgba(124, 58, 237, 0.7)',
                            borderColor: 'rgb(124, 58, 237)',
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1E293B',
                                titleFont: { size: 14 },
                                bodyFont: { size: 14 },
                                padding: 12,
                                displayColors: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            </script>
        </div>
    </div>

    <!-- URLs Más Visitadas -->
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h2 class="text-2xl font-semibold mb-4 text-gray-800">URLs Más Visitadas</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Porcentaje</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php 
                    $total_url_visits = array_sum(array_column($urls_data, 'visitas'));
                    foreach ($urls_data as $url_item): 
                        $percentage = ($total_url_visits > 0) ? round(($url_item['visitas'] / $total_url_visits) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="<?php echo htmlspecialchars($url_item['url']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline break-all">
                                <?php echo htmlspecialchars($url_item['url']); ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo number_format($url_item['visitas']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="mr-2"><?php echo $percentage; ?>%</span>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>