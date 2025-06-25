document.addEventListener('DOMContentLoaded', function () {
    // Configurar menús desplegables para clic
    const dropdowns = document.querySelectorAll('.dropdown');

    dropdowns.forEach(function (dropdown) {
        const button = dropdown.querySelector('.dropbtn');
        
        button.addEventListener('click', function () {
            const content = dropdown.querySelector('.dropdown-content');
            content.style.display = (content.style.display === 'block') ? 'none' : 'block';
        });
    });

    // Agregar cierre de los menús cuando se haga clic fuera de ellos
    window.addEventListener('click', function (e) {
        dropdowns.forEach(function (dropdown) {
            const button = dropdown.querySelector('.dropbtn');
            const content = dropdown.querySelector('.dropdown-content');

            if (!dropdown.contains(e.target)) {
                content.style.display = 'none';
            }
        });
    });
});
