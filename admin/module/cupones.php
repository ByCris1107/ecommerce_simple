<form id="form-cupon" method="POST" class="bg-white shadow-lg rounded-xl px-8 pt-8 pb-6 mb-4 max-w-3xl mx-auto">
    <h2 class="text-3xl font-bold mb-8 text-center text-gray-800">Crear Nuevo Cupón</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Columna Izquierda -->
        <div class="space-y-5">
            <!-- Código -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
                <div class="relative">
                    <input type="text" name="codigo" maxlength="20" required 
                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                           placeholder="EJEMPLO20">
                    <span class="absolute right-3 top-3 text-xs text-gray-400">20 caracteres max</span>
                </div>
            </div>

            <!-- Tipo y Valor de descuento -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select name="tipo_descuento" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="porcentaje">Porcentaje (%)</option>
                        <option value="monto_fijo">Monto Fijo ($)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor *</label>
                    <input type="number" name="descuento" step="0.01" min="0" required
                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
            </div>
        </div>

        <!-- Columna Derecha -->
        <div class="space-y-5">
            <!-- Usos -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Usos Totales *</label>
                    <input type="number" name="usos_totales" id="usos_totales" min="1" required
                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Usos Restantes</label>
                    <input type="number" name="usos_restantes" id="usos_restantes" 
                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-gray-50 focus:ring-2 focus:ring-blue-500 transition-all"
                           readonly>
                </div>
            </div>

            <!-- Fechas -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio *</label>
                    <input type="date" name="fecha_inicio" required
                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin *</label>
                    <input type="date" name="fecha_fin" required
                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
            </div>

            <!-- Estado -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado *</label>
                <select name="estado" required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <option value="activo" selected>Activo</option>
                    <option value="inactivo">Inactivo</option>
                    <option value="agotado">Agotado</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Botón de envío -->
    <div class="mt-8 flex justify-center">
        <button type="submit" 
                class="bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white font-semibold py-3 px-8 rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
            Guardar Cupón
        </button>
    </div>
</form>

