<?php
session_start();
require_once './conexion/base_de_datos.php';
require_once './include/funciones_cupones.php'; // Asumo que tienes esta función para registrar uso de cupones

// Verificar que hay productos en el carrito
if (empty($_SESSION['carrito'])) {
    header("Location: ./?page=carrito");
    exit;
}

// Función para validar cupón (por si no está cargada en funciones_cupones.php)
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

// Procesamiento del cupón
$descuento_aplicado = 0;
$cupon_id = null;

// Calcular subtotal sin descuento
$subtotal = 0;
foreach ($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}

// Validar cupón si existe en sesión
if (isset($_SESSION['cupon_aplicado'])) {
    $cupon = validarCupon($conexion, $_SESSION['cupon_aplicado']['codigo']);
    if ($cupon) {
        $descuento_aplicado = $cupon['descuento'];
        $cupon_id = $cupon['id'];
        $_SESSION['cupon_info'] = [
            'id' => $cupon_id,
            'codigo' => $cupon['codigo'],
            'descuento' => $descuento_aplicado
        ];
    } else {
        unset($_SESSION['cupon_aplicado'], $_SESSION['cupon_info']);
    }
}

// Procesar el formulario de envío solo si llegó por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ./?page=carrito");
    exit;
}

// Validar datos obligatorios del formulario de envío
$required_fields = ['nombre', 'telefono', 'email', 'dni', 'direccion', 'entre_calles', 'codigo_postal', 'localidad', 'provincia', 'metodo_envio'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $_SESSION['error'] = "El campo $field es obligatorio";
        header("Location: ./?page=carrito");
        exit;
    }
}

// Recopilar datos de envío
$datos_envio = [
    'nombre' => trim($_POST['nombre']),
    'telefono' => trim($_POST['telefono']),
    'email' => trim($_POST['email']),
    'dni' => trim($_POST['dni']),
    'direccion' => trim($_POST['direccion']),
    'entre_calles' => trim($_POST['entre_calles']),
    'codigo_postal' => trim($_POST['codigo_postal']),
    'localidad' => trim($_POST['localidad']),
    'provincia' => trim($_POST['provincia']),
    'departamento' => isset($_POST['departamento']) ? trim($_POST['departamento']) : '',
    'referencias' => isset($_POST['referencias']) ? trim($_POST['referencias']) : '',
    'metodo_envio' => trim($_POST['metodo_envio'])
];

// Guardar datos envío en sesión por si hay error y vuelven al carrito
$_SESSION['datos_envio'] = $datos_envio;

// Calcular costo de envío
$costo_envio = ($datos_envio['metodo_envio'] === 'express') ? 500 : 0;

// Calcular total con descuento si aplica
$total_sin_envio = $subtotal;
if ($descuento_aplicado > 0) {
    $total_sin_envio = $subtotal * (1 - ($descuento_aplicado / 100));
}
$total_compra = $total_sin_envio + $costo_envio;

// Iniciar transacción para insertar pedido y productos
$conexion->begin_transaction();

try {
    // Insertar pedido
    $stmt_pedido = $conexion->prepare("
        INSERT INTO pedidos (
            nombre_cliente, email_cliente, dni, telefono_cliente,
            direccion, entre_calles, codigo_postal, localidad,
            provincia, departamento, referencias, metodo_envio,
            total, estado, fecha_creacion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', NOW())
    ");

    if (!$stmt_pedido) {
        throw new Exception("Error al preparar consulta: " . $conexion->error);
    }

    $stmt_pedido->bind_param(
        "ssssssssssssd",
        $datos_envio['nombre'],
        $datos_envio['email'],
        $datos_envio['dni'],
        $datos_envio['telefono'],
        $datos_envio['direccion'],
        $datos_envio['entre_calles'],
        $datos_envio['codigo_postal'],
        $datos_envio['localidad'],
        $datos_envio['provincia'],
        $datos_envio['departamento'],
        $datos_envio['referencias'],
        $datos_envio['metodo_envio'],
        $total_compra
    );

    if (!$stmt_pedido->execute()) {
        throw new Exception("Error al insertar pedido: " . $stmt_pedido->error);
    }

    $pedido_id = $conexion->insert_id;
    $stmt_pedido->close();

    // Registrar uso de cupón si existe
    if ($cupon_id) {
        if (!registrarUsoCupon($conexion, $cupon_id)) {
            throw new Exception("Error al actualizar los usos del cupón");
        }
    }

    // Insertar productos del pedido y actualizar stock
    foreach ($_SESSION['carrito'] as $item) {
        $stmt_producto = $conexion->prepare("
            INSERT INTO pedidos_productos (
                pedido_id, producto_id, variante_id, nombre_producto,
                color, talle, precio_unitario, cantidad, descuento_aplicado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$stmt_producto) {
            throw new Exception("Error al preparar producto: " . $conexion->error);
        }

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
        if (!$stmt_update) {
            throw new Exception("Error al preparar actualización de stock: " . $conexion->error);
        }
        $stmt_update->bind_param("ii", $item['cantidad'], $item['variante_id']);
        if (!$stmt_update->execute()) {
            throw new Exception("Error al actualizar stock: " . $stmt_update->error);
        }
        $stmt_update->close();
    }

    $conexion->commit();

    // Limpiar sesión
    unset(
        $_SESSION['carrito'],
        $_SESSION['datos_envio'],
        $_SESSION['total_compra'],
        $_SESSION['cupon_aplicado'],
        $_SESSION['cupon_info']
    );

    header("Location: ./?page=exito&pedido=" . $pedido_id);
    exit;

} catch (Exception $e) {
    $conexion->rollback();
    error_log("Error en pedido: " . $e->getMessage());
    $_SESSION['error_pedido'] = "Ocurrió un error al procesar tu pedido. Por favor intenta nuevamente.";
    header("Location: ./?page=carrito");
    exit;
}
