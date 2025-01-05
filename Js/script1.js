// Esperar que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    const table = document.querySelector('.links-table');
    
    // Aplicar animación de fade-in a la tabla al cargarse
    table.style.opacity = 0;
    table.style.transition = "opacity 1s ease-in";
    
    setTimeout(function() {
        table.style.opacity = 1;
    }, 300);
});
