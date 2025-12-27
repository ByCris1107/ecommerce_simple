<?php session_start(); ?>


<!DOCTYPE html>
<html lang="es-AR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php
// Consulta para obtener el nombre de la tienda desde la tabla personalizaciones_tienda
$consulta_tienda = "SELECT nombre_tienda FROM personalizaciones_tienda WHERE id = 1"; // Asegúrate de que el WHERE sea correcto
$resultado_tienda = mysqli_query($conexion, $consulta_tienda);

// Verifica si la consulta tuvo éxito
if ($resultado_tienda) {
    $row_tienda = mysqli_fetch_assoc($resultado_tienda);
    $nombreTienda = $row_tienda['nombre_tienda']; // Guarda el nombre de la tienda
} else {
    // Si no se encuentra el nombre de la tienda, puedes asignar un nombre por defecto
    $nombreTienda = 'Mi Tienda Online'; // Nombre por defecto
}
?>
<title><?php echo $nombreTienda; ?></title>

  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link rel="stylesheet" href="style.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">

  <?php
  // Consulta para obtener el favicon desde la tabla personalizacion_tienda
$consulta = "SELECT favicon_tienda FROM personalizaciones_tienda WHERE id = 1"; // Cambia el WHERE según el caso
$resultado = mysqli_query($conexion, $consulta);

// Verifica si la consulta tuvo éxito
if ($resultado) {
    $row = mysqli_fetch_assoc($resultado);
    $faviconUrl = $row['favicon_tienda']; // Guarda la URL del favicon
} else {
    // Si no se encuentra el favicon, puedes asignar una URL por defecto
    $faviconUrl = 'assets/img/default_favicon.ico'; // Asegúrate de tener un favicon por defecto
}
?>
<meta property="og:image" content="http://www.tusitio.com/admin/controllers/uploads/tienda_imagenes/<?php echo $faviconUrl; ?>">
<meta name="twitter:image" content="http://www.tusitio.com/admin/controllers/uploads/tienda_imagenes/<?php echo $faviconUrl; ?>">
  <link rel="icon" href="./admin/controllers/uploads/tienda_imagenes/<?php echo $faviconUrl; ?>" type="image/x-icon">
  <meta name="description" content="Tu tienda online de ropa con las últimas tendencias.">
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

  <meta name="keywords" content="ropa, moda, online, tendencias, mujer, hombre, niños">
</head>
<body class="min-h-screen flex flex-col">

<?php
// Obtener la URL actual y la fecha de hoy
$url_actual = $_SERVER['REQUEST_URI'];
$fecha_actual = date('Y-m-d');

// Verificar si ya hay un registro para esta URL y esta fecha
$consulta_visita = "SELECT contador FROM visitas WHERE url = ? AND fecha = ?";
$stmt = $conexion->prepare($consulta_visita);
$stmt->bind_param("ss", $url_actual, $fecha_actual);
$stmt->execute();
$stmt->bind_result($contador_existente);
$stmt->fetch();
$stmt->close();

if (isset($contador_existente)) {
    // Ya hay un registro, actualizamos el contador
    $nuevo_contador = $contador_existente + 1;
    $actualizar_visita = "UPDATE visitas SET contador = ? WHERE url = ? AND fecha = ?";
    $stmt = $conexion->prepare($actualizar_visita);
    $stmt->bind_param("iss", $nuevo_contador, $url_actual, $fecha_actual);
    $stmt->execute();
    $stmt->close();
} else {
    // No existe registro, lo insertamos
    $nuevo_contador = 1;
    $insertar_visita = "INSERT INTO visitas (url, contador, fecha) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($insertar_visita);
    $stmt->bind_param("sis", $url_actual, $nuevo_contador, $fecha_actual);
    $stmt->execute();
    $stmt->close();
}

// Obtener la información de contacto de la tienda
$consulta_tienda = "SELECT contacto_tienda AS store_contacto, correo_tienda AS store_email, facebook, instagram FROM personalizaciones_tienda LIMIT 1";
$resultado_tienda = $conexion->query($consulta_tienda);

if ($resultado_tienda && $resultado_tienda->num_rows > 0) {
    $datos_tienda = $resultado_tienda->fetch_assoc();
    $store_contact = $datos_tienda['store_contacto'] ?? 'Contacto no disponible';
    $store_email = $datos_tienda['store_email'] ?? 'email@tienda.com';
    $store_facebook = $datos_tienda['facebook'] ?? '';
    $store_instagram = $datos_tienda['instagram'] ?? '';
} else {
    $store_contact = 'Contacto no disponible';
    $store_email = 'email@tienda.com';
    $store_facebook = '';
    $store_instagram = '';
}

?>



