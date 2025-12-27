<?php
require_once 'vendor/autoload.php';  // Esto carga el SDK si usas Composer

// Configura Mercado Pago con tus credenciales
MercadoPago\SDK::setAccessToken('TU_ACCESS_TOKEN');
MercadoPago\SDK::setPublicKey('TU_PUBLIC_KEY');
