<?php
// Consultar los datos de personalización de la tienda
$consulta = "SELECT * FROM personalizaciones_tienda WHERE id = 1";  // Cambiá '1' por el ID correspondiente si hace falta
$resultado = $conexion->query($consulta);

// Verificar si hay resultados
if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
} else {
    // Si no hay datos, se dejan los campos vacíos
    $fila = [
        'nombre_tienda' => '',
        'contacto_tienda' => '',
        'correo_tienda' => '',
        'direccion_tienda' => '',
        'contrasena_aplicacion' => '',
        'logo_tienda' => '',
        'favicon_tienda' => '',
        'portada_tienda' => '',
        'portada_tienda_celular' => '',
        'facebook' => '',
        'instagram' => ''
    ];
}
?>


<form action="./controllers/guardar_cambios_tienda.php" method="POST" enctype="multipart/form-data" class="max-w-3xl mx-auto bg-white p-6 shadow-lg rounded-lg">
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Personaliza tu Ecommerce</h2>

    <!-- Información de la Tienda -->
    <div class="mb-6">
    <h3 class="text-lg font-semibold mb-2 text-gray-700">Información General</h3>

    <label class="block font-medium text-gray-600">Nombre de la Tienda</label>
    <input type="text" name="nombre_tienda" value="<?php echo htmlspecialchars($fila['nombre_tienda']); ?>" required placeholder="Ejemplo: Tienda Online" class="w-full p-3 border rounded-lg mb-4">

    <label class="block font-medium text-gray-600">Contacto</label>
    <input type="text" name="contacto_tienda" value="<?php echo htmlspecialchars($fila['contacto_tienda']); ?>" placeholder="Ejemplo: +54 9 11 1234-5678" class="w-full p-3 border rounded-lg mb-4">

    <label class="block font-medium text-gray-600">Correo Electrónico</label>
    <input type="email" name="correo_tienda" value="<?php echo htmlspecialchars($fila['correo_tienda']); ?>" required placeholder="correo@ejemplo.com" class="w-full p-3 border rounded-lg mb-4">

    <label class="block font-medium text-gray-600">Dirección</label>
    <input type="text" name="direccion_tienda" value="<?php echo htmlspecialchars($fila['direccion_tienda']); ?>" placeholder="Ejemplo: Calle Ficticia 123, Ciudad" class="w-full p-3 border rounded-lg mb-4">

    <label class="block font-medium text-gray-600">Contraseña de aplicación (se obtiene desde tu Gmail)</label>
    <input type="text" name="contrasena_aplicacion" value="<?php echo htmlspecialchars($fila['contrasena_aplicacion']); ?>" placeholder="Es un código de 16 dígitos * IMPORTANTE BORRAR LOS ESPACIOS *" class="w-full p-3 border rounded-lg mb-4">
</div>


   <!-- Imágenes de la Tienda -->
<div class="mb-6">
    <h3 class="text-lg font-semibold mb-2 text-gray-700">Imágenes y Branding</h3>

    <!-- Logo -->
    <label class="block font-medium text-gray-600">Logo</label>
    <?php if ($fila['logo_tienda']) { ?>
        <div class="mb-4">
            <label class="text-gray-500">Vista previa del logo:</label>
            <img src="./controllers/uploads/tienda_imagenes/<?php echo htmlspecialchars($fila['logo_tienda']); ?>" alt="Logo" class="mb-4 w-32">
        </div>
    <?php } ?>
    <input type="file" name="logo_tienda" accept="image/*" class="w-full p-2 border rounded-lg mb-4">
    <input type="hidden" name="logo_actual" value="<?php echo htmlspecialchars($fila['logo_tienda']); ?>">

    <!-- Ícono de Pestaña (Favicon) -->
    <label class="block font-medium text-gray-600">Ícono de Pestaña (Favicon)</label>
    <?php if ($fila['favicon_tienda']) { ?>
        <div class="mb-4">
            <label class="text-gray-500">Vista previa del favicon:</label>
            <img src="./controllers/uploads/tienda_imagenes/<?php echo htmlspecialchars($fila['favicon_tienda']); ?>" alt="Favicon" class="mb-4 w-16">
        </div>
    <?php } ?>
    <input type="file" name="favicon_tienda" accept="image/*" class="w-full p-2 border rounded-lg mb-4">
    <input type="hidden" name="favicon_actual" value="<?php echo htmlspecialchars($fila['favicon_tienda']); ?>">

    <!-- Imagen de Portada -->
    <label class="block font-medium text-gray-600">Imagen de Portada</label>
    <?php if ($fila['portada_tienda']) { ?>
        <div class="mb-4">
            <label class="text-gray-500">Vista previa de la portada:</label>
            <img src="./controllers/uploads/tienda_imagenes/<?php echo htmlspecialchars($fila['portada_tienda']); ?>" alt="Portada" class="mb-4 w-full">
        </div>
    <?php } ?>
    <input type="file" name="portada_tienda" accept="image/*" class="w-full p-2 border rounded-lg mb-4">
    <input type="hidden" name="portada_actual" value="<?php echo htmlspecialchars($fila['portada_tienda']); ?>">

        <!-- Imagen de Portada celular-->
    <label class="block font-medium text-gray-600">Imagen de Portada para celular</label>
    <?php if ($fila['portada_tienda_celular']) { ?>
        <div class="mb-4">
            <label class="text-gray-500">Vista previa de la portada para celular:</label>
            <img src="./controllers/uploads/tienda_imagenes/<?php echo htmlspecialchars($fila['portada_tienda_celular']); ?>" alt="Portada" class="mb-4 w-full">
        </div>
    <?php } ?>
    <input type="file" name="portada_tienda_celular" accept="image/*" class="w-full p-2 border rounded-lg mb-4">
    <input type="hidden" name="portada_actual_celular" value="<?php echo htmlspecialchars($fila['portada_tienda_celular']); ?>">

</div>

<!-- Redes Sociales -->
<div class="mb-6">
    <h3 class="text-lg font-semibold mb-2 text-gray-700">Redes Sociales</h3>

    <label class="block font-medium text-gray-600">Facebook</label>
    <input type="url" name="facebook" value="<?php echo htmlspecialchars($fila['facebook']); ?>" placeholder="Ejemplo: https://facebook.com/mi_tienda" class="w-full p-3 border rounded-lg mb-2">

    <label class="block font-medium text-gray-600">Instagram</label>
    <input type="url" name="instagram" value="<?php echo htmlspecialchars($fila['instagram']); ?>" placeholder="Ejemplo: https://instagram.com/mi_tienda" class="w-full p-3 border rounded-lg mb-2">
</div>

<!-- Botón de Guardar -->
<button type="submit" class="w-full bg-blue-500 text-white px-6 py-3 rounded-lg text-lg font-semibold hover:bg-blue-600 transition duration-300">
    Guardar Personalización
</button>

</form>


