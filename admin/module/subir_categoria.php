<form id="formularioCategoria" class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg border border-gray-200" method="post" enctype="multipart/form-data">
    <h2 class="text-3xl font-semibold mb-6 text-center text-gray-800">Agregar Nueva Categoría</h2>

    <!-- Campo: Nombre de la Categoría -->
    <div class="mb-6">
        <label for="nombre_categoria" class="block text-lg font-medium text-gray-700 mb-2">Nombre de la Categoría</label>
        <input type="text" id="nombre_categoria" name="nombre_categoria" required
            class="w-full p-4 border border-gray-300 rounded-lg text-gray-700 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Ej. Ropa, Zapatos, Accesorios">
    </div>

    <!-- Campo: Imagen de la Categoría -->
    <div class="mb-6">
        <label for="imagen_categoria" class="block text-lg font-medium text-gray-700 mb-2">Imagen de la Categoría</label>
        <input type="file" id="imagen_categoria" name="imagen_categoria" accept="image/*" required
            class="w-full p-2 border border-gray-300 rounded-lg text-gray-700 focus:ring-blue-500 focus:border-blue-500">
        <div id="previewImagen" class="mt-4 hidden">
            <p class="text-gray-600 mb-2">Vista previa:</p>
            <img id="imagenMostrada" src="" alt="Vista previa"
                class="w-40 h-40 object-cover rounded border border-gray-300">
        </div>
    </div>

    <!-- Botón: Guardar Categoría -->
    <div class="text-center">
        <button type="submit" id="btnGuardarCategoria"
            class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
            Guardar Categoría
        </button>
    </div>
</form>

<script>
document.getElementById("formularioCategoria").addEventListener("submit", function (event) {
    event.preventDefault();

    const btn = document.getElementById("btnGuardarCategoria");
    btn.disabled = true;
    btn.innerHTML = "Guardando...";

    const form = this;
    const datosFormulario = new FormData(form);

    // Validación simple de archivo
    const archivo = datosFormulario.get("imagen_categoria");
    if (!archivo || !archivo.type.startsWith("image/")) {
        Swal.fire({
            icon: "error",
            title: "Archivo inválido",
            text: "Por favor seleccioná una imagen válida.",
            confirmButtonColor: "#d33"
        });
        btn.disabled = false;
        btn.innerHTML = "Guardar Categoría";
        return;
    }

    fetch("./controllers/guardar_categoria.php", {
        method: "POST",
        body: datosFormulario
    })
    .then(async respuesta => {
        const contentType = respuesta.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error("La respuesta no es JSON.");
        }
        return await respuesta.json();
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: "success",
                title: "¡Categoría guardada!",
                text: data.message,
                confirmButtonColor: "#3085d6"
            }).then(() => {
                location.reload();
            });
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        console.error("Error al enviar los datos:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: error.message || "No se pudo completar la solicitud.",
            confirmButtonColor: "#d33"
        });
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = "Guardar Categoría";
    });
});

// Vista previa de imagen
document.getElementById("imagen_categoria").addEventListener("change", function () {
    const archivo = this.files[0];
    const vistaPrevia = document.getElementById("previewImagen");
    const imagen = document.getElementById("imagenMostrada");

    if (archivo && archivo.type.startsWith("image/")) {
        const lector = new FileReader();
        lector.onload = function (e) {
            imagen.src = e.target.result;
            vistaPrevia.classList.remove("hidden");
        };
        lector.readAsDataURL(archivo);
    } else {
        imagen.src = "";
        vistaPrevia.classList.add("hidden");
    }
});
</script>
