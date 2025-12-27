<?php
require_once '../conexion/base_de_datos.php';

if (isset($_GET['id']) && isset($_GET['tipo'])) {
    $id = intval($_GET['id']);
    $tipo = $_GET['tipo'];
    $imagen = isset($_GET['imagen']) ? $_GET['imagen'] : '';

    // Determinar la tabla correspondiente según el tipo
    switch ($tipo) {
        case 'producto':
            $tabla = 'descuentos_productos';
            break;
        default:
            die('Tipo de descuento no válido.');
    }

    // Eliminar la imagen si existe
    if (!empty($imagen)) {
        $ruta_imagen = __DIR__ . '/uploads/descuentos/' . $imagen;
        if (file_exists($ruta_imagen)) {
            unlink($ruta_imagen);
        }
    }

    // Eliminar el descuento de la base de datos
    $stmt = $conexion->prepare("DELETE FROM $tabla WHERE id = ?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        header('Location: ../?module=descuentos&mensaje=eliminado');
        exit;
    } else {
        echo 'Error al eliminar el descuento.';
    }

    $stmt->close();
} else {
    echo 'Datos incompletos.';
}
?>
