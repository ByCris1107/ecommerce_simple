
<footer class="bg-gray-900 text-white mt-20">
  <div class="max-w-7xl mx-auto px-6 py-12 grid grid-cols-1 md:grid-cols-4 gap-8">
    <!-- Logo / Descripción -->
    <div>
      <h3 class="text-xl font-bold mb-2">

      <?php echo htmlspecialchars($store_name); ?>

      </h3>
      <p class="text-sm text-gray-400">Ofrecemos productos con estilo, calidad y descuentos exclusivos. ¡Gracias por elegirnos!</p>
    </div>

    <!-- Navegación -->
    <div>
      <h4 class="text-lg font-semibold mb-2">Enlaces</h4>
      <ul class="space-y-2 text-sm text-gray-300">
        <li><a href="./" class="hover:text-white">Inicio</a></li>
        <li><a href="./?page=productos" class="hover:text-white">Productos</a></li>
        <li><a href="./?page=registro" class="hover:text-white">Registrarse</a></li>
      </ul>
    </div>

    <!-- Información de contacto -->
    <div>
      <h4 class="text-lg font-semibold mb-2">Contacto</h4>
      <ul class="space-y-2 text-sm text-gray-300">
      <li>
    <div class="font-medium flex items-center gap-2">
        <i class="fa-solid fa-envelope text-gray-600"></i>
        <?php echo ucfirst(htmlspecialchars($store_email)); ?>
    </div>
</li>
<li>
    <div class="font-medium flex items-center gap-2">
        <i class="fa-solid fa-phone text-gray-600"></i>
        <?php echo ucfirst(htmlspecialchars($store_contact)); ?>
    </div>
</li>
        </li>
      </ul>
    </div>

    <!-- Redes sociales -->
    <div>
  <h4 class="text-lg font-semibold mb-2">Síguenos</h4>
  <div class="flex space-x-4">
    <a href="<?php echo htmlspecialchars($store_facebook); ?>" target="_blank" class="hover:text-blue-400">
      <i class="fab fa-facebook-f"></i>
    </a>
    <a href="<?php echo htmlspecialchars($store_instagram); ?>" target="_blank" class="hover:text-pink-400">
  <i class="fab fa-instagram"></i>
</a>

  </div>
</div>
  </div>


  <!-- Línea inferior -->
  <div class="border-t border-gray-700 text-center text-sm py-4 text-gray-400">
  <p class="text-gray-500 text-sm">© <?php echo date('Y'); ?> Creado por <a href="https://zonacode.com" target="_blank" class="text-gray-300 hover:text-white">ZonaCode.com</a>. Todos los derechos reservados.</p>
  </div>
</footer>



<script src="./js/script.js"></script>
</body>
</html>