<?php

require_once '../conexion/base_de_datos.php'; // Asegúrate que el path sea correcto

$mensaje = "";
$exito = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $sql = "SELECT id FROM usuarios WHERE token_activacion = ? LIMIT 1";
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id_usuario);
            $stmt->fetch();

            // Activar la cuenta
            $sql_update = "UPDATE usuarios SET activo = 1, token_activacion = NULL WHERE id = ?";
            if ($stmt_update = $conexion->prepare($sql_update)) {
                $stmt_update->bind_param("i", $id_usuario);
                if ($stmt_update->execute()) {
                    $mensaje = "¡Cuenta activada exitosamente!";
                    $exito = true;
                } else {
                    $mensaje = "Ocurrió un error al activar la cuenta. Intenta más tarde.";
                }
                $stmt_update->close();
            }
        } else {
            $mensaje = "El enlace de activación es inválido o ya fue utilizado.";
        }

        $stmt->close();
    } else {
        $mensaje = "Error al conectar con la base de datos.";
    }
} else {
    $mensaje = "No se proporcionó un token de activación válido.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activar Cuenta</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-r from-blue-100 to-purple-100">

    <div class="bg-white shadow-lg rounded-xl p-8 max-w-md w-full text-center animate-fade-in">
        <?php if ($exito): ?>
            <div class="text-green-600 mb-6">
                <svg class="mx-auto w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2l4 -4m5 2a9 9 0 11-18 0a9 9 0 0118 0z" />
                </svg>
                <h1 class="text-2xl font-bold mt-4">¡Cuenta activada!</h1>
            </div>
            <p class="text-gray-700 mb-6"><?= htmlspecialchars($mensaje) ?></p>
            <a href="../?page=iniciar_sesion" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                Iniciar Sesión
            </a>
        <?php else: ?>
            <div class="text-red-600 mb-6">
                <svg class="mx-auto w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18a9 9 0 000-18z" />
                </svg>
                <h1 class="text-2xl font-bold mt-4">Error de activación</h1>
            </div>
            <a href="./?page=registro" class="inline-block bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                Registrarme
            </a>
        <?php endif; ?>
    </div>

    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-fade-in {
            animation: fade-in 0.5s ease-out;
        }
    </style>

</body>
</html>
