// Obtener elementos del DOM
const sectionForm = document.getElementById("section-form");
const exerciseForm = document.getElementById("exercise-form");
const exerciseTable = document.getElementById("exercise-table").getElementsByTagName("tbody")[0];

const newSectionButton = document.getElementById("new-section-button");  // Botón para nueva sección

let currentSection = ''; // Variable para almacenar la sección actual
let sectionDate = '';    // Variable para almacenar la fecha de la sección

// Función para obtener la fecha actual
function getCurrentDate() {
    const date = new Date();
    return date.toISOString().split('T')[0]; // Devuelve la fecha en formato "YYYY-MM-DD"
}

// Función para manejar el formulario de sección
sectionForm.addEventListener("submit", function (event) {
    event.preventDefault();

    currentSection = document.getElementById("section").value.trim();
    sectionDate = document.getElementById("section-date").value || getCurrentDate(); // Si no se pone una fecha, usar la actual

    if (!currentSection || !sectionDate) {
        alert("Por favor ingresa una sección y una fecha.");
        return;
    }

    // Ocultar el formulario de sección y mostrar el de ejercicios
    document.getElementById("section-form").style.display = 'none';
    document.getElementById("exercise-form").style.display = 'block';
    document.getElementById("exercise-form").reset();
});

// Función para agregar ejercicio
function addExercise(event) {
    event.preventDefault();

    const exerciseName = document.getElementById("exercise").value;
    const sets = document.getElementById("sets").value;
    const reps = document.getElementById("reps").value;
    const weight = document.getElementById("weight").value;

    // Crear un objeto para el ejercicio
    const exercise = {
        date: sectionDate, // Usar la fecha de la sección
        section: currentSection,
        exerciseName: exerciseName,
        sets: sets,
        reps: reps,
        weight: weight
    };

    // Agregar ejercicio a la tabla
    addExerciseToTable(exercise);

    // Guardar el ejercicio en el localStorage
    saveExercisesToLocalStorage();

    // Limpiar los campos del formulario
    exerciseForm.reset();
}

// Función para agregar un ejercicio a la tabla
function addExerciseToTable(exercise) {
    const newRow = exerciseTable.insertRow();

    newRow.insertCell(0).textContent = exercise.date;
    newRow.insertCell(1).textContent = exercise.section;
    newRow.insertCell(2).textContent = exercise.exerciseName;
    newRow.insertCell(3).textContent = exercise.sets;
    newRow.insertCell(4).textContent = exercise.reps;
    newRow.insertCell(5).textContent = exercise.weight;

    // Crear un botón de eliminar
    const deleteButton = document.createElement("button");
    deleteButton.textContent = "Eliminar";
    deleteButton.onclick = () => {
        let confirmDelete = true;

        // Preguntar tres veces antes de eliminar
        for (let i = 0; i < 3; i++) {
            confirmDelete = confirm("¿Estás seguro de que deseas eliminar este ejercicio?");
            if (!confirmDelete) {
                break;  // Si el usuario cancela, salimos del ciclo
            }
        }

        if (confirmDelete) {
            exerciseTable.deleteRow(newRow.rowIndex - 1);
            // Después de eliminar, guardamos los cambios en localStorage
            saveExercisesToLocalStorage();
        }
    };
    newRow.insertCell(6).appendChild(deleteButton);
}

// Función para guardar los ejercicios en el localStorage
function saveExercisesToLocalStorage() {
    const exercises = [];

    // Recolectar los datos de los ejercicios de la tabla
    const rows = exerciseTable.rows;
    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].cells;
        const exercise = {
            date: cells[0].textContent,
            section: cells[1].textContent,
            exerciseName: cells[2].textContent,
            sets: cells[3].textContent,
            reps: cells[4].textContent,
            weight: cells[5].textContent,
        };
        exercises.push(exercise);
    }

    // Guardar los ejercicios en localStorage
    localStorage.setItem('exercises', JSON.stringify(exercises));
}

// Función para cargar los ejercicios desde localStorage
function loadExercisesFromLocalStorage() {
    const exercises = JSON.parse(localStorage.getItem('exercises'));

    if (exercises) {
        exercises.forEach(exercise => addExerciseToTable(exercise));
    }
}

// Escuchar el evento de envío del formulario de ejercicio
exerciseForm.addEventListener("submit", addExercise);

// Función para generar el archivo Word con el historial de ejercicios
function generateWord() {
    const exercises = [];

    // Recolectar los datos de los ejercicios de la tabla
    const rows = exerciseTable.rows;
    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].cells;
        const exercise = {
            date: cells[0].textContent,
            section: cells[1].textContent,
            exerciseName: cells[2].textContent,
            sets: cells[3].textContent,
            reps: cells[4].textContent,
            weight: cells[5].textContent,
        };
        exercises.push(exercise);
    }

    // Crear contenido en formato de texto para el archivo .docx
    let content = "Historial de Ejercicios\n\n";
    exercises.forEach(exercise => {
        content += `Fecha: ${exercise.date}\n`;
        content += `Seccion: ${exercise.section}\n`;
        content += `Ejercicio: ${exercise.exerciseName}\n`;
        content += `Series: ${exercise.sets}\n`;
        content += `Repeticiones: ${exercise.reps}\n`;
        content += `Peso: ${exercise.weight} kg\n\n`;
    });

    // Crear un Blob con el contenido
    const blob = new Blob([content], { type: "application/msword" });

    // Guardar el archivo .docx
    saveAs(blob, "historial_ejercicios.docx");
}

// Escuchar el evento del botón de generar archivo Word
document.getElementById("generate-word-button").addEventListener("click", generateWord);

// Función para crear una nueva sección
newSectionButton.addEventListener("click", function () {
    // Mostrar el formulario de sección y ocultar el de ejercicios
    document.getElementById("section-form").style.display = 'block';
    document.getElementById("exercise-form").style.display = 'none';
    newSectionButton.style.display = 'none'; // Ocultar el botón de nueva sección

    // Restablecer los valores para una nueva sección
    currentSection = '';
    sectionDate = '';
    document.getElementById("section-form").reset();
});

// Cargar los ejercicios desde el localStorage cuando se carga la página
window.addEventListener('load', loadExercisesFromLocalStorage);
