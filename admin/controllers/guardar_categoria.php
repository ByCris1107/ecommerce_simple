<?php
header('Content-Type: application/json; charset=utf-8');

include '../conexion/base_de_datos.php';

// Verificar que sea método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
    exit();
}

// Validar campos requeridos
if (empty($_POST['nombre_categoria']) || !isset($_FILES['imagen_categoria'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos necesarios."]);
    exit();
}

$nombre_categoria = trim($_POST['nombre_categoria']);
$imagen_categoria = $_FILES['imagen_categoria'];

// Validar nombre
if (strlen($nombre_categoria) < 2) {
    echo json_encode(["success" => false, "message" => "El nombre de la categoría es demasiado corto."]);
    exit();
}

// Validar archivo de imagen
$permitidos = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($imagen_categoria['type'], $permitidos)) {
    echo json_encode(["success" => false, "message" => "Solo se permiten imágenes JPG, PNG o WEBP."]);
    exit();
}


// Crear directorio si no existe
$directorio_destino = './uploads/imagen_categoria/';
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

// Insertar en la base de datos
$sql = "INSERT INTO categorias (nombre, imagen) VALUES (?, ?)";
$stmt = $conexion->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ss", $nombre_categoria, $ruta_para_bd);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Categoría guardada exitosamente."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al insertar en la base de datos."]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Error al preparar la consulta."]);
}
?>
