<?php
session_start();

header('Content-Type: application/json');
require_once './conexion/base_de_datos.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email no válido.']);
        exit;
    }

    $stmt = $conexion->prepare("SELECT id FROM newsletter WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Este email ya está suscripto.']);
    } else {
        $stmt = $conexion->prepare("INSERT INTO newsletter (email, fecha_suscripcion) VALUES (?, NOW())");
        $stmt->bind_param("s", $email);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => '¡Gracias por suscribirte!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar. Intentá más tarde.']);
        }
    }

    $stmt->close();
    $conexion->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
}
?>
