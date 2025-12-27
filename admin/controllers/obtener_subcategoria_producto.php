<?php
require '../conexion/base_de_datos.php';

header('Content-Type: application/json');

if (isset($_GET['categoria_id'])) {
    $categoria_id = intval($_GET['categoria_id']);

    $sql = "SELECT id, nombre FROM subcategorias WHERE categoria_id = ? ORDER BY nombre ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $categoria_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $subcategorias = [];

    while ($fila = $resultado->fetch_assoc()) {
        $subcategorias[] = [
            'id' => $fila['id'],
            'nombre' => $fila['nombre']
        ];
    }

    echo json_encode($subcategorias);
    $stmt->close();
} else {
    echo json_encode([]);
}

$conexion->close();