<script>
    // Auto-completar usos restantes
    document.getElementById('usos_totales').addEventListener('input', function() {
        document.getElementById('usos_restantes').value = this.value;
    });

    // Inicializar al cargar
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('usos_restantes').value = document.getElementById('usos_totales').value || 0;
    });

    // Manejo del formulario con jQuery
    $(document).ready(function() {
        $('#form-cupon').on('submit', function(e) {
            e.preventDefault();
            
            // Mostrar loader mientras se procesa
            Swal.fire({
                title: 'Procesando',
                html: 'Guardando el cupón...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: './controllers/guardar_cupon.php',
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                dataType: 'json'
            })
            .done(function(data) {
                Swal.close();
                if (data.swal) {
                    Swal.fire({
                        icon: data.swal.icon,
                        title: data.swal.title,
                        html: data.swal.text + (data.swal.footer ? `<br><small>${data.swal.footer}</small>` : ''),
                        showConfirmButton: true,
                        allowOutsideClick: false
                    }).then(function() {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    });
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                Swal.close();
                let errorMsg = 'Error en la comunicación con el servidor';
                if (jqXHR.responseJSON && jqXHR.responseJSON.swal) {
                    errorMsg = jqXHR.responseJSON.swal.text;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
            });
        });
    });
</script>



<?php
// Consulta para obtener todos los cupones
$query = "SELECT * FROM cupones_descuento ORDER BY creado_en DESC";
$result = $conexion->query($query);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Administración de Cupones</h1>
        <button onclick="abrirModalCreacion()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            Nuevo Cupón
        </button>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descuento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vigencia</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($cupon = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900"><?= htmlspecialchars($cupon['codigo']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $cupon['tipo_descuento'] === 'porcentaje' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' ?>">
                                        <?= $cupon['tipo_descuento'] === 'porcentaje' ? 'Porcentaje' : 'Monto Fijo' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-gray-900">
                                        <?= $cupon['descuento'] ?>
                                        <?= $cupon['tipo_descuento'] === 'porcentaje' ? '%' : '$' ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-gray-900">
                                        <span class="font-medium"><?= $cupon['usos_restantes'] ?></span>
                                        <span class="text-gray-500">/<?= $cupon['usos_totales'] ?></span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                        <div class="bg-blue-600 h-1.5 rounded-full" 
                                             style="width: <?= ($cupon['usos_restantes'] / $cupon['usos_totales']) * 100 ?>%"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-gray-900">
                                        <?= date('d/m/Y', strtotime($cupon['fecha_inicio'])) ?> - 
                                        <?= date('d/m/Y', strtotime($cupon['fecha_fin'])) ?>
                                    </div>
                                    <?php 
                                        $hoy = new DateTime();
                                        $fin = new DateTime($cupon['fecha_fin']);
                                        $inicio = new DateTime($cupon['fecha_inicio']);
                                        $dias_restantes = $hoy->diff($fin)->days;
                                    ?>
                                    <div class="text-xs <?= $hoy > $fin ? 'text-red-500' : ($hoy < $inicio ? 'text-yellow-500' : 'text-green-500') ?>">
                                        <?php if ($hoy > $fin): ?>
                                            Expirado
                                        <?php elseif ($hoy < $inicio): ?>
                                            Inicia en <?= $hoy->diff($inicio)->days ?> días
                                        <?php else: ?>
                                            <?= $dias_restantes ?> días restantes
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= $cupon['estado'] === 'activo' ? 'bg-green-100 text-green-800' : 
                                           ($cupon['estado'] === 'inactivo' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800') ?>">
                                        <?= ucfirst($cupon['estado']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button type="button" onclick="abrirModalEdicion(<?= $cupon['id'] ?>)" 
                                                class="text-blue-600 hover:text-blue-900">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </button>
                                        <button type="button" onclick="confirmarEliminacion(<?= $cupon['id'] ?>)" 
                                                class="text-red-600 hover:text-red-900">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No hay cupones registrados
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de creación (nuevo cupón) -->
<script>
function abrirModalCreacion() {
    Swal.fire({
        title: 'Crear Nuevo Cupón',
        html: generarFormularioCreacion(),
        width: '800px',
        showConfirmButton: false,
        showCloseButton: true,
        didOpen: () => {
            inicializarFormularioCreacion();
        }
    });
}

function generarFormularioCreacion() {
    return `
    <form id="form-crear-cupon" class="text-left">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Columna Izquierda -->
            <div class="space-y-4">
                <!-- Código -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
                    <input type="text" name="codigo" maxlength="20" required 
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Tipo y Valor de descuento -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                        <select name="tipo_descuento" required
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="porcentaje">Porcentaje (%)</option>
                            <option value="monto_fijo">Monto Fijo ($)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor *</label>
                        <input type="number" name="descuento" step="0.01" min="0" required
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Columna Derecha -->
            <div class="space-y-4">
                <!-- Usos -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usos Totales *</label>
                        <input type="number" name="usos_totales" id="usos_totales_crear" min="1" required
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usos Restantes</label>
                        <input type="number" name="usos_restantes" id="usos_restantes_crear" 
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-gray-50 focus:ring-2 focus:ring-blue-500"
                               readonly>
                    </div>
                </div>

                <!-- Fechas -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio *</label>
                        <input type="date" name="fecha_inicio" required
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin *</label>
                        <input type="date" name="fecha_fin" required
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado *</label>
            <select name="estado" required
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-3">
            <button type="button" onclick="Swal.close()" 
                    class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg">
                Cancelar
            </button>
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                Crear Cupón
            </button>
        </div>
    </form>
    `;
}

function inicializarFormularioCreacion() {
    // Auto-completar usos restantes
    document.getElementById('usos_totales_crear').addEventListener('input', function() {
        document.getElementById('usos_restantes_crear').value = this.value;
    });

    // Manejar envío del formulario
    document.getElementById('form-crear-cupon').addEventListener('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Creando cupón...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const formData = new FormData(this);
        
        fetch('./controllers/crear_cupon.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.message || 'Cupón creado correctamente',
                willClose: () => {
                    window.location.reload();
                }
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Error al crear el cupón'
            });
            console.error('Error:', error);
        });
    });
}

function abrirModalEdicion(idCupon) {
    Swal.fire({
        title: 'Cargando...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            
            fetch(`./controllers/obtener_cupon.php?id=${idCupon}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    Swal.fire({
                        title: `Editar Cupón: ${data.codigo}`,
                        html: generarFormularioEdicion(data),
                        width: '800px',
                        showConfirmButton: false,
                        showCloseButton: true,
                        didOpen: () => {
                            inicializarFormularioEdicion();
                        }
                    });
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message
                    });
                    console.error('Error:', error);
                });
        }
    });
}

function generarFormularioEdicion(cupon) {
    return `
    <form id="form-editar-cupon" class="text-left">
        <input type="hidden" name="id" value="${cupon.id}">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Columna Izquierda -->
            <div class="space-y-4">
                <!-- Código -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
                    <input type="text" name="codigo" maxlength="20" required 
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="${cupon.codigo}">
                </div>

                <!-- Tipo y Valor de descuento -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                        <select name="tipo_descuento" required
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="porcentaje" ${cupon.tipo_descuento === 'porcentaje' ? 'selected' : ''}>Porcentaje (%)</option>
                            <option value="monto_fijo" ${cupon.tipo_descuento === 'monto_fijo' ? 'selected' : ''}>Monto Fijo ($)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor *</label>
                        <input type="number" name="descuento" step="0.01" min="0" required
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="${cupon.descuento}">
                    </div>
                </div>
            </div>

            <!-- Columna Derecha -->
            <div class="space-y-4">
                <!-- Usos -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usos Totales *</label>
                        <input type="number" name="usos_totales" id="usos_totales" min="1" required
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="${cupon.usos_totales}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usos Restantes</label>
                        <input type="number" name="usos_restantes" id="usos_restantes" 
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-gray-50 focus:ring-2 focus:ring-blue-500"
                               value="${cupon.usos_restantes}" readonly>
                    </div>
                </div>

                <!-- Fechas -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio *</label>
                        <input type="date" name="fecha_inicio" required
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="${cupon.fecha_inicio}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin *</label>
                        <input type="date" name="fecha_fin" required
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               value="${cupon.fecha_fin}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Estado *</label>
            <select name="estado" required
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="activo" ${cupon.estado === 'activo' ? 'selected' : ''}>Activo</option>
                <option value="inactivo" ${cupon.estado === 'inactivo' ? 'selected' : ''}>Inactivo</option>
                <option value="agotado" ${cupon.estado === 'agotado' ? 'selected' : ''}>Agotado</option>
            </select>
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-3">
            <button type="button" onclick="Swal.close()" 
                    class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg">
                Cancelar
            </button>
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                Guardar Cambios
            </button>
        </div>
    </form>
    `;
}

function inicializarFormularioEdicion() {
    // Auto-completar usos restantes
    document.getElementById('usos_totales').addEventListener('input', function() {
        document.getElementById('usos_restantes').value = this.value;
    });

    // Manejar envío del formulario
    document.getElementById('form-editar-cupon').addEventListener('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Guardando cambios...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const formData = new FormData(this);
        
        fetch('./controllers/actualizar_cupon.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.message || 'Cupón actualizado correctamente',
                willClose: () => {
                    window.location.reload();
                }
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Error al actualizar el cupón'
            });
            console.error('Error:', error);
        });
    });
}

function confirmarEliminacion(idCupon) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esta acción!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Eliminando...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            
            fetch('./controllers/eliminar_cupon.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${idCupon}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: data.message || 'Cupón eliminado correctamente',
                    willClose: () => {
                        window.location.reload();
                    }
                });
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Error al eliminar el cupón'
                });
                console.error('Error:', error);
            });
        }
    });
}
</script>