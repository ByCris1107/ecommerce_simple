<?php
header('Content-Type: application/json');
include('../conexion/base_de_datos.php');

$id = $_POST['id'] ?? null;
$tipo = $_POST['tipo'] ?? null;
$porcentaje_descuento = $_POST['porcentaje_descuento'] ?? null;

if (!$id || !$tipo || !$porcentaje_descuento) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan datos obligatorios.'
    ]);
    exit;
}

// Definir tabla según tipo
switch ($tipo) {
    case 'producto':
        $tabla = 'descuentos_productos';
        break;
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Tipo de descuento no válido.'
        ]);
        exit;
}

// Obtener imagen actual para eliminar si se reemplaza
$sql_img = "SELECT imagen_descuento FROM $tabla WHERE id = ?";
$stmt_img = $conexion->prepare($sql_img);
$stmt_img->bind_param("i", $id);
$stmt_img->execute();
$resultado = $stmt_img->get_result();
$datos_actuales = $resultado->fetch_assoc();
$imagen_actual = $datos_actuales['imagen_descuento'] ?? null;

// Procesar imagen nueva
$nueva_imagen = $_FILES['nueva_imagen'] ?? null;
$nombre_archivo = $imagen_actual;

if ($nueva_imagen && $nueva_imagen['error'] === UPLOAD_ERR_OK) {
    $directorio = "../controllers/uploads/descuentos/";
    $extension = pathinfo($nueva_imagen['name'], PATHINFO_EXTENSION);
    $nombre_archivo = uniqid('descuento_', true) . '.' . $extension;
    $ruta_destino = $directorio . $nombre_archivo;

    // Subir nueva imagen
    if (move_uploaded_file($nueva_imagen['tmp_name'], $ruta_destino)) {
        // Eliminar imagen anterior si existe
        if ($imagen_actual && file_exists($directorio . $imagen_actual)) {
            unlink($directorio . $imagen_actual);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al subir la nueva imagen.'
        ]);
        exit;
    }
}

// Actualizar datos
$sql_update = "UPDATE $tabla SET porcentaje_descuento = ?, imagen_descuento = ? WHERE id = ?";
$stmt = $conexion->prepare($sql_update);
$stmt->bind_param("ssi", $porcentaje_descuento, $nombre_archivo, $id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Descuento actualizado correctamente.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo actualizar el descuento.'
    ]);
}
