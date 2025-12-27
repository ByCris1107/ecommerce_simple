<?php
// eliminar_variante.php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Leer JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || empty($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de variante requerido']);
    exit;
}

$id_variante = intval($input['id']);

require_once '../conexion/base_de_datos.php'; // Ajusta la ruta según corresponda

// Consultamos si la variante tiene imagen para eliminar el archivo físico
$stmt = $conexion->prepare("SELECT foto_color FROM variantes_producto WHERE id = ?");
$stmt->bind_param("i", $id_variante);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $foto = $row['foto_color'];
    if ($foto) {
        // Ruta absoluta de la imagen
        $ruta_imagen = __DIR__ . '/' . $foto;
        if (file_exists($ruta_imagen)) {
            unlink($ruta_imagen);
        }
    }
}
$stmt->close();

// Eliminamos la variante de la base de datos
$stmt = $conexion->prepare("DELETE FROM variantes_producto WHERE id = ?");
$stmt->bind_param("i", $id_variante);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Variante eliminada correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la variante']);
}
$stmt->close();
$conexion->close();
