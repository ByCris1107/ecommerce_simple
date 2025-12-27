<?php
include("./conexion/base_de_datos.php");

// Verificar si se recibió el ID de la categoría
if (isset($_POST['id'])) {
    $id_categoria = $_POST['id'];

    // Primero, eliminar las subcategorías asociadas
    $sql_subcategorias = "SELECT imagen FROM subcategorias WHERE categoria_id = ?";
    $stmt = $conexion->prepare($sql_subcategorias);
    $stmt->bind_param("i", $id_categoria);
    $stmt->execute();
    $resultado_subcategorias = $stmt->get_result();

    // Eliminar las imágenes de las subcategorías en el servidor
    while ($subcategoria = $resultado_subcategorias->fetch_assoc()) {
        if (!empty($subcategoria['imagen']) && file_exists($subcategoria['imagen'])) {
            unlink($subcategoria['imagen']); // Elimina la imagen de la subcategoría
        }
    }

    // Eliminar las subcategorías de la base de datos
    $sql_eliminar_subcategorias = "DELETE FROM subcategorias WHERE categoria_id = ?";
    $stmt = $conexion->prepare($sql_eliminar_subcategorias);
    $stmt->bind_param("i", $id_categoria);
    $stmt->execute();

    // Ahora eliminar la categoría
    $sql_categoria = "SELECT imagen FROM categorias WHERE id = ?";
    $stmt = $conexion->prepare($sql_categoria);
    $stmt->bind_param("i", $id_categoria);
    $stmt->execute();
    $resultado_categoria = $stmt->get_result();
    $categoria = $resultado_categoria->fetch_assoc();

    // Eliminar la imagen de la categoría en el servidor
    if (!empty($categoria['imagen']) && file_exists($categoria['imagen'])) {
        unlink($categoria['imagen']); // Elimina la imagen de la categoría
    }

    // Eliminar la categoría de la base de datos
    $sql_eliminar_categoria = "DELETE FROM categorias WHERE id = ?";
    $stmt = $conexion->prepare($sql_eliminar_categoria);
    $stmt->bind_param("i", $id_categoria);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Categoría y subcategorías eliminadas con éxito."]);
    } else {
        echo json_encode(["success" => false, "message" => "Hubo un error al eliminar la categoría."]);
    }

    $stmt->close();
}
?>
