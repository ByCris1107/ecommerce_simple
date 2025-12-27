<?php
// Eliminar producto
if (isset($_GET['eliminar'])) {
    $id_producto = $_GET['eliminar'];
    $conexion->query("DELETE FROM variantes_producto WHERE producto_id = $id_producto");
    $conexion->query("DELETE FROM productos WHERE id = $id_producto");
    echo "<div class='bg-green-100 text-green-800 p-4 rounded mb-4'>Producto eliminado con éxito.</div>";
}

$sql = "SELECT 
            p.*, 
            c.nombre AS nombre_categoria, 
            s.nombre AS nombre_subcategoria 
        FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        LEFT JOIN subcategorias s ON p.subcategoria_id = s.id";

$resultado = $conexion->query($sql);
?>
<?php if (isset($_GET['eliminado'])): ?>
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">Producto eliminado con éxito.</div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">Error: <?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="container mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold mb-6 text-center text-blue-600">Lista de Productos</h1>

    <div class="overflow-x-auto shadow rounded-lg">
        <table class="min-w-full bg-white rounded-lg">
            <thead class="bg-blue-50 text-gray-700 text-sm">
                <tr>
                    <th class="px-4 py-2 text-left">Producto</th>
                    <th class="px-4 py-2 text-left">Categoría</th>
                    <th class="px-4 py-2 text-left">Subcategoría</th>
                    <th class="px-4 py-2 text-left">Precio</th>
                    <th class="px-4 py-2 text-left">Variantes</th>
                    <th class="px-4 py-2 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                <?php while ($producto = $resultado->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4 flex items-center space-x-4">
                            <img src="../admin/controllers/<?= htmlspecialchars($producto['imagen_portada']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="w-16 h-16 object-cover rounded shadow-sm">
                            <div>
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($producto['nombre']) ?></p>
                                <p class="text-gray-500"><?= htmlspecialchars($producto['genero']) ?></p>
                            </div>
                        </td>
                        <td class="px-4 py-4"><?= htmlspecialchars($producto['nombre_categoria']) ?></td>
                        <td class="px-4 py-4"><?= htmlspecialchars($producto['nombre_subcategoria']) ?></td>
                        <td class="px-4 py-4">$<?= number_format($producto['precio'], 2, ',', '.') ?></td>
                        <td class="px-4 py-4">
                            <?php
                            $id = $producto['id'];
                            $variantes = $conexion->query("SELECT color, talle, stock, foto_color FROM variantes_producto WHERE producto_id = $id");
                            while ($variante = $variantes->fetch_assoc()):
                            ?>


                                <div class="flex items-center gap-3 mb-2">
                                    <?php if (!empty($variante['foto_color'])): ?>
                                        <img src="../admin/controllers/<?= htmlspecialchars($variante['foto_color']) ?>" alt="Variante"
                                            class="w-8 h-8 object-cover rounded shadow border border-gray-300">
                                    <?php else: ?>
                                        <div class="w-8 h-8 bg-gray-100 rounded shadow border border-gray-300 flex items-center justify-center text-gray-400 text-xs">
                                            Sin imagen
                                        </div>
                                    <?php endif; ?>

                                    <span class="inline-block w-4 h-4 rounded-full border border-gray-300"
                                        style="background-color: <?= htmlspecialchars($variante['color']) ?>"
                                        title="Color: <?= htmlspecialchars($variante['color']) ?>"></span>

                                    <span class="text-gray-700 font-medium"><?= htmlspecialchars($variante['talle']) ?></span>

                                    <span class="ml-auto text-sm text-gray-500">
                                        Stock: <span class="font-semibold text-gray-700"><?= intval($variante['stock']) ?></span>
                                    </span>
                                </div>




                            <?php endwhile; ?>
                        </td>
                        <td class="px-4 py-4 space-x-2 flex items-center">
                            <button onclick="abrirModalEditar(<?= $producto['id'] ?>)"
                                class="text-yellow-600 hover:text-yellow-800 font-semibold text-sm focus:outline-none">
                                Editar
                            </button>

                            <button onclick="abrirModalAgregarVariante(<?= $producto['id'] ?>)"
                                class="text-blue-600 hover:text-blue-800 font-semibold text-sm focus:outline-none">
                                Agregar variante
                            </button>

                            <a href="./controllers/eliminar_producto.php?id=<?= $producto['id'] ?>"
                                onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?')"
                                class="text-red-600 hover:text-red-800 font-semibold text-sm">
                                Eliminar
                            </a>
                        </td>

                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modal-editar" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50 overflow-hidden">
    <div class="modal-wrapper bg-white w-full max-w-4xl max-h-[90vh] rounded-lg shadow-lg relative flex flex-col">

        <button onclick="cerrarModalEditar()" class="absolute top-2 right-2 text-gray-500 hover:text-black text-xl z-10">&times;</button>

        <h2 class="text-xl font-semibold p-6 pb-2 border-b">Editar Producto</h2>

        <div class="modal-scrollable overflow-y-auto px-6 py-4 space-y-4" style="max-height: calc(90vh - 60px);">
            <form id="form-editar-producto" class="space-y-4" enctype="multipart/form-data">
                <input type="hidden" name="id_producto" id="edit-id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium">Nombre:</label>
                        <input type="text" name="nombre_producto" id="edit-nombre" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Género:</label>
                        <input type="text" name="genero_producto" id="edit-genero" class="w-full border rounded px-3 py-2" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium">Categoría:</label>
                        <select name="categoria" id="edit-categoria" class="w-full border rounded px-3 py-2" onchange="cargarSubcategorias()" required>
                            <option value="">Selecciona una categoría</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Subcategoría:</label>
                        <select name="subcategoria" id="edit-subcategoria" class="w-full border rounded px-3 py-2">
                            <option value="">Selecciona una subcategoría</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium">Descripción:</label>
                    <textarea name="descripcion" id="edit-descripcion" class="w-full border rounded px-3 py-2" rows="3"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium">Precio:</label>
                        <input type="number" step="0.01" name="precio" id="edit-precio" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Estaciones:</label>
                        <div class="flex flex-wrap gap-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="estaciones[]" value="Primavera" class="rounded">
                                <span class="ml-2">Primavera</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="estaciones[]" value="Verano" class="rounded">
                                <span class="ml-2">Verano</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="estaciones[]" value="Otoño" class="rounded">
                                <span class="ml-2">Otoño</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="estaciones[]" value="Invierno" class="rounded">
                                <span class="ml-2">Invierno</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium">Imagen de portada:</label>
                    <div id="contenedor-imagen-portada" class="mb-4">
                        <img id="imagen-portada-actual" src="" alt="Imagen actual" class="w-32 h-32 object-cover rounded shadow-sm">
                    </div>
                    <input type="file" name="imagen_portada" id="edit-imagen-portada" class="w-full border rounded px-3 py-2" accept="image/jpeg, image/png, image/webp">
                </div>

                <div class="border-t pt-4">
                    <h3 class="font-medium mb-3">Variantes del Producto</h3>
                    <div id="variantes-container" class="space-y-4"></div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="modal-agregar-variante" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white p-6 rounded-lg w-full max-w-md relative">
        <h2 class="text-xl font-bold mb-4">Agregar Variante</h2>

        <form id="form-agregar-variante" action="./controllers/guardar_variante" method="POST" enctype="multipart/form-data" class="space-y-4">
            <!-- input oculto con el ID del producto -->
            <input type="hidden" name="producto_id" id="producto_id_input">

            <div>
                <label class="block font-semibold">Color</label>
                <input type="text" name="color" class="w-full border rounded px-2 py-1" required>
            </div>

            <div>
                <label class="block font-semibold">Talle</label>
                <input type="text" name="talle" class="w-full border rounded px-2 py-1" required>
            </div>

            <div>
                <label class="block font-semibold">Stock</label>
                <input type="number" name="stock" class="w-full border rounded px-2 py-1" required>
            </div>


            <div>
                <label class="block font-semibold">Foto del color</label>
                <input type="file" name="foto_color" class="w-full border rounded px-2 py-1" required>
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button" onclick="cerrarModalAgregarVariante()" class="bg-gray-400 text-white px-3 py-1 rounded hover:bg-gray-500">Cancelar</button>
                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Guardar</button>
            </div>
        </form>
    </div>
