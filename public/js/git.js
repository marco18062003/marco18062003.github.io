// script.js
document.querySelectorAll('.copyButton').forEach(button => {
    button.addEventListener('click', function() {
        const codeId = this.getAttribute('data-code'); // Obtener el ID del bloque de código
        const codeText = document.getElementById(codeId).textContent; // Obtener el contenido del código

        // Crear un "textarea" temporal para seleccionar el texto
        const textArea = document.createElement('textarea');
        textArea.value = codeText;  // Copiar el texto del código
        document.body.appendChild(textArea);

        // Seleccionar el contenido del textarea
        textArea.select();
        textArea.setSelectionRange(0, 99999); // Para dispositivos móviles

        // Intentar copiar al portapapeles
        try {
            document.execCommand('copy');  // Método obsoleto, pero aún funciona en muchos navegadores
            const status = document.getElementById('status-' + codeId);
            status.textContent = '¡Código copiado!';
            status.classList.add('show');

            // Eliminar el textarea temporal
            document.body.removeChild(textArea);

            // Ocultar el mensaje después de 2 segundos
            setTimeout(() => {
                status.classList.remove('show');
            }, 2000);
        } catch (err) {
            const status = document.getElementById('status-' + codeId);
            status.textContent = 'Error al copiar el código.';
            status.classList.add('show');
            setTimeout(() => {
                status.classList.remove('show');
            }, 2000);
        }
    });
});
