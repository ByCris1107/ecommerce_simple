<?php
header('Content-Type: application/json; charset=utf-8');

include '../conexion/base_de_datos.php';

// Obtener ID del cupón
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(['error' => 'ID de cupón inválido']);
    exit();
}

try {
    // Consultar el cupón
    $stmt = $conexion->prepare("SELECT * FROM cupones_descuento WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Cupón no encontrado']);
        exit();
    }
    
    $cupon = $result->fetch_assoc();
    echo json_encode($cupon);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al obtener el cupón']);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conexion->close();
}
?>