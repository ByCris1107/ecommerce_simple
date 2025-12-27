<?php
$servidor = "localhost"; // Cambiar si el servidor no es local
$usuario = "root";  // Reemplazar con tu usuario de MySQL
$contrasena = ""; // Reemplazar con tu contraseña de MySQL
$base_de_datos = "web_ecommerce"; // Nombre de la base de datos

// Crear la conexión
$conexion = new mysqli($servidor, $usuario, $contrasena, $base_de_datos);

// Verificar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Establecer el conjunto de caracteres a UTF-8
$conexion->set_charset("utf8");

// Evitar errores de fechas con MySQL
date_default_timezone_set("America/Argentina/Buenos_Aires"); // Cambiar según tu zona horaria
?>
