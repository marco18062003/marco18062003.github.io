function viewFile(url) {
    const modalBody = document.getElementById('modal-body');

    // Obtén la extensión del archivo
    const fileExtension = url.split('.').pop().toLowerCase();

    // Si es un archivo PDF
    if (fileExtension === 'pdf') {
        const iframe = document.createElement('iframe');
        iframe.src = url;
        iframe.width = '100%';
        iframe.height = '600px';
        modalBody.innerHTML = '';  // Limpiar cualquier contenido previo
        modalBody.appendChild(iframe);
    }
    // Si es un archivo MP3
    else if (fileExtension === 'mp3') {
        const audio = document.createElement('audio');
        audio.src = url;
        audio.controls = true;
        modalBody.innerHTML = '';  // Limpiar cualquier contenido previo
        modalBody.appendChild(audio);
    } else {
        modalBody.innerHTML = `<p>Este tipo de archivo no se puede visualizar directamente.</p>`;
    }

    // Mostrar el modal
    const modal = document.getElementById('file-modal');
    modal.style.display = 'flex';

    // Cerrar el modal
    const closeBtn = document.querySelector('.close-btn');
    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });
}
