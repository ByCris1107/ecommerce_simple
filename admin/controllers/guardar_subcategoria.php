<?php
include '../conexion/base_de_datos.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
    exit();
}

$categoria_id = isset($_POST['categoria_id']) ? intval($_POST['categoria_id']) : 0;
$nombre_subcategoria = isset($_POST['nombre_subcategoria']) ? trim($_POST['nombre_subcategoria']) : '';
$imagen_subcategoria = isset($_FILES['imagen_subcategoria']) ? $_FILES['imagen_subcategoria'] : null;

if (empty($categoria_id) || empty($nombre_subcategoria)) {
    echo json_encode(["success" => false, "message" => "El nombre de la subcategoría y la categoría son obligatorios."]);
    exit();
}

// Validar si ya existe
$consultaExistente = $conexion->prepare("SELECT id FROM subcategorias WHERE nombre = ? AND categoria_id = ?");
$consultaExistente->bind_param("si", $nombre_subcategoria, $categoria_id);
$consultaExistente->execute();
$consultaExistente->store_result();

if ($consultaExistente->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "La subcategoría ya existe en esta categoría."]);
    $consultaExistente->close();
    exit();
}
$consultaExistente->close();

// Validar y subir imagen si existe
$ruta_para_bd = null;

if ($imagen_subcategoria && $imagen_subcategoria["error"] === UPLOAD_ERR_OK) {
    $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($imagen_subcategoria['type'], $permitidos)) {
        echo json_encode(["success" => false, "message" => "Solo se permiten imágenes JPG, PNG o WEBP."]);
        exit();
    }

    if ($imagen_subcategoria['size'] > 2 * 1024 * 1024) {
        echo json_encode(["success" => false, "message" => "La imagen no debe superar los 2MB."]);
        exit();
    }

    $directorio_destino = './uploads/imagen_subcategoria/';
    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0775, true);
    }

    $extension = pathinfo($imagen_subcategoria['name'], PATHINFO_EXTENSION);
    $nombre_imagen = uniqid('subcat_', true) . '.' . $extension;
    $ruta_guardada = $directorio_destino . $nombre_imagen;
    $ruta_para_bd = './controllers/uploads/imagen_subcategoria/' . $nombre_imagen;

    // Validar imagen real
    $verificar_imagen = getimagesize($imagen_subcategoria["tmp_name"]);
    if ($verificar_imagen === false) {
        echo json_encode(["success" => false, "message" => "El archivo no es una imagen válida."]);
        exit();
    }

    if (!move_uploaded_file($imagen_subcategoria["tmp_name"], $ruta_guardada)) {
        echo json_encode(["success" => false, "message" => "Error al guardar la imagen."]);
        exit();
    }
}

// Insertar subcategoría
$insertar = $conexion->prepare("INSERT INTO subcategorias (categoria_id, nombre, imagen) VALUES (?, ?, ?)");
$insertar->bind_param("iss", $categoria_id, $nombre_subcategoria, $ruta_para_bd);

if ($insertar->execute()) {
    echo json_encode(["success" => true, "message" => "Subcategoría guardada exitosamente."]);
} else {
    echo json_encode(["success" => false, "message" => "Error al guardar la subcategoría: " . $insertar->error]);
}

$insertar->close();
$conexion->close();
?>
