<?php
session_start();
include("./conexion/base_de_datos.php");

// Determinar estado del pago
$status = $_GET['status'] ?? 'unknown';
$preference_id = $_SESSION['mp_checkout']['preference_id'] ?? null;

// Verificar el estado real con la API de Mercado Pago
$payment_status = $status;
if ($preference_id) {
    try {
        require_once 'vendor/autoload.php';
        MercadoPago\SDK::setAccessToken('TU_ACCESS_TOKEN');
        
        $preference = MercadoPago\Preference::find_by_id($preference_id);
        $payment_status = $preference->status ?? $status;
        
        // Si el pago fue aprobado, vaciar el carrito
        if ($payment_status === 'approved') {
            unset($_SESSION['carrito']);
            
            // Aquí puedes registrar el pedido en tu base de datos
            // ...
        }
    } catch (Exception $e) {
        error_log("Error verificando pago: " . $e->getMessage());
    }
}

// Configurar mensajes según el estado
switch ($payment_status) {
    case 'approved':
        $titulo = "¡Pago Aprobado!";
        $mensaje = "Tu pedido ha sido procesado correctamente.";
        $icono = "fas fa-check-circle text-green-500";
        $bg_color = "bg-green-50";
        break;
        
    case 'failure':
        $titulo = "Pago Rechazado";
        $mensaje = "Hubo un problema con tu pago. Por favor intenta nuevamente.";
        $icono = "fas fa-times-circle text-red-500";
        $bg_color = "bg-red-50";
        break;
        
    case 'pending':
        $titulo = "Pago Pendiente";
        $mensaje = "Estamos esperando la confirmación de tu pago.";
        $icono = "fas fa-clock text-yellow-500";
        $bg_color = "bg-yellow-50";
        break;
        
    default:
        $titulo = "Estado de pago desconocido";
        $mensaje = "No hemos podido verificar el estado de tu pago.";
        $icono = "fas fa-question-circle text-gray-500";
        $bg_color = "bg-gray-50";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="<?= $bg_color ?> min-h-screen flex items-center">
    <div class="max-w-md mx-auto p-6 w-full">
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-8 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                    <i class="<?= $icono ?> text-3xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900"><?= $titulo ?></h2>
                <p class="mt-2 text-gray-600"><?= $mensaje ?></p>
                
                <?php if ($payment_status === 'approved' && $preference_id): ?>
                <div class="mt-4 p-4 bg-green-50 rounded-lg">
                    <p class="text-green-800">N° de pedido: <?= $preference_id ?></p>
                    <p class="text-green-800 mt-2">Total: $<?= number_format($_SESSION['mp_checkout']['total'] ?? 0, 2, ',', '.') ?></p>
                </div>
                <?php endif; ?>
                
                <div class="mt-6">
                    <a href="./" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                        <i class="fas fa-home mr-2"></i>
                        Volver al inicio
                    </a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="./mis_pedidos.php" class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-shopping-bag mr-2"></i>
                        Ver mis pedidos
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>