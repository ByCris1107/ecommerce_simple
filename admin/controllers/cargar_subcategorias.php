<?php
include '../conexion/base_de_datos.php';


header('Content-Type: application/json');

try {
    $categoria_id = $_GET['categoria_id'] ?? null;
    if (!$categoria_id) {
        throw new Exception("ID de categoría no proporcionado");
    }

    $stmt = $conexion->prepare("SELECT * FROM subcategorias WHERE categoria_id = ? ORDER BY nombre");
    $stmt->bind_param("i", $categoria_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subcategorias = [];
    while ($row = $result->fetch_assoc()) {
        $subcategorias[] = $row;
    }
    
    echo json_encode($subcategorias);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}