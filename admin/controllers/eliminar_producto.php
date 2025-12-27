<?php
require_once '../conexion/base_de_datos.php';

// Mostrar errores para depuración (puedes quitar esto en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['id'])) {
    $id_producto = intval($_GET['id']);

    // Verificar si el producto existe
    $verificacion = $conexion->prepare("SELECT imagen_portada FROM productos WHERE id = ?");
    $verificacion->bind_param("i", $id_producto);
    $verificacion->execute();
    $resultado = $verificacion->get_result();

    if ($resultado->num_rows > 0) {
        $producto = $resultado->fetch_assoc();

        // Ruta base hacia las imágenes reales
        $base_ruta = __DIR__ . '/';

        // Eliminar imagen principal del producto si existe
        if (!empty($producto['imagen_portada'])) {
            $ruta_portada = $base_ruta . $producto['imagen_portada'];
            if (file_exists($ruta_portada)) {
                unlink($ruta_portada);
            }
        }

        // Eliminar imágenes de las variantes
        $stmt_variantes_img = $conexion->prepare("SELECT foto_color FROM variantes_producto WHERE producto_id = ?");
        $stmt_variantes_img->bind_param("i", $id_producto);
        $stmt_variantes_img->execute();
        $resultado_variantes = $stmt_variantes_img->get_result();

        while ($variante = $resultado_variantes->fetch_assoc()) {
            if (!empty($variante['foto_color'])) {
                $ruta_variante = $base_ruta . $variante['foto_color'];
                if (file_exists($ruta_variante)) {
                    unlink($ruta_variante);
                }
            }
        }

        // Eliminar variantes asociadas
        $stmt_variantes = $conexion->prepare("DELETE FROM variantes_producto WHERE producto_id = ?");
        $stmt_variantes->bind_param("i", $id_producto);
        $stmt_variantes->execute();

        // Eliminar el producto
        $stmt_producto = $conexion->prepare("DELETE FROM productos WHERE id = ?");
        $stmt_producto->bind_param("i", $id_producto);
        $stmt_producto->execute();

        header("Location: ../?module=ver_producto&eliminado=1");
        exit;
    } else {
        header("Location: ../?module=ver_producto&error=producto_no_encontrado");
        exit;
    }
} else {
    header("Location: ../?module=ver_producto&error=id_invalido");
    exit;
}
?>
