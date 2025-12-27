<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar que todos los campos requeridos están presentes
    if (!isset($_POST['tipo_descuento'], $_POST['porcentaje_descuento'])) {
        die("Faltan campos requeridos");
    }

    $tipo_descuento = $_POST['tipo_descuento'];
    $porcentaje_descuento = $_POST['porcentaje_descuento'];
    $imagen_descuento = null;

    // Procesar imagen si se subió
    if (isset($_FILES['imagen_descuento']) && $_FILES['imagen_descuento']['error'] === UPLOAD_ERR_OK) {
        $directorio = './controllers/uploads/descuentos/';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        
        $nombre_archivo = basename($_FILES['imagen_descuento']['name']);
        $ruta_imagen_descuento = $directorio . $nombre_archivo;
        
        if (move_uploaded_file($_FILES['imagen_descuento']['tmp_name'], $ruta_imagen_descuento)) {
            $imagen_descuento = $nombre_archivo;
        }
    }

    // Validar campos según el tipo de descuento
    $error = false;
    $stmt = null;
    
    if ($tipo_descuento === 'producto') {
        if (!isset($_POST['producto_id'])) {
            $error = true;
        } else {
            $producto_id = $_POST['producto_id'];
            $sql = "INSERT INTO descuentos_productos (producto_id, porcentaje_descuento, imagen_descuento) VALUES (?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("iis", $producto_id, $porcentaje_descuento, $imagen_descuento);
        }
    
    } else {
        $error = true;
    }

    if (!$error && $stmt && $stmt->execute()) {
        echo "<script>
                Swal.fire({
                    title: 'Éxito!',
                    text: 'Descuento agregado correctamente.',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = './?module=subir_descuento';
                    }
                });
              </script>";
    } else {
        echo "<script>
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al agregar el descuento. Verifica que todos los campos estén completos.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
              </script>";
    }
}
?>

<form action="" method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded-lg shadow-lg space-y-6">
    <!-- Seleccionar tipo de descuento -->
    <div class="mb-6">
    <label for="tipo_descuento" class="block text-sm font-medium text-gray-700">Tipo de Descuento</label>
    <select id="tipo_descuento" name="tipo_descuento" required readonly class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 bg-gray-100 cursor-not-allowed">
        <option value="producto" selected>Producto</option>
    </select>
</div>


    <!-- Seleccionar categoria o producto según el tipo -->
    <div class="mb-6" id="campoSeleccion">
        <!-- Se actualizará dinámicamente -->
    </div>

    <!-- Porcentaje de descuento -->
    <div class="mb-6">
        <label for="porcentaje_descuento" class="block text-sm font-medium text-gray-700">Porcentaje de Descuento</label>
        <input type="number" id="porcentaje_descuento" name="porcentaje_descuento" min="1" max="100" required class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <!-- Imagen opcional -->
    <div class="mb-6">
        <label for="imagen_descuento" class="block text-sm font-medium text-gray-700">Imagen de Descuento</label>
        <input type="file" id="imagen_descuento" name="imagen_descuento" class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <!-- Botón de envío -->
    <div class="flex justify-center">
        <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-indigo-700 transition-all duration-300 transform hover:scale-105">Agregar Descuento</button>
    </div>
</form>

<script>
// Función para actualizar los campos de selección
function actualizarCamposSeleccion() {
    let tipoSeleccionado = document.getElementById('tipo_descuento').value;
    let campoSeleccion = document.getElementById('campoSeleccion');
    
    campoSeleccion.innerHTML = '';

    if (tipoSeleccionado === 'producto') {
        campoSeleccion.innerHTML = `
            <label for="producto_id" class="block text-sm font-medium text-gray-700">Selecciona un Producto</label>
            <select id="producto_id" name="producto_id" required class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Selecciona un producto</option>
                <?php
                $sql = "SELECT id, nombre FROM productos";
                $stmt = $conexion->prepare($sql);
                $stmt->execute();
                $resultado = $stmt->get_result();
                while ($producto = $resultado->fetch_assoc()) {
                    echo "<option value='" . $producto['id'] . "'>" . htmlspecialchars($producto['nombre']) . "</option>";
                }
                ?>
            </select>
        `;
    }
}

// Ejecutar al cargar la página y cuando cambie el select
document.addEventListener('DOMContentLoaded', actualizarCamposSeleccion);
document.getElementById('tipo_descuento').addEventListener('change', actualizarCamposSeleccion);
</script>