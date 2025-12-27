<?php
include("./conexion/base_de_datos.php");

// Verificar que se ha enviado una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
    exit();
}

// Obtener el ID de la subcategoría y el nombre del formulario
$id_subcategoria = isset($_POST['id']) ? intval($_POST['id']) : 0;
$nombre_subcategoria = isset($_POST['nombre_subcategoria']) ? trim($_POST['nombre_subcategoria']) : '';
$imagen_subcategoria = isset($_FILES['imagen_subcategoria']) ? $_FILES['imagen_subcategoria'] : null;
$categoria_subcategoria = isset($_POST['categoria_subcategoria']) ? intval($_POST['categoria_subcategoria']) : 0;

// Validar campos requeridos
if (empty($id_subcategoria) || empty($nombre_subcategoria) || empty($categoria_subcategoria)) {
    echo json_encode(["success" => false, "message" => "Faltan datos necesarios."]);
    exit();
}

// Validar el nombre de la subcategoría
if (strlen($nombre_subcategoria) < 2) {
    echo json_encode(["success" => false, "message" => "El nombre de la subcategoría es demasiado corto."]);
    exit();
}

// Obtener la imagen actual de la subcategoría (antes de actualizar)
$sql = "SELECT imagen FROM subcategorias WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_subcategoria);
$stmt->execute();
$stmt->bind_result($imagen_actual);
$stmt->fetch();
$stmt->close();

// Si se sube una nueva imagen
if ($imagen_subcategoria && !empty($imagen_subcategoria['name'])) {
    // Validar archivo de imagen
    $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($imagen_subcategoria['type'], $permitidos)) {
        echo json_encode(["success" => false, "message" => "Solo se permiten imágenes JPG, PNG o WEBP."]);
        exit();
    }

    if ($imagen_subcategoria['size'] > 2 * 1024 * 1024) {
        echo json_encode(["success" => false, "message" => "La imagen no debe superar los 2MB."]);
        exit();
    }

    // Eliminar la imagen anterior si existe
    if ($imagen_actual && file_exists($imagen_actual)) {
        unlink($imagen_actual); // Elimina la imagen antigua
    }

    // Crear directorio si no existe
    $directorio_destino = './controllers/uploads/imagen_subcategoria/';
    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0775, true);
    }

    // Crear nombre único para la imagen
    $extension = pathinfo($imagen_subcategoria['name'], PATHINFO_EXTENSION);
    $nombre_imagen = uniqid('subcat_', true) . '.' . $extension;
    $ruta_guardada = $directorio_destino . $nombre_imagen;
    $ruta_para_bd = './controllers/uploads/imagen_subcategoria/' . $nombre_imagen;

    // Mover archivo
    if (!move_uploaded_file($imagen_subcategoria['tmp_name'], $ruta_guardada)) {
        echo json_encode(["success" => false, "message" => "Error al guardar la imagen."]);
        exit();
    }

    // Actualizar imagen en la base de datos
    $sql = "UPDATE subcategorias SET nombre = ?, categoria_id = ?, imagen = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sisi", $nombre_subcategoria, $categoria_subcategoria, $ruta_para_bd, $id_subcategoria);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Subcategoría actualizada exitosamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al actualizar en la base de datos."]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error al preparar la consulta."]);
    }
} else {
    // Si no se sube una nueva imagen, solo se actualiza el nombre y la categoría
    $sql = "UPDATE subcategorias SET nombre = ?, categoria_id = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sii", $nombre_subcategoria, $categoria_subcategoria, $id_subcategoria);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Subcategoría actualizada exitosamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al actualizar en la base de datos."]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error al preparar la consulta."]);
    }
}
?>
