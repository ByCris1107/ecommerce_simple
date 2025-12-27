<form id="formularioProducto" action="./controllers/cargar_producto.php" method="POST" enctype="multipart/form-data" class="max-w-3xl mx-auto p-6 bg-white shadow-md rounded-lg border border-gray-200">
    <h2 class="text-3xl font-semibold mb-8 text-center text-gray-800">Cargar nuevo producto</h2>

    <!-- Nombre del producto -->
    <div class="mb-6">
        <label for="nombre_producto" class="block text-lg font-medium text-gray-700 mb-2">Nombre del producto</label>
        <input type="text" id="nombre_producto" name="nombre_producto" required class="w-full p-4 border border-gray-300 rounded-lg text-gray-700" placeholder="Ej: Remera oversize blanca">
    </div>

    <!-- Género -->
    <div class="mb-6">
        <label for="genero_producto" class="block text-lg font-medium text-gray-700 mb-2">Género</label>
        <select id="genero_producto" name="genero_producto" required class="w-full p-4 border border-gray-300 rounded-lg text-gray-700" onchange="actualizarSubcategorias()">
            <option value="" disabled selected>Seleccionar género</option>
            <option value="Hombre">Hombre</option>
            <option value="Mujer">Mujer</option>
            <option value="Unisex">Unisex</option>
            <option value="Niños">Niños</option>
            <option value="Niñas">Niñas</option>
            <option value="Bebés">Bebés</option>
            <option value="Accesorios">Accesorios</option>
        </select>
    </div>

<!-- Categoría -->
<div class="mb-6">
    <?php 

$categoria_id = isset($_GET['categoria_id']) ? intval($_GET['categoria_id']) : 0;

$sql = "SELECT id, nombre FROM subcategorias WHERE categoria_id = ? ORDER BY nombre ASC";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $categoria_id);
$stmt->execute();
$resultado = $stmt->get_result();

$subcategorias = [];

while ($fila = $resultado->fetch_assoc()) {
    $subcategorias[] = $fila;
}

?>
    <label for="categoria" class="block text-lg font-medium text-gray-700 mb-2">Categoría</label>
    <select id="categoria" name="categoria" required class="w-full p-4 border border-gray-300 rounded-lg text-gray-700" onchange="cargarSubcategorias()">
        <option value="">-- Seleccionar categoría --</option>
        <?php
        $consulta = $conexion->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
        while ($fila = $consulta->fetch_assoc()):
        ?>
            <option value="<?= $fila['id'] ?>"><?= htmlspecialchars($fila['nombre']) ?></option>
        <?php endwhile; ?>
    </select>
</div>

<!-- Subcategoría -->
<div class="mb-6">
    <label for="subcategoria" class="block text-lg font-medium text-gray-700 mb-2">Subcategoría</label>
    <select id="subcategoria" name="subcategoria" required class="w-full p-4 border border-gray-300 rounded-lg text-gray-700">
        <option value="">-- Seleccioná una categoría primero --</option>
    </select>
</div>

