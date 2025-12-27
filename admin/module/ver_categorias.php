<?php
// Consulta para obtener las categorías
$sql = "SELECT * FROM categorias";
$resultado = $conexion->query($sql);
?>

<div class="container mx-auto mt-8 px-4">
    <h1 class="text-3xl font-semibold text-center mb-6">Categorías</h1>
    
    <table class="w-full border border-gray-300 rounded-lg overflow-hidden shadow-sm">
        <thead class="bg-gray-200">
            <tr>
                <th class="text-left py-3 px-6">Nombre</th>
                <th class="text-left py-3 px-6">Imagen</th>
                <th class="text-left py-3 px-6">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($categoria = $resultado->fetch_assoc()) { ?>
                <tr class="border-t hover:bg-gray-50">
                    <td class="py-3 px-6"><?= htmlspecialchars($categoria['nombre']) ?></td>
                    <td class="py-3 px-6">
                        <?php if (!empty($categoria['imagen'])) { ?>
                            <img src="<?= htmlspecialchars($categoria['imagen']) ?>" alt="<?= htmlspecialchars($categoria['nombre']) ?>" class="w-16 h-16 object-cover rounded">
                        <?php } else { ?>
                            <span class="text-gray-500">Sin imagen</span>
                        <?php } ?>
                    </td>
                    <td class="py-3 px-6 space-x-2">
                        <!-- Botón Editar -->
                        <button onclick="abrirModalEditar(<?= $categoria['id'] ?>, '<?= htmlspecialchars($categoria['nombre']) ?>', '<?= htmlspecialchars($categoria['imagen']) ?>')" class="bg-blue-500 text-white py-1 px-3 rounded hover:bg-blue-600">Editar</button>

                        <!-- Botón Eliminar -->
                        <button onclick="eliminarCategoria(<?= $categoria['id'] ?>)" class="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-600">Eliminar</button>
                    </td>
                </tr>

                <!-- Mostrar subcategorías -->
                <?php
                // Obtener subcategorías de la categoría actual
                $sql_subcategoria = "SELECT * FROM subcategorias WHERE categoria_id = " . $categoria['id'];
                $resultado_subcategoria = $conexion->query($sql_subcategoria);
                while ($subcategoria = $resultado_subcategoria->fetch_assoc()) { ?>
                    <tr class="border-t bg-gray-50">
                        <td class="py-3 px-12"><?= htmlspecialchars($subcategoria['nombre']) ?></td>
                        <td class="py-3 px-6">
                            <?php if (!empty($subcategoria['imagen'])) { ?>
                                <img src="<?= htmlspecialchars($subcategoria['imagen']) ?>" alt="<?= htmlspecialchars($subcategoria['nombre']) ?>" class="w-16 h-16 object-cover rounded">
                            <?php } else { ?>
                                <span class="text-gray-500">Sin imagen</span>
                            <?php } ?>
                        </td>
                        <td class="py-3 px-6 space-x-2">
                            <!-- Botón Editar Subcategoría -->
                            <button onclick="abrirModalEditarSubcategoria(<?= $subcategoria['id'] ?>, '<?= htmlspecialchars($subcategoria['nombre']) ?>', '<?= htmlspecialchars($subcategoria['categoria_id']) ?>', '<?= htmlspecialchars($subcategoria['imagen']) ?>')" class="bg-blue-500 text-white py-1 px-3 rounded hover:bg-blue-600">Editar</button>

                            <!-- Botón Eliminar Subcategoría -->
                            <button onclick="eliminarSubcategoria(<?= $subcategoria['id'] ?>)" class="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-600">Eliminar</button>
                        </td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal para Editar Categoría -->
<div id="modalEditarCategoria" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
        <h2 class="text-xl font-bold mb-4">Editar Categoría</h2>
        <form id="formEditarCategoria" enctype="multipart/form-data">
            <input type="hidden" id="editarIdCategoria" name="id">
            <div class="mb-4">
                <label for="editarNombreCategoria" class="block text-gray-700">Nombre</label>
                <input type="text" id="editarNombreCategoria" name="nombre_categoria" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="editarImagenCategoria" class="block text-gray-700">Imagen</label>
                <input type="file" id="editarImagenCategoria" name="imagen_categoria" class="w-full p-2 border border-gray-300 rounded">
                <div id="previewImagenCategoria" class="mt-2"></div>
            </div>
            <button type="button" onclick="guardarEdicionCategoria()" class="bg-blue-500 text-white py-2 px-4 rounded">Guardar cambios</button>
            <button type="button" onclick="cerrarModalCategoria()" class="bg-gray-500 text-white py-2 px-4 rounded">Cancelar</button>
        </form>
    </div>
</div>

<!-- Modal para Editar Subcategoría -->
<div id="modalEditarSubcategoria" class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/2">
        <h2 class="text-xl font-bold mb-4">Editar Subcategoría</h2>
        <form id="formEditarSubcategoria" enctype="multipart/form-data">
            <input type="hidden" id="editarIdSubcategoria" name="id">
            <div class="mb-4">
                <label for="editarNombreSubcategoria" class="block text-gray-700">Nombre</label>
                <input type="text" id="editarNombreSubcategoria" name="nombre_subcategoria" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="editarCategoriaSubcategoria" class="block text-gray-700">Categoría</label>
                <select id="editarCategoriaSubcategoria" name="categoria_subcategoria" class="w-full p-2 border border-gray-300 rounded">
                    <?php
                    // Obtener la categoría actual de la subcategoría que estamos editando
                    $id_subcategoria = isset($_GET['id']) ? $_GET['id'] : 0;  
                    if ($id_subcategoria > 0) {
                        $sql_subcategoria = "SELECT categoria_id FROM subcategorias WHERE id = ?";
                        $stmt = $conexion->prepare($sql_subcategoria);
                        $stmt->bind_param("i", $id_subcategoria);
                        $stmt->execute();
                        $resultado_subcategoria = $stmt->get_result();

                        if ($resultado_subcategoria->num_rows > 0) {
                            $subcategoria = $resultado_subcategoria->fetch_assoc();
                            $categoria_actual = $subcategoria['categoria_id'];
                        }
                    }

                    // Obtener las categorías disponibles
                    $sql_categorias = "SELECT * FROM categorias";
                    $resultado_categorias = $conexion->query($sql_categorias);
                    while ($categoria = $resultado_categorias->fetch_assoc()) {
                        $selected = ($categoria['id'] == $categoria_actual) ? "selected" : "";
                        echo "<option value='" . $categoria['id'] . "' $selected>" . htmlspecialchars($categoria['nombre']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="editarImagenSubcategoria" class="block text-gray-700">Imagen</label>
                <input type="file" id="editarImagenSubcategoria" name="imagen_subcategoria" class="w-full p-2 border border-gray-300 rounded">
                <div id="previewImagenSubcategoria" class="mt-2"></div>
            </div>
            <button type="button" onclick="guardarEdicionSubcategoria()" class="bg-blue-500 text-white py-2 px-4 rounded">Guardar cambios</button>
            <button type="button" onclick="cerrarModalSubcategoria()" class="bg-gray-500 text-white py-2 px-4 rounded">Cancelar</button>
        </form>
    </div>
</div>

<script>
// Función para abrir el modal de edición de categoría
function abrirModalEditar(id, nombre, imagen) {
    document.getElementById('editarIdCategoria').value = id;
    document.getElementById('editarNombreCategoria').value = nombre;
    document.getElementById('previewImagenCategoria').innerHTML = imagen ? `<img src="${imagen}" alt="${nombre}" class="w-16 h-16 object-cover rounded mt-2">` : '';
    document.getElementById('modalEditarCategoria').classList.remove('hidden');
}

// Función para cerrar el modal de categoría
function cerrarModalCategoria() {
    document.getElementById('modalEditarCategoria').classList.add('hidden');
}

// Función para guardar la edición de la categoría
function guardarEdicionCategoria() {
    const form = document.getElementById('formEditarCategoria');
    const formData = new FormData(form);

    fetch('./editar_categoria.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire("¡Éxito!", data.message, "success").then(() => {
                location.reload();
            });
        } else {
            Swal.fire("Error", data.message, "error");
        }
    })
    .catch(error => {
        Swal.fire("Error", "Hubo un problema al procesar la solicitud.", "error");
    });
}

