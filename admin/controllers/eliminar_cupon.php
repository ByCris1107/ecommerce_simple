<?php
header('Content-Type: application/json; charset=utf-8');

// Incluir conexión a la base de datos
include '../conexion/base_de_datos.php';

// Función para enviar respuestas de error
function enviarRespuestaError($mensaje, $codigo = 400) {
    http_response_code($codigo);
    echo json_encode([
        "swal" => [
            "icon" => "error",
            "title" => "Error",
            "text" => htmlspecialchars($mensaje)
        ]
    ]);
    exit();
}

// Verificar que sea método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enviarRespuestaError('Método no permitido.', 405);
}

// Verificar que se recibió el ID
if (empty($_POST['id'])) {
    enviarRespuestaError('No se especificó el cupón a eliminar.');
}

// Sanitizar el ID
$id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

if ($id === false || $id <= 0) {
    enviarRespuestaError('ID de cupón inválido.');
}

try {
    // Iniciar transacción
    $conexion->begin_transaction();
    
    // 1. Verificar si el cupón existe y obtener su código
    $stmt = $conexion->prepare("SELECT codigo FROM cupones_descuento WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conexion->error);
    }
    
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception("El cupón no existe o ya fue eliminado.");
    }
    
    $cupon = $result->fetch_assoc();
    $codigo_cupon = $cupon['codigo'];
    $stmt->close();
    
    // 2. Eliminar el cupón
    $stmt = $conexion->prepare("DELETE FROM cupones_descuento WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de eliminación: " . $conexion->error);
    }
    
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        throw new Exception("Error al eliminar el cupón: " . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("No se eliminó ningún cupón.");
    }
    
    $conexion->commit();
    
    // Respuesta exitosa
    echo json_encode([
        "swal" => [
            "icon" => "success",
            "title" => "¡Eliminado!",
            "text" => "El cupón '" . htmlspecialchars($codigo_cupon) . "' fue eliminado correctamente."
        ],
        "reload" => true
    ]);
    
} catch (Exception $e) {
    if (isset($conexion)) {
        $conexion->rollback();
    }
    error_log("Error al eliminar cupón [ID: $id]: " . $e->getMessage());
    enviarRespuestaError($e->getMessage());
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conexion)) {
        $conexion->close();
    }
}
?>