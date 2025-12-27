<?php
include("./conexion/base_de_datos.php");

// Verificar que se ha enviado una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
    exit();
}

// Obtener el ID de la categoría y el nombre del formulario
$id_categoria = isset($_POST['id']) ? intval($_POST['id']) : 0;
$nombre_categoria = isset($_POST['nombre_categoria']) ? trim($_POST['nombre_categoria']) : '';
$imagen_categoria = isset($_FILES['imagen_categoria']) ? $_FILES['imagen_categoria'] : null;

// Validar campos requeridos
if (empty($id_categoria) || empty($nombre_categoria)) {
    echo json_encode(["success" => false, "message" => "Faltan datos necesarios."]);
    exit();
}

// Validar el nombre de la categoría
if (strlen($nombre_categoria) < 2) {
    echo json_encode(["success" => false, "message" => "El nombre de la categoría es demasiado corto."]);
    exit();
}

// Obtener la imagen actual de la categoría (antes de actualizar)
$sql = "SELECT imagen FROM categorias WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_categoria);
$stmt->execute();
$stmt->bind_result($imagen_actual);
$stmt->fetch();
$stmt->close();

// Si se sube una nueva imagen
if ($imagen_categoria && !empty($imagen_categoria['name'])) {
    // Validar archivo de imagen
    $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($imagen_categoria['type'], $permitidos)) {
        echo json_encode(["success" => false, "message" => "Solo se permiten imágenes JPG, PNG o WEBP."]);
        exit();
    }

    if ($imagen_categoria['size'] > 2 * 1024 * 1024) {
        echo json_encode(["success" => false, "message" => "La imagen no debe superar los 2MB."]);
        exit();
    }

    // Eliminar la imagen anterior si existe
    if ($imagen_actual && file_exists($imagen_actual)) {
        unlink($imagen_actual); // Elimina la imagen antigua
    }

    // Crear directorio si no existe
    $directorio_destino = './controllers/uploads/imagen_categoria/';
    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0775, true);
    }

    // Crear nombre único para la imagen
    $extension = pathinfo($imagen_categoria['name'], PATHINFO_EXTENSION);
    $nombre_imagen = uniqid('cat_', true) . '.' . $extension;
    $ruta_guardada = $directorio_destino . $nombre_imagen;
    $ruta_para_bd = './controllers/uploads/imagen_categoria/' . $nombre_imagen;

    // Mover archivo
    if (!move_uploaded_file($imagen_categoria['tmp_name'], $ruta_guardada)) {
        echo json_encode(["success" => false, "message" => "Error al guardar la imagen."]);
        exit();
    }

    // Actualizar imagen en la base de datos
    $sql = "UPDATE categorias SET nombre = ?, imagen = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssi", $nombre_categoria, $ruta_para_bd, $id_categoria);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Categoría actualizada exitosamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al actualizar en la base de datos."]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error al preparar la consulta."]);
    }
} else {
    // Si no se sube una nueva imagen, solo se actualiza el nombre
    $sql = "UPDATE categorias SET nombre = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("si", $nombre_categoria, $id_categoria);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Categoría actualizada exitosamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al actualizar en la base de datos."]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Error al preparar la consulta."]);
    }
}
?>
