<?php
session_start(); // ESTA LÍNEA ES OBLIGATORIA ANTES DE TOCAR $_SESSION

// Verificar si hay una sesión activa antes de destruirla
if (isset($_SESSION['admin_id'])) {
    // Destruir todas las variables de sesión
    $_SESSION = [];

    // Destruir la sesión
    session_destroy();
}

// Redirigir a la página de inicio (o a la página de login si lo prefieres)
echo "<script>window.location.href = 'iniciar_sesion';</script>";
exit;
?>
