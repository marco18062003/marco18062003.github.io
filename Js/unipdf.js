// Elementos del DOM
const comicFrame = document.getElementById('comic-frame');
const comic1Btn = document.getElementById('comic1-btn');
const comic2Btn = document.getElementById('comic2-btn');
const comic3Btn = document.getElementById('comic3-btn');
const comic4Btn = document.getElementById('comic4-btn');
const themeToggle = document.getElementById('theme-toggle');

// Función para cambiar el contenido del iframe
function loadComic(pdfUrl) {
  comicFrame.src = pdfUrl;
}

// Agregar eventos a los botones de selección de comics
comic1Btn.addEventListener('click', () => loadComic('xxx.pdf'));
comic2Btn.addEventListener('click', () => loadComic('2xxx.pdf'));
comic3Btn.addEventListener('click', () => loadComic('xxx.pdf'));
comic4Btn.addEventListener('click', () => loadComic('../Assents/universidad/Semestre - copia (2)/Mecanica_clasica_i/fisica vol i.pdf'));


// Funcionalidad para cambiar el tema
themeToggle.addEventListener('click', () => {
  document.body.classList.toggle('dark-theme');
  if (document.body.classList.contains('dark-theme')) {
    document.body.style.backgroundColor = '#212121';
    document.body.style.color = '#fff';
  } else {
    document.body.style.backgroundColor = '#f4f4f4';
    document.body.style.color = '#333';
  }
});
