<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include './vendor/autoload.php'; // Si usas PHPMailer

// Inicializar mensajes
$mensaje = "";
$mensaje_tipo = "";

// Verificar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Verificar si el usuario existe y no está activado
    $sql = "SELECT token, nombre FROM usuarios WHERE email = ? AND is_active = 0";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($token, $nombre);
            $stmt->fetch();

            // Configuración del correo
            $mail = new PHPMailer(true);

            try {
                // Configuración SMTP para el correo
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'cristian.aquino1312@gmail.com'; // Cambiar por tu correo
                $mail->Password = 'scxendtjxilxxkig';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Configuración del contenido del correo
                $mail->setFrom('noreply@tudominio.com', 'Tu Sitio Web');
                $mail->addAddress($email, $nombre);

                $mail->isHTML(true);
                $mail->Subject = 'Reenvío de activación de cuenta';
                $mail->Body = "
                    <h2>Hola, $nombre</h2>
                    <p>Por favor, haz clic en el siguiente enlace para activar tu cuenta:</p>
                    <a href='http://www.zonacode.com/module/activar_cuenta.php?token=$token'>Activar Cuenta</a>
                ";

                $mail->send();
                $mensaje = "El correo de activación ha sido reenviado a $email.";
                $mensaje_tipo = "success";

            } catch (Exception $e) {
                $mensaje = "Error al reenviar el correo. Inténtalo más tarde.";
                $mensaje_tipo = "error";
            }
        } else {
            $mensaje = "No se pudo encontrar una cuenta no activada con ese correo.";
            $mensaje_tipo = "error";
        }
        $stmt->close();
    }
}

?>

<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold text-gray-800 text-center mb-6">Reenviar Activación</h2>

            <!-- Mostrar mensaje de éxito o error -->
            <?php if (!empty($mensaje)): ?>
                <div class="mb-4 p-4 rounded-lg <?= $mensaje_tipo == 'error' ? 'bg-red-100 text-red-700 border border-red-300' : 'bg-green-100 text-green-700 border border-green-300' ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <!-- Correo Electrónico -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                    <input id="email" name="email" type="email" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="tucorreo@ejemplo.com">
                </div>

                <!-- Botón de Reenvío -->
                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Reenviar Correo de Activación
                    </button>
                </div>
            </form>
        </div>
    </div>
