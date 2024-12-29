// script.js

// Efecto para resaltar las filas cuando el ratón pasa por encima
document.querySelectorAll('tr').forEach(row => {
    row.addEventListener('mouseover', () => {
        row.style.backgroundColor = '#e9f5ff';
    });

    row.addEventListener('mouseout', () => {
        row.style.backgroundColor = '';
    });
});

// Animación para mostrar las filas de la tabla con un retraso
document.addEventListener('DOMContentLoaded', () => {
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach((row, index) => {
        setTimeout(() => {
            row.style.opacity = 1;
            row.style.transition = "opacity 0.6s ease";
        }, index * 100); // Animación escalonada
    });
});
