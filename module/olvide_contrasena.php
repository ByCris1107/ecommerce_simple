<?php
require './vendor/autoload.php'; // Asegúrate de que la ruta es correcta

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Inicializar mensajes
$mensaje = "";
$mensaje_tipo = ""; // success o error

// Verificar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar el email del formulario
    $email = $_POST['email'];

    // Conectar a la base de datos (supón que ya tienes la conexión establecida en $conn)
    // Preparar la consulta para verificar si el usuario existe
    $sql = "SELECT id FROM usuarios WHERE email = ?";

    if ($stmt = $conexion->prepare($sql)) {
        // Vincular el parámetro del correo electrónico
        $stmt->bind_param("s", $email);
        
        // Ejecutar la consulta
        $stmt->execute();
        
        // Obtener el resultado
        $stmt->store_result();
        
        // Verificar si se encontró un usuario con ese correo
        if ($stmt->num_rows == 1) {
            // Generar un token único para el restablecimiento de la contraseña
            $token = bin2hex(random_bytes(50)); 
            $stmt->bind_result($id);
            $stmt->fetch();
            
            // Almacenar el token en la base de datos con un tiempo de expiración
            $expira_en = date("Y-m-d H:i:s", strtotime('+1 hour'));
            $insertTokenSql = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)";
            if ($stmt_token = $conexion->prepare($insertTokenSql)) {
                $stmt_token->bind_param("iss", $id, $token, $expira_en);
                $stmt_token->execute();

                // Enviar el correo electrónico de restablecimiento usando PHPMailer
                $reset_link = "https://zonacode.com/restablecer_contrasena.php?token=$token";

                $mail = new PHPMailer(true);
                try {
                    // Configuración del servidor de correo
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';  // Servidor SMTP de Gmail
                    $mail->SMTPAuth = true;
                    $mail->Username = 'cristian.aquino1312@gmail.com'; // Tu dirección de correo de Gmail
                    $mail->Password = 'scxendtjxilxxkig'; // Tu contraseña de aplicación de Gmail
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
                    $mail->Port = 587; // Puerto para TLS

                    // Mostrar detalles de depuración (opcional)
                    $mail->SMTPDebug = 0; // Cambia a 0 o 1 si no necesitas la depuración

                    // Configuración del email
                    $mail->setFrom('contacto@zonacode.com', 'Zona Code');
                    $mail->addAddress($email); // Correo del destinatario

                    // Contenido del correo
                    $mail->isHTML(true); // Habilitar HTML
                    $mail->Subject = 'Restablecer Contraseña';
                    $mail->Body    = "<p>Haz clic en el siguiente enlace para restablecer tu contraseña:</p><p><a href='$reset_link'>$reset_link</a></p>";
                    $mail->AltBody = "Haz clic en el siguiente enlace para restablecer tu contraseña: $reset_link";

                    // Enviar el correo
                    $mail->send();
                    $mensaje = "Te hemos enviado un correo con el enlace para restablecer tu contraseña.";
                    $mensaje_tipo = "success";
                } catch (Exception $e) {
                    // Manejar errores de envío
                    $mensaje = "No se pudo enviar el correo. Error de PHPMailer: {$mail->ErrorInfo}";
                    $mensaje_tipo = "error";
                }
            }
        } else {
            // Usuario no encontrado
            $mensaje = "No se encontró ninguna cuenta con ese correo electrónico.";
            $mensaje_tipo = "error";
        }

        // Cerrar la sentencia
        $stmt->close();
    }
}
?>
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-lg">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Olvidé mi contraseña</h2>
                <p class="text-gray-600">Ingresa tu correo electrónico para restablecer tu contraseña</p>
            </div>

            <!-- Mostrar mensaje de advertencia o éxito -->
            <?php if (!empty($mensaje)): ?>
                <div class="mb-4 p-4 rounded-lg fade-in 
                    <?= $mensaje_tipo == 'error' ? 'bg-red-100 text-red-700 border border-red-300' : 'bg-green-100 text-green-700 border border-green-300' ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <form class="space-y-6" action="" method="POST">
                <!-- Correo Electrónico -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                    <input id="email" name="email" type="email" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="tucorreo@ejemplo.com">
                </div>

                <!-- Botón para enviar solicitud de restablecimiento -->
                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Enviar enlace de restablecimiento
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    <a href="login.php" class="text-indigo-600 hover:text-indigo-500">Regresar al inicio de sesión</a>
                </p>
            </div>
        </div>
    </div>
