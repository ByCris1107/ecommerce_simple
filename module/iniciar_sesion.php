<?php

// Redirigir si hay sesión activa
if (!empty($_SESSION['id'])) {
    echo "<script>window.location.href = './';</script>";
    exit;
}

// Función para mostrar mensajes estilizados
function mostrarMensaje($mensaje, $tipo = "error") {
    $clase = $tipo === "success"
        ? "bg-green-100 text-green-800 border border-green-300"
        : "bg-red-100 text-red-800 border border-red-300";
    return "<div class='mb-6 p-4 rounded-xl shadow-sm animate-fade-in $clase'>" . htmlspecialchars($mensaje) . "</div>";
}

$mensajeHTML = "";

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $mensajeHTML = mostrarMensaje("Por favor, completá todos los campos.");
    } else {
        $sql = "SELECT id, nombre_completo, contrasena, rol FROM usuarios WHERE email = ? LIMIT 1";
        if ($stmt = $conexion->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $nombre_completo, $hashed_password, $rol);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    // Iniciar sesión
                    $_SESSION['id'] = $id;
                    $_SESSION['nombre'] = $nombre_completo;
                    $_SESSION['rol'] = $rol;

                    echo "<script>window.location.href = './';</script>";
                    exit;
                } else {
                    $mensajeHTML = mostrarMensaje("La contraseña ingresada es incorrecta.");
                }
            } else {
                $mensajeHTML = mostrarMensaje("No se encontró una cuenta con ese correo.");
            }
            $stmt->close();
        } else {
            $mensajeHTML = mostrarMensaje("Ocurrió un error al conectar con la base de datos.");
        }
    }
}
?>

<!-- Vista -->
<?php if (!isset($_SESSION['id'])): ?>
<div class="min-h-screen flex items-center justify-center px-4 py-20 bg-gray-50">
    <div class="w-full max-w-md bg-white p-8 rounded-3xl shadow-xl transition-all">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-bold text-gray-900">Bienvenido de nuevo 👋</h2>
            <p class="mt-2 text-gray-500 text-sm">Iniciá sesión para continuar</p>
        </div>

        <?= $mensajeHTML ?>

        <form class="space-y-5" method="POST" action="">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                <input id="email" name="email" type="email" required
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-black focus:border-black transition"
                    placeholder="tucorreo@ejemplo.com" value="<?= htmlspecialchars($email ?? '') ?>">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                <input id="password" name="password" type="password" required
                    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-black focus:border-black transition"
                    placeholder="********">
            </div>

            <div class="text-right">
                <a href="./?page=olvide_contrasena" class="text-sm text-black hover:text-gray-600 transition">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <button type="submit"
                class="w-full py-3 px-6 text-sm font-semibold rounded-lg text-white bg-black hover:bg-gray-800 focus:ring-2 focus:ring-offset-2 focus:ring-black transition">
                Iniciar sesión
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-600">
            ¿No tenés una cuenta?
            <a href="./?page=registro" class="text-black font-semibold hover:text-gray-700 transition">Registrate</a>
        </div>
    </div>
</div>
<?php endif; ?>
