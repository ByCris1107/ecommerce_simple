<?php
session_start();
require_once './conexion/base_de_datos.php'; // Asegurate de incluir la conexión

// Manejo de mensajes de error y éxito
$error = isset($_GET['error']) ? trim($_GET['error']) : '';
$success = isset($_GET['success']) ? trim($_GET['success']) : '';

// Realizar la consulta para obtener el nombre de la tienda
$consulta = "SELECT nombre_tienda FROM personalizaciones_tienda WHERE id = 1";
$resultado = $conexion->query($consulta);
$nombre_tienda = "Mi Tienda"; // Valor por defecto

if ($resultado && $resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    $nombre_tienda = $fila['nombre_tienda'];
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars($nombre_tienda); ?> </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-900 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-lg shadow-md p-8 max-w-sm w-full">
        <h2 class="text-2xl font-semibold text-gray-800 text-center mb-4">Iniciar Sesión</h2>

        <form action="procesar_login.php" method="POST">
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                <input type="email" id="email" name="email" class="w-full p-2 mt-1 border rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="clave" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input type="password" id="clave" name="clave" class="w-full p-2 mt-1 border rounded-md" required>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Iniciar Sesión</button>
            </div>
        </form>
    </div>

    <!-- Alertas solo si los parámetros existen -->
    <?php if (!empty($error)): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo htmlspecialchars($error); ?>',
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '<?php echo htmlspecialchars($success); ?>',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                window.location.href = './';
            });
        </script>
    <?php endif; ?>
</body>

</html>
