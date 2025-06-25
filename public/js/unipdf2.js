document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const tableRows = document.querySelectorAll('.comic-table tbody tr'); // Seleccionamos todas las filas de la tabla
  
    // Escuchamos el evento de entrada en el campo de búsqueda
    searchInput.addEventListener('input', function () {
      const searchTerm = searchInput.value.toLowerCase(); // Convertimos el término de búsqueda a minúsculas
  
      // Iteramos sobre todas las filas de la tabla
      tableRows.forEach(function (row) {
        const cells = row.getElementsByTagName('td');
        const rowText = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(' '); // Unimos todo el texto de las celdas de cada fila
  
        // Si la fila contiene el término de búsqueda, la mostramos; si no, la ocultamos
        if (rowText.includes(searchTerm)) {
          row.style.display = ''; // Mostramos la fila
        } else {
          row.style.display = 'none'; // Ocultamos la fila
        }
      });
    });
  });
  