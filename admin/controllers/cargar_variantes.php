<?php
include '../conexion/base_de_datos.php';


header('Content-Type: application/json');

try {
    $id_producto = $_GET['id_producto'] ?? null;
    if (!$id_producto) {
        throw new Exception("ID de producto no proporcionado");
    }

    $stmt = $conexion->prepare("SELECT * FROM variantes_producto WHERE producto_id = ?");
    $stmt->bind_param("i", $id_producto);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $variantes = [];
    while ($row = $result->fetch_assoc()) {
        $variantes[] = $row;
    }
    
    echo json_encode($variantes);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}