<script>
function cargarSubcategorias() {
    const categoriaId = document.getElementById('categoria').value;
    const selectSubcategoria = document.getElementById('subcategoria');

    // Limpiar subcategorías anteriores
    selectSubcategoria.innerHTML = '<option value="">Cargando subcategorías...</option>';

    if (categoriaId === '') {
        selectSubcategoria.innerHTML = '<option value="">-- Seleccioná una categoría primero --</option>';
        return;
    }

    fetch(`./controllers/obtener_subcategoria_producto.php?categoria_id=${categoriaId}`)
        .then(response => response.json())
        .then(data => {
            selectSubcategoria.innerHTML = '<option value="">-- Seleccionar subcategoría --</option>';
            data.forEach(sub => {
                const option = document.createElement('option');
                option.value = sub.id;
                option.textContent = sub.nombre;
                selectSubcategoria.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error al cargar subcategorías:', error);
            selectSubcategoria.innerHTML = '<option value="">Error al cargar subcategorías</option>';
        });
}
</script>



<!-- Detalles: Talle, Color, Stock, Imagen -->
<div class="mb-8">
    <label class="block text-lg font-medium text-gray-800 mb-4">Talles, colores, stock e imagen</label>
    <div id="contenedorDetalles">
        <div class="flex flex-wrap items-center gap-6 mb-6 fila-detalle-producto p-4 bg-white shadow-md rounded-lg border border-gray-200">
            <input type="text" name="talle[]" class="w-1/5 p-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Talle (Ej: S, M, 40)" required>
            <input type="text" name="color[]" class="w-1/5 p-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Color (Ej: Negro)" required>
            <input type="number" name="stock[]" class="w-1/5 p-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Cantidad" required>
            
            <div class="relative w-1/4">
                <input type="file" name="foto_color[]" accept="image/*" class="w-full p-2 border border-gray-300 rounded-lg text-gray-700 cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="mostrarImagen(event)">
                <div class="preview-imagen mt-2 hidden w-full h-auto rounded-lg overflow-hidden">
                    <img id="imagenPreview" src="" alt="Vista previa" class="w-full h-auto object-cover rounded-lg border border-gray-300 shadow-md"/>
                </div>
            </div>
            
            <button type="button" class="eliminar-fila bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">Eliminar</button>
        </div>
    </div>

    <button type="button" id="agregarFila" class="mt-4 px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">Agregar otra variante</button>
</div>

<script>
    // Función para mostrar la imagen seleccionada
    function mostrarImagen(event) {
        const archivo = event.target.files[0];
        const vistaPrevia = event.target.closest('.fila-detalle-producto').querySelector('.preview-imagen img');
        const contenedorVistaPrevia = event.target.closest('.fila-detalle-producto').querySelector('.preview-imagen');
        
        if (archivo) {
            const lector = new FileReader();
            lector.onload = function(e) {
                vistaPrevia.src = e.target.result;
                contenedorVistaPrevia.classList.remove('hidden');  // Mostrar la imagen
            };
            lector.readAsDataURL(archivo);
        }
    }

    // Funcionalidad para agregar nuevas variantes
    document.getElementById('agregarFila').addEventListener('click', function() {
        const contenedorDetalles = document.getElementById('contenedorDetalles');
        const nuevaFila = document.createElement('div');
        nuevaFila.classList.add('flex', 'flex-wrap', 'items-center', 'gap-6', 'mb-6', 'fila-detalle-producto', 'p-4', 'bg-white', 'shadow-md', 'rounded-lg', 'border', 'border-gray-200');

        nuevaFila.innerHTML = `
            <input type="text" name="talle[]" class="w-1/5 p-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Talle (Ej: S, M, 40)" required>
            <input type="text" name="color[]" class="w-1/5 p-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Color (Ej: Negro)" required>
            <input type="number" name="stock[]" class="w-1/5 p-3 border border-gray-300 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Cantidad" required>
            
            <div class="relative w-1/4">
                <input type="file" name="foto_color[]" accept="image/*" class="w-full p-2 border border-gray-300 rounded-lg text-gray-700 cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="mostrarImagen(event)">
                <div class="preview-imagen mt-2 hidden w-full h-auto rounded-lg overflow-hidden">
                    <img id="imagenPreview" src="" alt="Vista previa" class="w-full h-auto object-cover rounded-lg border border-gray-300 shadow-md"/>
                </div>
            </div>
            
            <button type="button" class="eliminar-fila bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">Eliminar</button>
        `;
        
        contenedorDetalles.appendChild(nuevaFila);

        // Evento para eliminar la fila
        nuevaFila.querySelector('.eliminar-fila').addEventListener('click', function() {
            contenedorDetalles.removeChild(nuevaFila);
        });
    });
</script>



    <!-- Estaciones -->
    <div class="mb-6">
        <label class="block text-lg font-medium text-gray-700 mb-2">¿Para qué estación del año es?</label>
        <div class="grid grid-cols-2 gap-4">
            <label class="flex items-center"><input type="checkbox" name="estaciones[]" value="Primavera" class="mr-2"> Primavera</label>
            <label class="flex items-center"><input type="checkbox" name="estaciones[]" value="Verano" class="mr-2"> Verano</label>
            <label class="flex items-center"><input type="checkbox" name="estaciones[]" value="Otoño" class="mr-2"> Otoño</label>
            <label class="flex items-center"><input type="checkbox" name="estaciones[]" value="Invierno" class="mr-2"> Invierno</label>
        </div>
        <p class="text-sm text-gray-500 mt-2">Podés elegir una o varias estaciones según corresponda.</p>
    </div>

    <!-- Descripción -->
    <div class="mb-6">
        <label for="descripcion" class="block text-lg font-medium text-gray-700 mb-2">Descripción del producto</label>
        <textarea id="descripcion" name="descripcion" rows="4" required class="w-full p-4 border border-gray-300 rounded-lg text-gray-700" placeholder="Ej: Buzo con capucha de algodón, ideal para el invierno..."></textarea>
    </div>

    <!-- Imagen principal -->
    <div class="mb-6">
        <label for="imagen_portada" class="block text-lg font-medium text-gray-700 mb-2">Imagen principal del producto</label>
        <input type="file" id="imagen_portada" name="imagen_portada" accept="image/*" required class="w-full p-4 border border-gray-300 rounded-lg text-gray-700">
    </div>

    <!-- Precio -->
    <div class="mb-6">
        <label for="precio" class="block text-lg font-medium text-gray-700 mb-2">Precio</label>
        <input type="number" id="precio" name="precio" step="0.01" required class="w-full p-4 border border-gray-300 rounded-lg text-gray-700" placeholder="Ej: 19999.99">
    </div>

    <!-- Botón enviar -->
    <div class="text-center">
        <button type="submit" class="w-full py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">Cargar producto</button>
    </div>
</form>


