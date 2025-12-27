<?php
require_once './conexion/base_de_datos.php';
session_start();

// Asegúrate de que no haya salida antes del header
if (ob_get_length()) ob_clean();

header('Content-Type: application/json');

try {
    if (!isset($_POST['codigo'])) {
        throw new Exception('Código no proporcionado');
    }

    $codigo = trim($_POST['codigo']);
    
    // Validar cupón
    $stmt = $conexion->prepare("SELECT * FROM cupones_descuento 
                              WHERE codigo = ? 
                              AND estado = 'activo'
                              AND fecha_inicio <= CURDATE() 
                              AND fecha_fin >= CURDATE()
                              AND usos_restantes > 0");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $cupon = $stmt->get_result()->fetch_assoc();

    if ($cupon) {
        $_SESSION['cupon_aplicado'] = [
            'id' => $cupon['id'],
            'codigo' => $cupon['codigo'],
            'descuento' => $cupon['descuento']
        ];
        
        echo json_encode([
            'exito' => true,
            'descuento' => $cupon['descuento'],
            'mensaje' => 'Cupón aplicado correctamente'
        ]);
    } else {
        throw new Exception('Cupón no válido o ya no disponible');
    }
} catch (Exception $e) {
    // Limpiar cupón si existe
    if (isset($_SESSION['cupon_aplicado'])) {
        unset($_SESSION['cupon_aplicado']);
    }
    
    echo json_encode([
        'exito' => false,
        'mensaje' => $e->getMessage()
    ]);
}
?>