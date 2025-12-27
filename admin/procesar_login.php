<?php
session_start();
require_once './conexion/base_de_datos.php'; // Asegúrate de incluir tu archivo de conexión a la base de datos

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibir los datos del formulario
    $email = trim($_POST['email']);
    $clave = trim($_POST['clave']);

    // Validar si los campos están vacíos
    if (empty($email) || empty($clave)) {
        header("Location: iniciar_sesion?error=Por favor, ingresa tu correo electrónico y contraseña.");
        exit;
    }

    // Consultar la base de datos para obtener el usuario
    $stmt = $conexion->prepare("SELECT id, nombre, email, clave, tipo FROM administrador WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si el usuario existe
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verificar la contraseña
        if (password_verify($clave, $user['clave'])) {
            // Antes de iniciar sesión, destruir cualquier sesión anterior
            session_unset();
            session_destroy();
            session_start(); // Iniciar una nueva sesión

            // Guardar los datos del administrador en variables de sesión
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_nombre'] = $user['nombre'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_tipo'] = $user['tipo'];

            // Redirigir al administrador a su panel
            header("Location: ./"); // Redirige al panel de administrador
            exit;
        } else {
            header("Location: iniciar_sesion?error=Correo o contraseña incorrectos.");
            exit;
        }
    } else {
        header("Location: iniciar_sesion?error=Correo o contraseña incorrectos.");
        exit;
    }
}
?>
