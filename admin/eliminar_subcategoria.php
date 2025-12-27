<?php
include("./conexion/base_de_datos.php");

// Verificar si se recibió el ID de la subcategoría
if (isset($_POST['id'])) {
    $id_subcategoria = $_POST['id'];

    // Obtener la imagen de la subcategoría
    $sql_subcategoria = "SELECT imagen FROM subcategorias WHERE id = ?";
    $stmt = $conexion->prepare($sql_subcategoria);
    $stmt->bind_param("i", $id_subcategoria);
    $stmt->execute();
    $resultado_subcategoria = $stmt->get_result();
    $subcategoria = $resultado_subcategoria->fetch_assoc();

    // Eliminar la imagen de la subcategoría en el servidor
    if (!empty($subcategoria['imagen']) && file_exists($subcategoria['imagen'])) {
        unlink($subcategoria['imagen']); // Elimina la imagen de la subcategoría
    }

    // Eliminar la subcategoría de la base de datos
    $sql_eliminar_subcategoria = "DELETE FROM subcategorias WHERE id = ?";
    $stmt = $conexion->prepare($sql_eliminar_subcategoria);
    $stmt->bind_param("i", $id_subcategoria);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Subcategoría eliminada con éxito."]);
    } else {
        echo json_encode(["success" => false, "message" => "Hubo un error al eliminar la subcategoría."]);
    }

    $stmt->close();
}
?>
