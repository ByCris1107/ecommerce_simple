<?php
require_once './conexion/base_de_datos.php';
require_once './include/funciones_cupones.php';
session_start();

// Verificar datos necesarios
if (!isset($_GET['payment_id']) || !isset($_GET['preference_id']) || empty($_SESSION['carrito']) || !isset($_SESSION['datos_envio'])) {
    header("Location: ./?page=carrito");
    exit;
}

// Calcular valores
$subtotal = array_reduce($_SESSION['carrito'], function($total, $item) {
    return $total + ($item['precio_original'] * $item['cantidad']);
}, 0);

$descuento_productos = array_reduce($_SESSION['carrito'], function($total, $item) {
    return $total + (($item['precio_original'] - $item['precio']) * $item['cantidad']);
}, 0);

$subtotal_con_descuentos = $subtotal - $descuento_productos;

$descuento_cupon = 0;
$cupon_id = null;
if (isset($_SESSION['cupon_aplicado'])) {
    $cupon = $_SESSION['cupon_aplicado'];
    $cupon_id = $cupon['id'];
    
    // Calcular descuento según tipo
    if ($cupon['tipo_descuento'] == 'porcentaje') {
        $descuento_cupon = $subtotal_con_descuentos * ($cupon['descuento'] / 100);
    } else { // monto_fijo
        $descuento_cupon = min($cupon['descuento'], $subtotal_con_descuentos);
    }
}

$total_compra = $subtotal_con_descuentos - $descuento_cupon;

// Iniciar transacción
$conexion->begin_transaction();

try {
    // Insertar pedido (versión simplificada para tu estructura)
    $stmt_pedido = $conexion->prepare("
        INSERT INTO pedidos (
            nombre_cliente, email_cliente, dni, telefono_cliente, 
            direccion, entre_calles, codigo_postal, localidad, 
            provincia, departamento, referencias, metodo_envio, 
            total, estado, preference_id, payment_id, fecha_creacion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', ?, ?, NOW())
    ");
    
    if (!$stmt_pedido) {
        throw new Exception("Error al preparar consulta: " . $conexion->error);
    }
    
    $stmt_pedido->bind_param(
        "ssssssssssssdss",
        $_SESSION['datos_envio']['nombre'],
        $_SESSION['datos_envio']['email'],
        $_SESSION['datos_envio']['dni'],
        $_SESSION['datos_envio']['telefono'],
        $_SESSION['datos_envio']['direccion'],
        $_SESSION['datos_envio']['entre_calles'],
        $_SESSION['datos_envio']['codigo_postal'],
        $_SESSION['datos_envio']['localidad'],
        $_SESSION['datos_envio']['provincia'],
        $_SESSION['datos_envio']['departamento'],
        $_SESSION['datos_envio']['referencias'],
        $_SESSION['datos_envio']['metodo_envio'],
        $total_compra,
        $_GET['preference_id'],
        $_GET['payment_id']
    );
    
    if (!$stmt_pedido->execute()) {
        throw new Exception("Error al insertar pedido: " . $stmt_pedido->error);
    }
    
    $pedido_id = $conexion->insert_id;
    $stmt_pedido->close();

    // Registrar uso del cupón si existe
    if ($cupon_id) {
        if (!registrarUsoCupon($conexion, $cupon_id)) {
            throw new Exception("Error al actualizar los usos del cupón");
        }
    }

    // Insertar productos del pedido
    foreach ($_SESSION['carrito'] as $item) {
        $stmt_producto = $conexion->prepare("
            INSERT INTO pedidos_productos (
                pedido_id, producto_id, variante_id, nombre_producto, 
                color, talle, precio_unitario, cantidad, descuento_aplicado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $descuento = isset($item['descuento']) ? $item['descuento'] : 0;
        $stmt_producto->bind_param(
            "iiisssdii",
            $pedido_id,
            $item['producto_id'],
            $item['variante_id'],
            $item['titulo'],
            $item['color'],
            $item['talle'],
            $item['precio'],
            $item['cantidad'],
            $descuento
        );
        
        if (!$stmt_producto->execute()) {
            throw new Exception("Error al insertar producto: " . $stmt_producto->error);
        }
        $stmt_producto->close();

        // Actualizar stock
        $stmt_update = $conexion->prepare("UPDATE variantes_producto SET stock = stock - ? WHERE id = ?");
        $stmt_update->bind_param("ii", $item['cantidad'], $item['variante_id']);
        if (!$stmt_update->execute()) {
            throw new Exception("Error al actualizar stock: " . $stmt_update->error);
        }
        $stmt_update->close();
    }

    $conexion->commit();

    // Limpiar sesión
    unset($_SESSION['carrito'], $_SESSION['datos_envio'], $_SESSION['total_compra'], 
         $_SESSION['cupon_aplicado'], $_SESSION['cupon_info']);

    header("Location: ./?page=exito&pedido=" . $pedido_id);
    exit;

} catch (Exception $e) {
    $conexion->rollback();
    error_log("Error en pedido: " . $e->getMessage());
    $_SESSION['error_pedido'] = "Ocurrió un error al procesar tu pedido. Por favor intenta nuevamente.";
    header("Location: ./?page=carrito");
    exit;
}