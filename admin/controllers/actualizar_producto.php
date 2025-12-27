<?php
ob_start(); // <-- Captura cualquier salida inesperada

require_once '../conexion/base_de_datos.php';
header('Content-Type: application/json');

// Configuraciones
$uploadDirPortada = './uploads/imagenes_productos/portada/';
$uploadDirVariantes = './uploads/imagenes_productos/variantes/';
$dbPathPortada = 'uploads/imagenes_productos/portada/';
$dbPathVariantes = 'uploads/imagenes_productos/variantes/';

$response = ['success' => false, 'message' => 'Error desconocido'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido");
    }

    $id = $_GET['id'] ?? null;
    if (!$id || !is_numeric($id)) {
        throw new Exception("ID de producto no válido");
    }

    // Validar campos obligatorios
    $campos = ['nombre_producto', 'genero_producto', 'categoria', 'precio'];
    foreach ($campos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo '$campo' es obligatorio.");
        }
    }

    // Variables básicas
    $nombre = trim($_POST['nombre_producto']);
    $genero = trim($_POST['genero_producto']);
    $categoria_id = (int) $_POST['categoria'];
    $subcategoria_id = !empty($_POST['subcategoria']) ? (int) $_POST['subcategoria'] : null;
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = (float) $_POST['precio'];
    $estaciones = isset($_POST['estaciones']) ? implode(', ', $_POST['estaciones']) : '';

    // --- Imagen de portada
    $nueva_imagen_portada = null;
    if (!empty($_FILES['imagen_portada']['name'])) {
        $file = $_FILES['imagen_portada'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $permitidas)) {
            throw new Exception("Formato de imagen no válido.");
        }

        $filename = uniqid() . '-' . basename($file['name']);
        $destino = $uploadDirPortada . $filename;

        // Obtener imagen anterior
        $stmt = $conexion->prepare("SELECT imagen_portada FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $imagen_anterior = $stmt->get_result()->fetch_assoc()['imagen_portada'] ?? null;

        if (move_uploaded_file($file['tmp_name'], $destino)) {
            $nueva_imagen_portada = $dbPathPortada . $filename;

            // Eliminar anterior
            if ($imagen_anterior && file_exists('./' . $imagen_anterior)) {
                unlink('./' . $imagen_anterior);
            }
        } else {
            throw new Exception("Error al subir la imagen de portada.");
        }
    }

    // --- Actualizar producto
    $query = "UPDATE productos SET nombre=?, genero=?, categoria_id=?, subcategoria_id=?, descripcion=?, precio=?, estaciones=?";
    if ($nueva_imagen_portada) $query .= ", imagen_portada=?";
    $query .= " WHERE id=?";

    $stmt = $conexion->prepare($query);
    if ($nueva_imagen_portada) {
        $stmt->bind_param("ssisssssi", $nombre, $genero, $categoria_id, $subcategoria_id, $descripcion, $precio, $estaciones, $nueva_imagen_portada, $id);
    } else {
        $stmt->bind_param("ssissssi", $nombre, $genero, $categoria_id, $subcategoria_id, $descripcion, $precio, $estaciones, $id);
    }

    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar el producto: " . $stmt->error);
    }

    // --- Eliminar variantes si corresponde
    if (!empty($_POST['variantes_a_eliminar'])) {
        foreach ($_POST['variantes_a_eliminar'] as $vid) {
            if (!is_numeric($vid)) continue;

            $stmt = $conexion->prepare("SELECT foto_color FROM variantes_producto WHERE id = ?");
            $stmt->bind_param("i", $vid);
            $stmt->execute();
            $img = $stmt->get_result()->fetch_assoc()['foto_color'] ?? null;
            if ($img && file_exists('./' . $img)) unlink('./' . $img);

            $stmt = $conexion->prepare("DELETE FROM variantes_producto WHERE id = ?");
            $stmt->bind_param("i", $vid);
            $stmt->execute();
        }
    }

    // --- Variantes nuevas o existentes
    $talles = $_POST['talle'] ?? [];
    $colores = $_POST['color'] ?? [];
    $stocks = $_POST['stock'] ?? [];
    $ids_variantes = $_POST['variante_id'] ?? [];

    for ($i = 0; $i < count($talles); $i++) {
        $talle = $talles[$i];
        $color = $colores[$i];
        $stock = (int) $stocks[$i];
        $var_id = $ids_variantes[$i] ?? null;

        $foto_color_db = null;

        if (!empty($_FILES['foto_color']['name'][$i])) {
            $file = [
                'name' => $_FILES['foto_color']['name'][$i],
                'tmp_name' => $_FILES['foto_color']['tmp_name'][$i],
                'error' => $_FILES['foto_color']['error'][$i]
            ];

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = uniqid() . '-' . basename($file['name']);
            $destino = $uploadDirVariantes . $filename;

            if (move_uploaded_file($file['tmp_name'], $destino)) {
                $foto_color_db = $dbPathVariantes . $filename;

                if ($var_id) {
                    $stmt = $conexion->prepare("SELECT foto_color FROM variantes_producto WHERE id = ?");
                    $stmt->bind_param("i", $var_id);
                    $stmt->execute();
                    $vieja = $stmt->get_result()->fetch_assoc()['foto_color'] ?? null;
                    if ($vieja && file_exists('./' . $vieja)) unlink('./' . $vieja);
                }
            }
        } elseif ($var_id) {
            $stmt = $conexion->prepare("SELECT foto_color FROM variantes_producto WHERE id = ?");
            $stmt->bind_param("i", $var_id);
            $stmt->execute();
            $foto_color_db = $stmt->get_result()->fetch_assoc()['foto_color'] ?? null;
        }

        if ($var_id) {
            $sql = "UPDATE variantes_producto SET talle=?, color=?, stock=?";
            if ($foto_color_db) $sql .= ", foto_color=?";
            $sql .= " WHERE id=?";

            $stmt = $conexion->prepare($sql);
            if ($foto_color_db) {
                $stmt->bind_param("ssisi", $talle, $color, $stock, $foto_color_db, $var_id);
            } else {
                $stmt->bind_param("ssii", $talle, $color, $stock, $var_id);
            }
            $stmt->execute();
        } else {
            $stmt = $conexion->prepare("INSERT INTO variantes_producto (producto_id, talle, color, stock, foto_color) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issis", $id, $talle, $color, $stock, $foto_color_db);
            $stmt->execute();
        }
    }

    $response = ['success' => true, 'message' => 'Producto actualizado con éxito'];

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Captura cualquier salida inesperada (como errores o warnings)
$salida = ob_get_clean();
if (!empty($salida)) {
    $response['debug'] = trim($salida);
}

echo json_encode($response);
exit;
