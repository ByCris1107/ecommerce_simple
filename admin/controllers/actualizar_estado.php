<?php
header('Content-Type: application/json');

// Incluir la conexión a la base de datos
require_once '../conexion/base_de_datos.php';

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener los datos del POST
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;
$status = isset($_POST['status']) ? trim($_POST['status']) : null;

// Validar los datos
if (!$order_id || !$status) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

// Lista de estados permitidos
$allowed_statuses = ['Pendiente', 'En Camino', 'Entregado'];
if (!in_array($status, $allowed_statuses)) {
    http_response_code(400);
    echo json_encode(['error' => 'Estado no válido. Los estados permitidos son: Pendiente, En Camino, Entregado']);
    exit;
}

try {
    // Preparar la consulta SQL
    $stmt = $conexion->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    // Ejecutar la consulta
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Estado actualizado correctamente',
                'new_status' => $status,
                'status_class' => match($status) {
                    'Pendiente' => 'bg-yellow-100 text-yellow-800',
                    'En Camino' => 'bg-blue-100 text-blue-800',
                    'Entregado' => 'bg-green-100 text-green-800',
                    default => 'bg-gray-100 text-gray-800'
                }
            ]);
        } else {
            // No se afectaron filas (posiblemente el pedido no existe)
            http_response_code(404);
            echo json_encode(['error' => 'Pedido no encontrado o sin cambios']);
        }
    } else {
        // Error en la ejecución
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar el estado: ' . $stmt->error]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en el servidor: ' . $e->getMessage()]);
}

// Cerrar conexión
$conexion->close();
?>