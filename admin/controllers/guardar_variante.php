<?php
require '../conexion/base_de_datos.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto_id = $_POST['producto_id'] ?? null;
    $color = $_POST['color'] ?? null;
    $talle = $_POST['talle'] ?? null;
    $stock = $_POST['stock'] ?? null;


    // Validar campos obligatorios
    if (!$producto_id || !$color || !$stock || !$talle || !isset($_FILES['foto_color'])) {
        echo "Faltan datos obligatorios.";
        exit;
    }

    // Procesar imagen
    $foto_color = $_FILES['foto_color'];
    $nombre_temporal = $foto_color['tmp_name'];
    $nombre_original = basename($foto_color['name']);

    $ruta_destino = './uploads/imagenes_productos/variantes/' . $nombre_original;

    if (!move_uploaded_file($nombre_temporal, $ruta_destino)) {
        echo "Error al subir la imagen.";
        exit;
    }

    // Ruta que se guardará en la base de datos (sin el './' para que quede relativa desde la raíz)
    $ruta_para_db = 'uploads/imagenes_productos/variantes/' . $nombre_original;

    // Insertar variante en la base de datos
    $consulta = $conexion->prepare("INSERT INTO variantes_producto (producto_id, color, talle, stock, foto_color) VALUES (?, ?, ?, ?, ?)");
    $resultado = $consulta->execute([$producto_id, $color, $talle, $stock, $ruta_para_db]);

    if ($resultado) {
        header("Location: ../?module=ver_producto&id=$producto_id&mensaje=variante_agregada");
        exit;
    } else {
        echo "Error al guardar la variante.";
    }
} else {
    echo "Acceso no permitido.";
}
?>
