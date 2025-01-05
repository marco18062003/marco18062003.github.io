// toggleTable.js
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggle-table');
    const comicTableContainer = document.getElementById('comic-table-container');
    
    toggleButton.addEventListener('click', function() {
      comicTableContainer.classList.toggle('hidden');
      
      if (comicTableContainer.classList.contains('hidden')) {
        toggleButton.innerHTML = '<i class="fas fa-chevron-down"></i> Mostrar Cómics';
      } else {
        toggleButton.innerHTML = '<i class="fas fa-chevron-up"></i> Ocultar Cómics';
      }
    });
  });
  