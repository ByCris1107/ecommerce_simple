<!DOCTYPE html>
<html lang="es-AR">
<head>
<?php
session_start();

// Verificar si hay sesión activa y si el usuario es administrador
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_tipo']) || $_SESSION['admin_tipo'] !== 'admin') {
    // Destruir sesión por seguridad
    session_unset();
    session_destroy();

    // Redirigir al login con mensaje de error
    header("Location: iniciar_sesion?error=Acceso denegado. Solo administradores.");
    exit;
}

// Usuario autenticado y con rol de administrador
$usuario_id = $_SESSION['admin_id'];
$tipo = $_SESSION['admin_tipo'];
?>


  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Tu Tienda de Ropa</title>

  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link rel="stylesheet" href="./css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">


<script src="./tinymce/js/tinymce/tinymce.min.js" referrerpolicy="origin"></script>


  <meta name="description" content="Tu tienda online de ropa con las últimas tendencias.">
  <meta name="keywords" content="ropa, moda, online, tendencias, mujer, hombre, niños">
</head>
<body>

<?php
// Suponiendo que ya tenés la conexión a la base de datos en la variable $conexion

// Obtener el número de visitas desde la tabla 'visitas'
$consulta = "SELECT contador FROM visitas WHERE id = 1"; // Seleccionar el contador donde id = 1
$resultado = mysqli_query($conexion, $consulta);

// Verificar si la consulta fue exitosa
if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($conexion)); // Mostrar error si la consulta falla
}

// Obtener el resultado de la consulta
$fila = mysqli_fetch_assoc($resultado);

// Verificar si se encontró el contador
if ($fila) {
    $contador_actual = $fila['contador']; // Asignar el valor del contador
} else {
    $contador_actual = 0; // Si no se encuentra el valor, asignar 0
}


?>

<style>

@media (max-width: 768px) { /* Ajusta este valor si es necesario */
    /* Asegurándonos de que el menú tenga un z-index alto */
    aside#adminSidebar {
        z-index: 1000; /* Un valor ligeramente mayor por precaución */
    }

    /* Resetear el z-index del iframe de TinyMCE y su contenedor principal */
    iframe#message_ifr {
        z-index: auto !important;
        position: static !important; /* Aseguramos que no esté posicionado de forma absoluta */
    }

    div.tox-tinymce { /* Contenedor principal de TinyMCE */
        z-index: auto !important;
        position: static !important;
    }

    div.tox-edit-area { /* Contenedor del área de edición */
        z-index: auto !important;
        position: static !important;
    }

    div.tox-toolbar-grp { /* Contenedor de la barra de herramientas */
        z-index: auto !important;
        position: static !important;
    }
}
</style>


