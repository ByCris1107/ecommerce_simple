<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    // Obtener configuración del correo desde la base de datos
    $query_config = "SELECT correo_tienda, contrasena_aplicacion, nombre_tienda FROM personalizaciones_tienda LIMIT 1";
    $resultado_config = $conexion->query($query_config);

    if ($resultado_config && $resultado_config->num_rows > 0) {
        $config = $resultado_config->fetch_assoc();

        $correo_remitente = $config['correo_tienda'];
        $contrasena = $config['contrasena_aplicacion'];
        $nombre_remitente = $config['nombre_tienda'];

        // Obtener suscriptores
        $query = "SELECT email FROM newsletter";
        $result = $conexion->query($query);

        if ($result->num_rows > 0) {
            $mail = new PHPMailer(true);

            try {
                // Configuración SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $correo_remitente;
                $mail->Password = $contrasena;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Remitente
                $mail->setFrom($correo_remitente, $nombre_remitente);
                $mail->addReplyTo($correo_remitente, $nombre_remitente);

                // Destinatarios
                while ($row = $result->fetch_assoc()) {
                    $mail->addAddress($row['email']);
                }

                // Contenido del correo
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $message;

                $mail->send();
                echo "<p class='text-green-500 text-center'>Correos enviados exitosamente a todos los suscriptores.</p>";
            } catch (Exception $e) {
                echo "<p class='text-red-500 text-center'>Error al enviar el correo: {$mail->ErrorInfo}</p>";
            }
        } else {
            echo "<p class='text-yellow-500 text-center'>No hay suscriptores para enviar correos.</p>";
        }

    } else {
        echo "<p class='text-red-500 text-center'>No se pudo obtener la configuración del correo desde la base de datos.</p>";
    }

}
?>




<div class="container mx-auto py-8 px-4">
    <h1 class="text-3xl font-bold mb-6 text-center text-black">Lista de Suscriptores</h1>

    <!-- Formulario de envío de emails -->
    <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
        <form method="POST" id="emailForm">
            <div class="mb-4">
                <label for="subject" class="block text-sm font-medium text-gray-700">Asunto del Email</label>
                <input type="text" name="subject" id="subject" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md" required>
            </div>
            <!-- Descripción -->
            <div class="mb-4">
                <label for="message" class="block text-gray-700 font-bold mb-2">Descripción</label>
                <textarea
                    id="message"
                    name="message"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none"
                    placeholder="Descripción detallada"
                    rows="5"></textarea>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">Enviar a Todos</button>
        </form>
    </div>

    <!-- Tabla de suscriptores -->
<div class="bg-white p-6 rounded-lg shadow-lg overflow-x-auto">
    <table class="table-auto w-full border-collapse border border-gray-200 text-left min-w-max">
        <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                <th class="px-6 py-3 border border-gray-300">Correo Electrónico</th>
                <th class="px-6 py-3 border border-gray-300">Fecha de Suscripción</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT * FROM newsletter ORDER BY fecha_suscripcion DESC";
            $result = $conexion->query($query);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr class='hover:bg-gray-100 transition duration-200'>";
                    echo "<td class='px-6 py-3 border border-gray-300 break-words max-w-xs'>" . htmlspecialchars($row["email"]) . "</td>";
                    echo "<td class='px-6 py-3 border border-gray-300 whitespace-nowrap'>" . htmlspecialchars($row["fecha_suscripcion"]) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2' class='px-6 py-3 border border-gray-300 text-center'>No se encontraron suscriptores.</td></tr>";
            }

            $conexion->close();
            ?>
        </tbody>
    </table>
</div>

<!-- TinyMCE para el campo de mensaje -->
<script>
    tinymce.init({
        selector: '#message',
        menubar: false,
        plugins: 'lists link image preview anchor table emoticons',
        toolbar: 'undo redo | formatselect | bold italic | forecolor backcolor | alignleft aligncenter alignright alignjustify | link emoticons',
        branding: false,
        height: 300,
        forced_root_block: '', 
        valid_elements: '*[*]',
        extended_valid_elements: 'ol[style],ul[style],li[style],span[style],strong,em,u,s,a[href|target]'
    });

    // Asegurar que el contenido de TinyMCE se guarda antes de enviar el formulario
    document.getElementById("emailForm").addEventListener("submit", function() {
        tinymce.triggerSave();
    });
</script>
