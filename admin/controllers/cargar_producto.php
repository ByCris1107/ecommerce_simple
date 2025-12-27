<?php
// Incluir archivo de conexión
require_once '../conexion/base_de_datos.php';

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $nombre_producto = $_POST['nombre_producto'];
    $genero_producto = $_POST['genero_producto'];
    $categoria_id = $_POST['categoria'];
    $subcategoria_id = $_POST['subcategoria'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $estaciones = isset($_POST['estaciones']) ? implode(', ', $_POST['estaciones']) : '';
    
    // Manejo de imagen principal
    $ruta_db_portada = '';
    if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] === UPLOAD_ERR_OK) {
        $imagen_portada = $_FILES['imagen_portada'];
        $nombre_imagen_portada = uniqid() . '-' . basename($imagen_portada['name']);
        $directorio_destino = './uploads/imagenes_productos/portada/';
        if (!is_dir($directorio_destino)) {
            mkdir($directorio_destino, 0775, true);
        }
        $ruta_imagen_portada = $directorio_destino . $nombre_imagen_portada;
        $ruta_db_portada = 'uploads/imagenes_productos/portada/' . $nombre_imagen_portada;
        move_uploaded_file($imagen_portada['tmp_name'], $ruta_imagen_portada);
    }

    // Preparar SQL para insertar el producto
    $sql = "INSERT INTO productos (nombre, genero, categoria_id, subcategoria_id, descripcion, precio, imagen_portada, estaciones) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssisssss", $nombre_producto, $genero_producto, $categoria_id, $subcategoria_id, $descripcion, $precio, $ruta_db_portada, $estaciones);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        $producto_id = $stmt->insert_id;

        // Insertar variantes (talle, color, stock e imagen)
        if (isset($_POST['talle']) && isset($_POST['color']) && isset($_POST['stock']) && isset($_FILES['foto_color'])) {
            $talles = $_POST['talle'];
            $colores = $_POST['color'];
            $stocks = $_POST['stock'];
            $fotos_color = $_FILES['foto_color'];

            foreach ($talles as $index => $talle) {
                $color = $colores[$index];
                $stock = $stocks[$index];
                $foto_color = $fotos_color['tmp_name'][$index];

                if ($foto_color) {
                    $nombre_foto_color = uniqid() . '-' . basename($fotos_color['name'][$index]);
                    $directorio_destino_color = './uploads/imagenes_productos/variantes/';
                    if (!is_dir($directorio_destino_color)) {
                        mkdir($directorio_destino_color, 0775, true);
                    }
                    $ruta_foto_color = $directorio_destino_color . $nombre_foto_color;
                    $ruta_db_color = 'uploads/imagenes_productos/variantes/' . $nombre_foto_color;
                    move_uploaded_file($foto_color, $ruta_foto_color);
                } else {
                    $ruta_db_color = '';
                }

                // Insertar en la tabla variantes_producto
                $sql_variantes = "INSERT INTO variantes_producto (producto_id, talle, color, stock, foto_color) 
                                  VALUES (?, ?, ?, ?, ?)";
                $stmt_variantes = $conexion->prepare($sql_variantes);
                $stmt_variantes->bind_param("issis", $producto_id, $talle, $color, $stock, $ruta_db_color);
                $stmt_variantes->execute();
            }
        }

        // Mostrar mensaje de éxito y redireccionar
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Producto Cargado</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f5f5f5;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }
                .success-message {
                    background-color: #4CAF50;
                    color: white;
                    padding: 30px;
                    border-radius: 10px;
                    text-align: center;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                    max-width: 400px;
                    width: 100%;
                }
                .success-icon {
                    font-size: 60px;
                    margin-bottom: 20px;
                }
                .success-message h2 {
                    margin: 0 0 10px 0;
                }
                .success-message p {
                    margin: 0 0 20px 0;
                    font-size: 18px;
                }
                .loading-bar {
                    height: 5px;
                    background-color: rgba(255,255,255,0.3);
                    margin-top: 20px;
                    border-radius: 5px;
                    overflow: hidden;
                }
                .loading-progress {
                    height: 100%;
                    width: 0;
                    background-color: white;
                    animation: load 3s forwards;
                }
                @keyframes load {
                    to { width: 100%; }
                }
            </style>
        </head>
        <body>
            <div class="success-message">
                <div class="success-icon">✓</div>
                <h2>¡Éxito!</h2>
                <p>El producto ha sido cargado correctamente</p>
                <div class="loading-bar">
                    <div class="loading-progress"></div>
                </div>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = "../?module=ver_producto";
                }, 3000);
            </script>
        </body>
        </html>';
        exit();
    } else {
        // Mostrar error
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f5f5f5;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }
                .error-message {
                    background-color: #f44336;
                    color: white;
                    padding: 30px;
                    border-radius: 10px;
                    text-align: center;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                    max-width: 400px;
                    width: 100%;
                }
                .error-icon {
                    font-size: 60px;
                    margin-bottom: 20px;
                }
                .error-message h2 {
                    margin: 0 0 10px 0;
                }
                .error-message p {
                    margin: 0 0 20px 0;
                    font-size: 18px;
                }
            </style>
        </head>
        <body>
            <div class="error-message">
                <div class="error-icon">✗</div>
                <h2>Error</h2>
                <p>Ocurrió un error al cargar el producto: ' . htmlspecialchars($conexion->error) . '</p>
                <button onclick="window.history.back()" style="
                    background-color: white;
                    color: #f44336;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-weight: bold;
                    margin-top: 10px;
                ">Volver</button>
            </div>
        </body>
        </html>';
        exit();
    }

    $stmt->close();
    $conexion->close();
}
?>