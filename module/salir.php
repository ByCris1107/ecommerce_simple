<?php
// Destruir todas las variables de sesión
$_SESSION = [];

// Si se desea, destruir la sesión también
session_destroy();

// Redirigir a la página de inicio o a otra página usando JavaScript
echo "<script>window.location.href = './';</script>";
exit; // Asegúrate de detener la ejecución del script
?>
