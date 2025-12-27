<form id="formularioSubcategoria" class="max-w-lg mx-auto p-6 bg-white shadow-md rounded-lg border border-gray-200">
    <h2 class="text-3xl font-semibold mb-6 text-center text-gray-800">Agregar Nueva Subcategoría</h2>

    <!-- Categoría principal -->
    <div class="mb-6">
        <label for="id_categoria" class="block text-lg font-medium text-gray-700 mb-2">Selecciona una Categoría</label>
        <select id="id_categoria" name="categoria_id" required class="w-full p-4 border border-gray-300 rounded-lg text-gray-700 focus:ring-blue-500 focus:border-blue-500">
            <option value="">-- Seleccionar Categoría --</option>
        </select>
    </div>

    <!-- Nombre de la Subcategoría -->
    <div class="mb-6">
        <label for="nombre_subcategoria" class="block text-lg font-medium text-gray-700 mb-2">Nombre de la Subcategoría</label>
        <input type="text" id="nombre_subcategoria" name="nombre_subcategoria" required class="w-full p-4 border border-gray-300 rounded-lg text-gray-700 focus:ring-blue-500 focus:border-blue-500" placeholder="Ej. Camisas, Botas, Gorras">
    </div>

    <!-- Imagen de la Subcategoría -->
    <div class="mb-6">
        <label for="imagen_subcategoria" class="block text-lg font-medium text-gray-700 mb-2">Imagen de la Subcategoría</label>
        <input type="file" id="imagen_subcategoria" name="imagen_subcategoria" accept="image/*" class="w-full p-4 border border-gray-300 rounded-lg text-gray-700 focus:ring-blue-500 focus:border-blue-500">
        <!-- Vista previa -->
        <div id="preview_imagen" class="mt-4 text-center hidden">
            <p class="text-gray-600 mb-2">Vista previa:</p>
            <img id="imagen_miniatura" src="" alt="Vista previa" class="mx-auto rounded-lg shadow-md max-h-56 object-contain border border-gray-300">
        </div>
    </div>

    <!-- Botón de guardar -->
    <div class="text-center">
        <button type="submit" class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
            Guardar Subcategoría
        </button>
    </div>
</form>

<script>
// Cargar categorías dinámicamente
document.addEventListener("DOMContentLoaded", function () {
    fetch("./controllers/obtener_categorias.php")
        .then(response => response.json())
        .then(data => {
            let select = document.getElementById("id_categoria");
            data.forEach(categoria => {
                let opcion = document.createElement("option");
                opcion.value = categoria.id;
                opcion.textContent = categoria.nombre;
                select.appendChild(opcion);
            });
        })
        .catch(error => {
            console.error("Error al cargar las categorías:", error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudieron cargar las categorías.",
                confirmButtonColor: "#d33"
            });
        });
});

// Vista previa de imagen
document.getElementById("imagen_subcategoria").addEventListener("change", function (event) {
    const archivo = event.target.files[0];
    const vistaPrevia = document.getElementById("preview_imagen");
    const miniatura = document.getElementById("imagen_miniatura");

    if (archivo) {
        const lector = new FileReader();
        lector.onload = function (e) {
            miniatura.src = e.target.result;
            vistaPrevia.classList.remove("hidden");
        };
        lector.readAsDataURL(archivo);
    } else {
        miniatura.src = "";
        vistaPrevia.classList.add("hidden");
    }
});

// Envío del formulario
document.getElementById("formularioSubcategoria").addEventListener("submit", function (evento) {
    evento.preventDefault();

    let datosFormulario = new FormData(this);

    fetch("./controllers/guardar_subcategoria.php", {
        method: "POST",
        body: datosFormulario
    })
    .then(response => {
        // Primero verificar el estado de la respuesta
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
            });
        }
        return response.json().catch(() => {
            // Si falla el parseo JSON, devolver el texto para diagnóstico
            return response.text().then(text => {
                throw new Error(`Respuesta no JSON: ${text}`);
            });
        });
    })
    .then(data => {
        if (data && data.success) {
            Swal.fire({
                icon: "success",
                title: "Subcategoría guardada",
                text: "La subcategoría se guardó correctamente.",
                confirmButtonColor: "#3085d6"
            }).then(() => {
                location.reload();
            });
        } else {
            throw new Error(data?.message || "Respuesta inesperada del servidor");
        }
    })
    .catch(error => {
        console.error("Error en la solicitud:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: error.message || "No se pudo conectar con el servidor.",
            confirmButtonColor: "#d33"
        });
    });
});
</script>
