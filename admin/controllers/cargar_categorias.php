<?php
include '../conexion/base_de_datos.php';


header('Content-Type: application/json');

try {
    $result = $conexion->query("SELECT * FROM categorias ORDER BY nombre");
    $categorias = [];
    
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
    
    echo json_encode($categorias);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}