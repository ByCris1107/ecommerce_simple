<?php

$id = $_GET['id'] ?? null;
$tipo = $_GET['tipo'] ?? null;

if (!$id || !$tipo) {
    echo "Parámetros inválidos.";
    exit;
}

switch ($tipo) {
    case 'producto':
        $tabla = 'descuentos_productos';
        break;
    case 'categoria':
        $tabla = 'descuentos_categoria';
        break;
    case 'subcategoria':
        $tabla = 'descuentos_subcategoria';
        break;
    default:
        echo "Tipo no válido.";
        exit;
}

$sql = "SELECT * FROM $tabla WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "No se encontró el descuento.";
    exit;
}

$descuento = $resultado->fetch_assoc();
?>


<!-- Formulario con mejor estilo y AJAX -->
<div class="max-w-xl mx-auto mt-10 bg-white shadow-lg rounded-xl p-6">
    <h2 class="text-3xl font-bold text-center text-blue-700 mb-6">Editar Descuento</h2>
    
    <form id="form-editar-descuento" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="id" value="<?php echo $descuento['id']; ?>">
        <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">

        <div>
            <label class="text-gray-700 font-semibold">Porcentaje de descuento</label>
            <input type="number" name="porcentaje_descuento" min="1" max="100" required 
                value="<?php echo $descuento['porcentaje_descuento']; ?>"
                class="w-full border border-gray-300 rounded-lg px-4 py-2 mt-1 focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <div>
            <label class="text-gray-700 font-semibold block">Imagen actual</label>
            <?php if (!empty($descuento['imagen_descuento'])): ?>
                <img src="./controllers/uploads/descuentos/<?php echo $descuento['imagen_descuento']; ?>" 
                     class="w-28 h-28 object-cover rounded-lg border border-gray-300 mt-2">
            <?php else: ?>
                <p class="text-sm text-gray-500 mt-2">Sin imagen</p>
            <?php endif; ?>
        </div>

        <div>
            <label class="text-gray-700 font-semibold block">Nueva imagen (opcional)</label>
            <input type="file" name="nueva_imagen" class="mt-2 w-full border border-gray-300 rounded-lg px-4 py-2">
        </div>

        <div class="text-center">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition">
                Guardar cambios
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('form-editar-descuento').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('./controllers/actualizar_descuento.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.message,
                confirmButtonColor: '#3085d6',
                timer: 2000
            }).then(() => {
                location.reload(); // o redireccionar a otra parte si querés
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#d33'
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: 'error',
            title: 'Error inesperado',
            text: 'No se pudo actualizar el descuento.',
            confirmButtonColor: '#d33'
        });
        console.error(err);
    });
});
</script>
