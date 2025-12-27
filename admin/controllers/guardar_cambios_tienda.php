<?php
include("../conexion/base_de_datos.php");  // Incluye correctamente el archivo de conexión

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $nombre_tienda = $_POST['nombre_tienda'];
    $contacto_tienda = $_POST['contacto_tienda'];
    $correo_tienda = $_POST['correo_tienda'];
    $contrasena_aplicacion = $_POST['contrasena_aplicacion'] ?? '' ;
    $direccion_tienda = $_POST['direccion_tienda'] ?? '';
    $facebook = $_POST['facebook'];
    $instagram = $_POST['instagram'];

    // Archivos actuales o nuevos
    $logo_tienda = $_FILES['logo_tienda']['name'] ? $_FILES['logo_tienda']['name'] : $_POST['logo_actual'];
    $favicon_tienda = $_FILES['favicon_tienda']['name'] ? $_FILES['favicon_tienda']['name'] : $_POST['favicon_actual'];
    $portada_tienda = $_FILES['portada_tienda']['name'] ? $_FILES['portada_tienda']['name'] : $_POST['portada_actual'];
    $portada_tienda_celular = $_FILES['portada_tienda_celular']['name'] ? $_FILES['portada_tienda_celular']['name'] : $_POST['portada_actual_celular'];

    
    // Carpeta de destino
    $carpeta_destino = "uploads/tienda_imagenes/";

    // Función para subir imágenes
    function subirImagen($claveArchivo, $archivoActual, $carpeta_destino) {
        if ($_FILES[$claveArchivo]['name']) {
            $ruta_antigua = $carpeta_destino . $archivoActual;
            if (file_exists($ruta_antigua) && $archivoActual !== "") {
                unlink($ruta_antigua);
            }
            $ruta_nueva = $carpeta_destino . basename($_FILES[$claveArchivo]['name']);
            if (move_uploaded_file($_FILES[$claveArchivo]['tmp_name'], $ruta_nueva)) {
                return $_FILES[$claveArchivo]['name'];
            }
        }
        return $archivoActual;
    }

    // Subir imágenes (si hay nuevas)
    $logo_tienda = subirImagen('logo_tienda', $_POST['logo_actual'], $carpeta_destino);
    $favicon_tienda = subirImagen('favicon_tienda', $_POST['favicon_actual'], $carpeta_destino);
    $portada_tienda = subirImagen('portada_tienda', $_POST['portada_actual'], $carpeta_destino);
    $portada_tienda_celular = subirImagen('portada_tienda_celular', $_POST['portada_actual_celular'], $carpeta_destino);


    // Consulta para actualizar
    $query = "UPDATE personalizaciones_tienda SET 
                nombre_tienda = ?, 
                contacto_tienda = ?, 
                correo_tienda = ?, 
                contrasena_aplicacion = ?, 
                direccion_tienda = ?, 
                facebook = ?, 
                instagram = ?, 
                logo_tienda = ?, 
                favicon_tienda = ?, 
                portada_tienda = ?,
                portada_tienda_celular = ?

              WHERE id = 1";

    if ($stmt = $conexion->prepare($query)) {
        $stmt->bind_param("sssssssssss", 
            $nombre_tienda, 
            $contacto_tienda, 
            $correo_tienda, 
            $contrasena_aplicacion, 
            $direccion_tienda, 
            $facebook, 
            $instagram, 
            $logo_tienda, 
            $favicon_tienda, 
            $portada_tienda,
            $portada_tienda_celular
        );

        if ($stmt->execute()) {
            header("Location: ../?module=mi_tienda&success=true");
            exit();
        } else {
            echo "Error al ejecutar la consulta: " . $stmt->error;
        }
    } else {
        echo "Error al preparar la consulta: " . $conexion->error;
    }
}
?>
