<?php
function validarCupon($conexion, $codigo) {
    $stmt = $conexion->prepare("SELECT * FROM cupones_descuento 
                               WHERE codigo = ? 
                               AND estado = 'activo'
                               AND fecha_inicio <= CURDATE() 
                               AND fecha_fin >= CURDATE()
                               AND usos_restantes > 0");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function registrarUsoCupon($conexion, $cupon_id) {
    // Actualizar el cupón restando un uso
    $stmt = $conexion->prepare("
        UPDATE cupones_descuento 
        SET usos_restantes = usos_restantes - 1,
            estado = CASE 
                WHEN usos_restantes - 1 <= 0 THEN 'agotado' 
                ELSE estado 
            END
        WHERE id = ?
    ");
    
    $stmt->bind_param("i", $cupon_id);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}