<main class="w-full bg-white p-6">
            <h2 class="text-2xl font-bold mb-4">Panel de Administrador</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Tarjeta de visitas -->
                <div class="bg-blue-100 p-4 rounded-lg shadow flex items-center">
                    <i class="fas fa-eye text-3xl text-blue-600 mr-3"></i>
                    <div>
                        <h3 class="text-lg font-semibold">Visitas a la tienda</h3>
                        <p class="text-2xl font-bold">
                            <?php
                            // Suponiendo que $nuevo_contador tiene el número de visitas
                            echo $contador_actual;
                            ?>
                        </p>
                    </div>
                </div>
            </div>