// Función para abrir el modal de edición de subcategoría
function abrirModalEditarSubcategoria(id, nombre, categoriaId, imagen) {
    document.getElementById('editarIdSubcategoria').value = id;
    document.getElementById('editarNombreSubcategoria').value = nombre;
    document.getElementById('editarCategoriaSubcategoria').value = categoriaId;
    document.getElementById('previewImagenSubcategoria').innerHTML = imagen ? `<img src="${imagen}" alt="${nombre}" class="w-16 h-16 object-cover rounded mt-2">` : '';
    document.getElementById('modalEditarSubcategoria').classList.remove('hidden');
}

// Función para cerrar el modal de subcategoría
function cerrarModalSubcategoria() {
    document.getElementById('modalEditarSubcategoria').classList.add('hidden');
}

// Función para guardar la edición de la subcategoría
function guardarEdicionSubcategoria() {
    const form = document.getElementById('formEditarSubcategoria');
    const formData = new FormData(form);

    fetch('./editar_subcategoria.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire("¡Éxito!", data.message, "success").then(() => {
                location.reload();
            });
        } else {
            Swal.fire("Error", data.message, "error");
        }
    })
    .catch(error => {
        Swal.fire("Error", "Hubo un problema al procesar la solicitud.", "error");
    });
}

// Función para eliminar categoría
function eliminarCategoria(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción eliminará la categoría y todas sus subcategorías.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('eliminar_categoria.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('¡Eliminado!', data.message, 'success').then(() => {
                        location.reload();  // Recargar la página después de la eliminación
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Hubo un problema al procesar la solicitud.', 'error');
            });
        }
    });
}

// Función para eliminar subcategoría
function eliminarSubcategoria(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción eliminará la subcategoría.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('eliminar_subcategoria.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('¡Eliminado!', data.message, 'success').then(() => {
                        location.reload();  // Recargar la página después de la eliminación
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Hubo un problema al procesar la solicitud.', 'error');
            });
        }
    });
}


</script>
