$(document).ready(function() {
    // Actualizar la cantidad de productos en el carrito
    function actualizarCantidadCarrito() {
      let cantidad = localStorage.getItem('cantidadCarrito') || 0;
      $('#cantidad-carrito').text(cantidad);
    }
  
    actualizarCantidadCarrito();
  
    // Evento para agregar productos al carrito
    $('.agregar-al-carrito').click(function() {
      let cantidad = localStorage.getItem('cantidadCarrito') || 0;
      cantidad++;
      localStorage.setItem('cantidadCarrito', cantidad);
      actualizarCantidadCarrito();
    });
  });