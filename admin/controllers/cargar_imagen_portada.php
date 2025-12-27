<?php
// Incluir la conexión a la base de datos
require_once '../conexion/base_de_datos.php';

// Obtener el ID del producto desde la petición AJAX
if (isset($_GET['id_producto'])) {
    $producto_id = $_GET['id_producto'];

    // Realizamos la consulta para obtener la imagen de portada
    $query = "SELECT imagen_portada FROM productos WHERE id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // Comprobar si el producto existe
    if ($producto = $resultado->fetch_assoc()) {
        $imagenPortada = $producto['imagen_portada'];
        $rutaImagen = "../admin/controllers/" . $imagenPortada;
        
        // Comprobar si la imagen existe en el directorio
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/web_ecommerce/admin/controllers/" . $imagenPortada)) {
            echo json_encode(['exito' => true, 'imagen' => $rutaImagen]);
        } else {
            echo json_encode(['exito' => false, 'mensaje' => 'Imagen no disponible']);
        }
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'Producto no encontrado']);
    }
} else {
    echo json_encode(['exito' => false, 'mensaje' => 'ID de producto no recibido']);
}
?>
