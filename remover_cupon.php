<?php
session_start();

header('Content-Type: application/json');

try {
    if (isset($_SESSION['cupon_aplicado'])) {
        unset($_SESSION['cupon_aplicado']);
        echo json_encode(['exito' => true, 'mensaje' => 'Cupón eliminado correctamente']);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'No hay cupón aplicado']);
    }
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error al eliminar cupón']);
}
?>