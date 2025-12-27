<?php
include('../conexion/base_de_datos.php');

header('Content-Type: application/json');

$data = $_POST;
$id = (int)$data['id'];

// Validaciones básicas
if (empty($data['codigo']) || empty($data['tipo_descuento']) || !isset($data['descuento'])) {
    echo json_encode(['error' => 'Todos los campos son requeridos']);
    exit;
}

// Preparar la consulta
$query = "UPDATE cupones_descuento SET 
            codigo = ?,
            tipo_descuento = ?,
            descuento = ?,
            usos_totales = ?,
            usos_restantes = ?,
            fecha_inicio = ?,
            fecha_fin = ?,
            estado = ?
          WHERE id = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param(
    'ssdiisssi',
    $data['codigo'],
    $data['tipo_descuento'],
    $data['descuento'],
    $data['usos_totales'],
    $data['usos_restantes'],
    $data['fecha_inicio'],
    $data['fecha_fin'],
    $data['estado'],
    $id
);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Cupón actualizado correctamente',
        'reload' => true
    ]);
} else {
    echo json_encode(['error' => 'Error al actualizar el cupón: ' . $conexion->error]);
}
?>