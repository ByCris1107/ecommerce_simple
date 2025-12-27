<?php
header('Content-Type: application/json; charset=utf-8');

// Incluir conexión y funciones auxiliares
include '../conexion/base_de_datos.php';

// Funciones auxiliares deben estar definidas antes de usarse
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

function validarFecha($fecha, $formato = 'Y-m-d') {
    $d = DateTime::createFromFormat($formato, $fecha);
    return $d && $d->format($formato) === $fecha;
}

// Verificar que sea método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enviarRespuestaError('Método no permitido.', 405);
}

// Validar campos requeridos
$campos_requeridos = ['codigo', 'tipo_descuento', 'descuento', 'usos_totales', 'fecha_inicio', 'fecha_fin', 'estado'];
foreach ($campos_requeridos as $campo) {
    if (empty($_POST[$campo])) {
        enviarRespuestaError("Faltan datos necesarios: $campo");
    }
}

// Sanitizar y validar datos
$codigo = trim($_POST['codigo']);
$tipo_descuento = $_POST['tipo_descuento'];
$descuento = filter_var($_POST['descuento'], FILTER_VALIDATE_FLOAT);
$usos_totales = filter_var($_POST['usos_totales'], FILTER_VALIDATE_INT);
$fecha_inicio = $_POST['fecha_inicio'];
$fecha_fin = $_POST['fecha_fin'];
$estado = $_POST['estado'];
$minimo_compra = !empty($_POST['minimo_compra']) ? filter_var($_POST['minimo_compra'], FILTER_VALIDATE_FLOAT) : null;

// Validaciones adicionales
if (strlen($codigo) > 20) {
    enviarRespuestaError('El código no puede tener más de 20 caracteres.');
}

if ($descuento === false || $descuento <= 0) {
    enviarRespuestaError('El valor del descuento debe ser mayor que 0.');
}

if ($tipo_descuento === 'porcentaje' && $descuento > 100) {
    enviarRespuestaError('El porcentaje no puede ser mayor a 100%.');
}

if ($usos_totales === false || $usos_totales <= 0) {
    enviarRespuestaError('Los usos totales deben ser al menos 1.');
}

if (!validarFecha($fecha_inicio) || !validarFecha($fecha_fin)) {
    enviarRespuestaError('Formato de fecha inválido.');
}

if (strtotime($fecha_inicio) > strtotime($fecha_fin)) {
    enviarRespuestaError('La fecha de inicio no puede ser posterior a la fecha fin.');
}

// Procesar la inserción
try {
    $conexion->begin_transaction();
    
    // Verificar si minimo_compra es NULL y ajustar la consulta SQL
    if ($minimo_compra === null) {
        $sql = "INSERT INTO cupones_descuento (
            codigo, tipo_descuento, descuento, usos_totales, usos_restantes, 
            fecha_inicio, fecha_fin, estado, creado_en
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conexion->prepare($sql);
        $usos_restantes = $usos_totales;
        $stmt->bind_param(
            "ssdiisss",
            $codigo, $tipo_descuento, $descuento, $usos_totales,
            $usos_restantes, $fecha_inicio, $fecha_fin, $estado
        );
    } else {
        $sql = "INSERT INTO cupones_descuento (
            codigo, tipo_descuento, descuento, usos_totales, usos_restantes, 
            fecha_inicio, fecha_fin, estado, minimo_compra, creado_en
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conexion->prepare($sql);
        $usos_restantes = $usos_totales;
        $stmt->bind_param(
            "ssdiisssd",
            $codigo, $tipo_descuento, $descuento, $usos_totales,
            $usos_restantes, $fecha_inicio, $fecha_fin, $estado, $minimo_compra
        );
    }
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conexion->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception($conexion->error);
    }
    
    $conexion->commit();
    
    // Respuesta exitosa
    echo json_encode([
        "swal" => [
            "icon" => "success",
            "title" => "Éxito",
            "text" => "Cupón guardado exitosamente.",
            "footer" => "Código: " . htmlspecialchars($codigo) . 
                      "<br>Tipo: " . htmlspecialchars($tipo_descuento) . 
                      "<br>Descuento: " . htmlspecialchars($descuento) . 
                      ($tipo_descuento === 'porcentaje' ? '%' : '$')
        ],
        "redirect" => "./?module=cupones"
    ]);
    
} catch (Exception $e) {
    $conexion->rollback();
    
    // Manejar errores específicos
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        enviarRespuestaError('El código de cupón ya existe.');
    } else {
        error_log("Error al guardar cupón: " . $e->getMessage());
        enviarRespuestaError('Error al procesar la solicitud. Por favor, intente nuevamente.');
    }
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conexion->close();
}
?>