</div>




<!-- Agrega esto al final del modal, antes del cierre del div -->
<script>
    function cerrarModalEditar() {
        const modal = document.getElementById('modal-editar');
        modal.classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const formulario = document.getElementById('form-editar-producto');

        if (formulario) {
            formulario.addEventListener('submit', async function(e) {
                e.preventDefault();

                const id = document.getElementById('edit-id').value;
                const formData = new FormData(this);

                try {
                    const response = await fetch(`./controllers/actualizar_producto.php?id=${encodeURIComponent(id)}`, {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error: ${response.status}`);
                    }

                    const result = await response.json();

                    if (result.success) {
                        alert('Producto actualizado con éxito');
                        window.location.reload();
                    } else {
                        alert('Error al actualizar: ' + (result.message || 'Error desconocido'));
                        console.warn('Respuesta del servidor:', result);
                    }

                } catch (error) {
                    console.error('Error al conectar con el servidor:', error);
                    alert('No se pudo conectar con el servidor. Revisa la consola para más detalles.');
                }
            });
        }
    });

    async function cargarCategorias(categoriaId = null, subcategoriaId = null) {
        try {
            const res = await fetch('./controllers/cargar_categorias.php');
            if (!res.ok) throw new Error(`Error al obtener categorías: ${res.status}`);
            const data = await res.json();

            let html = '<option value="">Selecciona una categoría</option>';
            data.forEach(c => {
                html += `<option value="${c.id}">${c.nombre}</option>`;
            });

            const selectCategoria = document.getElementById('edit-categoria');
            selectCategoria.innerHTML = html;

            if (categoriaId) {
                selectCategoria.value = categoriaId;
                await cargarSubcategorias(categoriaId, subcategoriaId);
            }

        } catch (error) {
            console.error('Error al cargar categorías:', error);
            alert('Error cargando categorías');
        }
    }

    async function cargarSubcategorias(categoriaId, subcategoriaIdToSelect = null) {
        if (!categoriaId) return;

        try {
            const res = await fetch(`./controllers/cargar_subcategorias.php?categoria_id=${encodeURIComponent(categoriaId)}`);
            if (!res.ok) throw new Error(`Error al obtener subcategorías: ${res.status}`);
            const data = await res.json();

            let html = '<option value="">Selecciona una subcategoría</option>';
            data.forEach(s => {
                html += `<option value="${s.id}">${s.nombre}</option>`;
            });

            const selectSubcategoria = document.getElementById('edit-subcategoria');
            selectSubcategoria.innerHTML = html;

            if (subcategoriaIdToSelect) {
                selectSubcategoria.value = subcategoriaIdToSelect;
            }

        } catch (error) {
            console.error('Error al cargar subcategorías:', error);
        }
    }

    async function abrirModalEditar(id) {
        try {
            const res = await fetch(`./controllers/cargar_datos_producto.php?id=${encodeURIComponent(id)}`);
            if (!res.ok) throw new Error(`Error al cargar datos del producto: ${res.status}`);
            const data = await res.json();

            if (data.error) throw new Error(data.message || 'Error desconocido');

            document.getElementById('edit-id').value = data.id;
            document.getElementById('edit-nombre').value = data.nombre;
            document.getElementById('edit-genero').value = data.genero;
            document.getElementById('edit-descripcion').value = data.descripcion || '';
            document.getElementById('edit-precio').value = data.precio;

            if (data.estaciones) {
                const estaciones = data.estaciones.split(', ');
                document.querySelectorAll('input[name="estaciones[]"]').forEach(checkbox => {
                    checkbox.checked = estaciones.includes(checkbox.value);
                });
            }

            const imgElement = document.getElementById('imagen-portada-actual');
            if (data.imagen_portada) {
                imgElement.src = `./controllers/${data.imagen_portada}`;
                imgElement.style.display = 'block';
            } else {
                imgElement.style.display = 'none';
            }

            await cargarCategorias(data.categoria_id, data.subcategoria_id);
            await cargarVariantes(data.id);

            const modal = document.getElementById('modal-editar');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

        } catch (error) {
            console.error('Error al abrir el modal de edición:', error);
            alert('No se pudieron cargar los datos del producto');
        }
    }

    async function cargarVariantes(id) {
        try {
            const res = await fetch(`./controllers/cargar_variantes.php?id_producto=${encodeURIComponent(id)}`);
            if (!res.ok) throw new Error(`Error al cargar variantes: ${res.status}`);
            const data = await res.json();

            const container = document.getElementById('variantes-container');
            let html = '';

            data.forEach(variante => {
                html += `
                <div class="variant-item border p-4 mb-4 rounded-lg relative" data-id="${variante.id}">
                    <button type="button" class="btn-eliminar absolute top-2 right-2 text-red-600 font-bold text-xl hover:text-red-800" title="Eliminar variante">&times;</button>
                    
                    <input type="hidden" name="variante_id[]" value="${variante.id}">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Talle:</label>
                            <input type="text" name="talle[]" value="${variante.talle}" class="w-full border rounded px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Color:</label>
                            <input type="text" name="color[]" value="${variante.color}" class="w-full border rounded px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Stock:</label>
                            <input type="number" name="stock[]" value="${variante.stock}" class="w-full border rounded px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Foto Color:</label>
                            <div class="flex items-center gap-2">
                                ${variante.foto_color ? 
                                    `<img src="./controllers/${variante.foto_color}" alt="Variante" class="w-10 h-10 object-cover rounded border">` : 
                                    '<div class="w-10 h-10 bg-gray-100 rounded border flex items-center justify-center text-gray-400">Sin imagen</div>'
                                }
                                <input type="file" name="foto_color[]" class="flex-1 border rounded px-3 py-2">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            });

            container.innerHTML = html;

            // Listener para eliminar variantes
            container.querySelectorAll('.btn-eliminar').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const varianteDiv = e.target.closest('.variant-item');
                    const varianteId = varianteDiv.getAttribute('data-id');

                    if (confirm('¿Querés eliminar esta variante?')) {
                        try {
                            const res = await fetch('./controllers/eliminar_variante.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    id: varianteId
                                })
                            });
                            const data = await res.json();
                            if (data.success) {
                                varianteDiv.remove();
                                alert('Variante eliminada correctamente');
                            } else {
                                alert('Error al eliminar variante: ' + (data.message || 'Error desconocido'));
                            }
                        } catch {
                            alert('Error de comunicación con el servidor');
                        }
                    }
                });
            });

        } catch (error) {
            console.error('Error al cargar variantes:', error);
        }
    }




    const addButton = container.querySelector('button');
    if (addButton) {
        addButton.insertAdjacentHTML('beforebegin', newHTML);
    } else {
        container.insertAdjacentHTML('beforeend', newHTML);
    }


    function eliminarVariante(button, varianteId) {
        const variantItem = button.closest('.variant-item');

        if (varianteId) {
            if (confirm('¿Deseas eliminar esta variante definitivamente?')) {
                variantItem.style.display = 'none';

                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'variantes_a_eliminar[]';
                deleteInput.value = varianteId;
                variantItem.appendChild(deleteInput);
            }
        } else {
            variantItem.remove();
        }
    }



    function abrirModalAgregarVariante(productoId) {
        // Seteamos el ID del producto en el input oculto
        document.getElementById('producto_id_input').value = productoId;

        // Mostramos el modal
        document.getElementById('modal-agregar-variante').classList.remove('hidden');
    }

    function cerrarModalAgregarVariante() {
        document.getElementById('modal-agregar-variante').classList.add('hidden');
    }
</script>