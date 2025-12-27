<?php
require_once 'vendor/autoload.php';
require_once './conexion/base_de_datos.php';

// Configuración de MercadoPago
MercadoPago\SDK::setAccessToken('TU_ACCESS_TOKEN');

// Iniciar sesión
session_start();

// Verificar que hay productos en el carrito
if (empty($_SESSION['carrito'])) {
    header("Location: ./?page=carrito");
    exit;
}

// Función para validar cupón (debería estar en funciones_cupones.php)
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
$subtotal = 0;

// Calcular subtotal sin descuento
foreach ($_SESSION['carrito'] as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
}

// Validar cupón si existe
if (isset($_SESSION['cupon_aplicado'])) {
    $cupon = validarCupon($conexion, $_SESSION['cupon_aplicado']['codigo']);
    
    if ($cupon) {
        $descuento_aplicado = $cupon['descuento'];
        $cupon_id = $cupon['id'];
        
        // Guardar información del cupón para después del pago
        $_SESSION['cupon_info'] = [
            'id' => $cupon_id,
            'codigo' => $cupon['codigo'],
            'descuento' => $descuento_aplicado
        ];
    } else {
        // Si el cupón ya no es válido, limpiarlo
        unset($_SESSION['cupon_aplicado']);
        if (isset($_SESSION['cupon_info'])) {
            unset($_SESSION['cupon_info']);
        }
    }
}

// Procesar el formulario de envío
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar datos del formulario
    $required_fields = ['nombre', 'telefono', 'email', 'dni', 'direccion', 'entre_calles', 'codigo_postal', 'localidad', 'provincia'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = "El campo $field es obligatorio";
            header("Location: ./?page=carrito");
            exit;
        }
    }

    // Obtener datos del formulario
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
        'metodo_envio' => $_POST['metodo_envio']
    ];

    // Guardar datos de envío en sesión
    $_SESSION['datos_envio'] = $datos_envio;

    // Calcular costo de envío
    $costo_envio = ($datos_envio['metodo_envio'] === 'express') ? 500 : 0;

    // Calcular total con descuento si aplica
    $total_sin_envio = $subtotal;
    if ($descuento_aplicado > 0) {
        $total_sin_envio = $subtotal * (1 - ($descuento_aplicado / 100));
    }
    $total_compra = $total_sin_envio + $costo_envio;

    // Crear preferencia de MercadoPago
    $preference = new MercadoPago\Preference();

    // Configurar items del carrito (mostrando precio original)
    $items = [];
    foreach ($_SESSION['carrito'] as $item) {
        $mp_item = new MercadoPago\Item();
        $mp_item->title = $item['titulo'] . ' - ' . $item['color'] . ' - Talle: ' . $item['talle'];
        $mp_item->quantity = $item['cantidad'];
        $mp_item->unit_price = $item['precio']; // Precio unitario sin descuento
        $mp_item->currency_id = "ARS";
        $items[] = $mp_item;
    }

    // Agregar ítem de descuento si hay cupón
    if ($descuento_aplicado > 0) {
        $descuento_item = new MercadoPago\Item();
        $descuento_item->title = "Descuento (" . $_SESSION['cupon_info']['codigo'] . ")";
        $descuento_item->quantity = 1;
        $descuento_item->unit_price = -($subtotal * ($descuento_aplicado / 100)); // Valor negativo
        $descuento_item->currency_id = "ARS";
        $items[] = $descuento_item;
    }

    // Agregar costo de envío como ítem adicional si es express
    if ($costo_envio > 0) {
        $envio_item = new MercadoPago\Item();
        $envio_item->title = "Envío Express (24-48 horas)";
        $envio_item->quantity = 1;
        $envio_item->unit_price = $costo_envio;
        $envio_item->currency_id = "ARS";
        $items[] = $envio_item;
    }

    $preference->items = $items;

    // Configurar información del comprador
    $payer = new MercadoPago\Payer();
    $payer->name = $datos_envio['nombre'];
    $payer->email = $datos_envio['email'];
    $payer->phone = [
        "area_code" => "",
        "number" => $datos_envio['telefono']
    ];
    $payer->address = [
        "street_name" => $datos_envio['direccion'],
        "street_number" => "",
        "zip_code" => $datos_envio['codigo_postal']
    ];

    $preference->payer = $payer;

    // Configurar URLs de retorno
    $preference->back_urls = [
        "success" => "https://localhost/web_ecommerce/pago_exitoso.php",
        "failure" => "https://tudominio.com/pago_fallido.php",
        "pending" => "https://tudominio.com/pago_pendiente.php"
    ];

    $preference->auto_return = "approved";
    $preference->binary_mode = true;

    try {
        $preference->save();
        
        // Guardar información importante en sesión
        $_SESSION['total_compra'] = $total_compra;
        $_SESSION['subtotal'] = $subtotal;
        $_SESSION['costo_envio'] = $costo_envio;
        
        // Redirigir a MercadoPago para el pago
        header("Location: " . $preference->init_point);
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error al procesar el pago: " . $e->getMessage();
        header("Location: ./?page=carrito");
        exit;
    }
} else {
    header("Location: ./?page=carrito");
    exit;
}