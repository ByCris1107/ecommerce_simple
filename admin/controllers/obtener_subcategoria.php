<?php
// Incluir la conexión a la base de datos
require '../conexion/base_de_datos.php'; // Asegurate de que este archivo exista con ese nombre

// Obtener el ID de la categoría desde la URL
$id_categoria = $_GET['id_categoria'];

// Consulta para obtener las subcategorías de la categoría seleccionada
$consulta = "SELECT id, nombre FROM subcategorias WHERE id_categoria = ?";
$sentencia = mysqli_prepare($conexion, $consulta);
mysqli_stmt_bind_param($sentencia, 'i', $id_categoria);
mysqli_stmt_execute($sentencia);
$resultado = mysqli_stmt_get_result($sentencia);

$subcategorias = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $subcategorias[] = $fila;
}

// Devolver las subcategorías en formato JSON
echo json_encode($subcategorias);

// Cerrar la conexión
mysqli_close($conexion);
?>
