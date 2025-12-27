<?php
require '../conexion/base_de_datos.php';

header('Content-Type: application/json');

$sql = "SELECT id, nombre FROM categorias";
$resultado = $conexion->query($sql);

$categorias = [];

if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $categorias[] = [
            'id' => $fila['id'],
            'nombre' => $fila['nombre']
        ];
    }
}

echo json_encode($categorias);
$conexion->close();
?>
