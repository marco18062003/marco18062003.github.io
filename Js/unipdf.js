// Elementos del de la pagina web de la inipdf donde se guaradn las dirrecione de los archivos
const comicFrame = document.getElementById('comic-frame');
const comic1Btn = document.getElementById('comic1-btn');
const comic2Btn = document.getElementById('comic2-btn');
const comic3Btn = document.getElementById('comic3-btn');
const comic4Btn = document.getElementById('comic4-btn');
const comic5Btn = document.getElementById('comic5-btn');
const comic6Btn = document.getElementById('comic6-btn');
const comic7Btn = document.getElementById('comic7-btn');
const comic8Btn = document.getElementById('comic8-btn');
const comic9Btn = document.getElementById('comic9-btn');
const comic10Btn = document.getElementById('comic10-btn');
const comic11Btn = document.getElementById('comic11-btn');
const comic12Btn = document.getElementById('comic12-btn');
const comic13Btn = document.getElementById('comic13-btn');
const themeToggle = document.getElementById('theme-toggle');

// Función para cambiar el contenido del iframe
function loadComic(pdfUrl) {
  comicFrame.src = pdfUrl;
}

// Agregar eventos a los botones de selección de comics
comic1Btn.addEventListener('click', () => loadComic('../Assents/universidad/Semestre - copia/Prueba de uso y instalacion de tracker y jupyter.pdf'));
comic2Btn.addEventListener('click', () => loadComic('../Assents/universidad/Semestre - copia (2)/Contextos/calificar_estudiantes.pdf'));
comic3Btn.addEventListener('click', () => loadComic('../Assents/universidad/Semestre - copia (2)/Contextos/trabajo de observacion.pdf'));
comic4Btn.addEventListener('click', () => loadComic('../Assents/universidad/Semestre - copia (2)/Mecanica_clasica_i/Ejercicios Mec ́anica Cl ́asica I.pdf'));
comic5Btn.addEventListener('click', () => loadComic('../Assents/universidad/Semestre - copia (2)/Mecanica_clasica_i/fisica vol i.pdf'));
comic6Btn.addEventListener('click', () => loadComic('../Assents/universidad/Semestre - copia (2)/Mecanica_clasica_i/parcial1.pdf'));
comic7Btn.addEventListener('click', () => loadComic('../Assents/universidad/Semestre - copia (2)/Mecanica_clasica_i/Sear_Zemansky_Esapañol.pdf'));
comic8Btn.addEventListener('click', () => loadComic('../Assents/universidad/Semestre - copia (2)/\Tic_ii/trabajodetics.mp4'));
comic9Btn.addEventListener('click', () => loadComic('../Assents/universidad/Semestre - copia (2)/\Tic_ii/proyectos_tics/proyecto_latex/proyecto_semiparabolico (4) (1).pdf'));
comic10Btn.addEventListener('click', () => loadComic('../Assents/universidad/Semestre - copia (2)/Algebra_lineal/Claudio de J. Pita Ruiz.pdf'));
comic11Btn.addEventListener('click', () => loadComic('../Assents/universidad/Semestre - copia (2)/Contextos/trabajo observacion final.pdf'));
comic12Btn.addEventListener('click', () => loadComic('../Assents/universidad/Semestre - copia (2)/\Tic_ii/proyectos_tics/proyecto_latex/sim.pdf'));
comic13Btn.addEventListener('click', () => loadComic('../Assents/universidad/Semestre - copia (2)/\Tic_ii/proyectos_tics/proyecto_latex/sim.mp4'));

// Funcionalidad para cambiar el tema
themeToggle.addEventListener('click', () => {
  document.body.classList.toggle('dark-theme');
  if (document.body.classList.contains('dark-theme')) {
    document.body.style.backgroundColor = '#212121';
    document.body.style.color = '#333';
  } else {
    document.body.style.backgroundColor = '#f4f4f4';
    document.body.style.color = '#333';
  }
});
