<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Asegúrate que esto esté bien apuntado
require_once 'conexion/base_de_datos.php'; // Asegúrate que estás incluyendo tu conexión

function mostrarMensaje($mensaje, $tipo = "error") {
    $clase = $tipo === "success"
        ? "bg-green-100 text-green-700 border border-green-300"
        : "bg-red-100 text-red-700 border border-red-300";
    return "<div class='mb-4 p-4 rounded-lg fade-in $clase'>" . htmlspecialchars($mensaje) . "</div>";
}

function enviarCorreoActivacion($email, $nombre_completo, $token, $correo_tienda, $nombre_tienda, $contrasena_aplicacion) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $correo_tienda;
        $mail->Password = $contrasena_aplicacion;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($correo_tienda, $nombre_tienda);
        $mail->addAddress($email, $nombre_completo);

        $url_activacion = "http://localhost/web_ecommerce/controllers/activar_cuenta.php?token=" . urlencode($token);
        $mail->isHTML(true);
        $mail->Subject = 'Activa tu cuenta';
        $mail->Body = "
            <html>
            <head><title>Activa tu cuenta</title></head>
            <body>
                <p>Hola <strong>" . htmlspecialchars($nombre_completo) . "</strong>,</p>
                <p>Gracias por registrarte. Haz clic en el siguiente enlace para activar tu cuenta:</p>
                <p><a href='" . $url_activacion . "' style='color:blue;'>Activar mi cuenta</a></p>
                <p>Si no te registraste, ignora este mensaje.</p>
            </body>
            </html>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

$mensajeHTML = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contrasena = $_POST['password'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_password'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if (empty($nombre_completo) || empty($email) || empty($contrasena) || empty($confirmar_contrasena)) {
        $mensajeHTML = mostrarMensaje("Por favor, completa todos los campos obligatorios.");
    } elseif ($contrasena !== $confirmar_contrasena) {
        $mensajeHTML = mostrarMensaje("Las contraseñas no coinciden.");
    } else {
        $sql = "SELECT id FROM usuarios WHERE email = ? LIMIT 1";
        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $mensajeHTML = mostrarMensaje("El correo electrónico ya está registrado.");
            } else {
                $query_email = "SELECT correo_tienda, nombre_tienda, contrasena_aplicacion FROM personalizaciones_tienda WHERE id = 1";
                $result_email = mysqli_query($conexion, $query_email);

                if ($result_email) {
                    $row = mysqli_fetch_assoc($result_email);
                    $correo_tienda = $row['correo_tienda'] ?? '';
                    $nombre_tienda = $row['nombre_tienda'] ?? 'Tienda';
                    $contrasena_aplicacion = $row['contrasena_aplicacion'] ?? '';

                    if (empty($correo_tienda) || empty($contrasena_aplicacion)) {
                        $mensajeHTML = mostrarMensaje("Error al obtener los datos SMTP de la tienda.");
                    } else {
                        $token = bin2hex(random_bytes(32));
                        $password_hashed = password_hash($contrasena, PASSWORD_DEFAULT);

                        $sql_insert = "INSERT INTO usuarios (nombre_completo, email, contrasena, telefono, direccion, rol, activo, token_activacion)
                                       VALUES (?, ?, ?, ?, ?, 'usuario', 0, ?)";
                        if ($stmt_insert = $conexion->prepare($sql_insert)) {
                            $stmt_insert->bind_param("ssssss", $nombre_completo, $email, $password_hashed, $telefono, $direccion, $token);
                            if ($stmt_insert->execute()) {
                                if (enviarCorreoActivacion($email, $nombre_completo, $token, $correo_tienda, $nombre_tienda, $contrasena_aplicacion)) {
                                    $mensajeHTML = mostrarMensaje("¡Registro exitoso! Te enviamos un email para activar tu cuenta.", "success");
                                } else {
                                    $mensajeHTML = mostrarMensaje("Usuario registrado, pero falló el envío del correo de activación.");
                                }
                            } else {
                                $mensajeHTML = mostrarMensaje("Error al registrar el usuario.");
                            }
                            $stmt_insert->close();
                        } else {
                            $mensajeHTML = mostrarMensaje("Error en la preparación del registro.");
                        }
                    }
                } else {
                    $mensajeHTML = mostrarMensaje("Error al obtener la configuración de la tienda.");
                }
            }
            $stmt->close();
        } else {
            $mensajeHTML = mostrarMensaje("Error al verificar el correo.");
        }
    }
}
?>

<!-- HTML del formulario -->
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-2xl">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Crear una cuenta</h2>
            <p class="mt-2 text-gray-500 text-sm">Completa tus datos para registrarte</p>
        </div>

        <?= $mensajeHTML ?>

        <form class="space-y-6" method="POST" action="" onsubmit="return validarFormulario();">
            <div>
                <label for="nombre_completo" class="block text-sm font-medium text-gray-700">Nombre Completo *</label>
                <input id="nombre_completo" name="nombre_completo" type="text" required
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-black focus:ring-1 focus:ring-black sm:text-sm"
                    placeholder="Juan Pérez">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico *</label>
                <input id="email" name="email" type="email" required
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-black focus:ring-1 focus:ring-black sm:text-sm"
                    placeholder="correo@ejemplo.com">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña *</label>
                <input id="password" name="password" type="password" required minlength="6"
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-black focus:ring-1 focus:ring-black sm:text-sm"
                    placeholder="********">
            </div>

            <div>
                <label for="confirmar_password" class="block text-sm font-medium text-gray-700">Confirmar Contraseña *</label>
                <input id="confirmar_password" name="confirmar_password" type="password" required minlength="6"
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-black focus:ring-1 focus:ring-black sm:text-sm"
                    placeholder="********">
            </div>

            <div>
                <button type="submit"
                    class="w-full flex justify-center py-3 px-6 text-sm font-semibold rounded-lg text-white bg-black hover:bg-gray-800 transition duration-300">
                    Registrarme
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                ¿Ya tienes una cuenta?
                <a href="./?page=iniciar_sesion" class="text-black font-medium hover:text-gray-700">Inicia sesión</a>
            </p>
        </div>
    </div>
</div>

<!-- Validación JavaScript -->
<script>
function validarFormulario() {
    const pass = document.getElementById("password").value;
    const confirm = document.getElementById("confirmar_password").value;

    if (pass !== confirm) {
        alert("Las contraseñas no coinciden.");
        return false;
    }
    return true;
}
</script>
