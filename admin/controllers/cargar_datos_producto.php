<?php
include '../conexion/base_de_datos.php';

// Set headers first
header('Content-Type: application/json');

try {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        throw new Exception("ID de producto no proporcionado");
    }

    $stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta");
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Producto no encontrado");
    }
    
    $producto = $result->fetch_assoc();
    echo json_encode($producto);
    
} catch (Exception $e) {
    // Return error as JSON
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}

// Make sure nothing else is output after this
exit;