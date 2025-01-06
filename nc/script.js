// Obtener elementos del DOM
const exerciseForm = document.getElementById("exercise-form");
const exerciseTable = document.getElementById("exercise-table").getElementsByTagName("tbody")[0];

// Función para obtener la fecha actual
function getCurrentDate() {
    const date = new Date();
    return date.toISOString().split('T')[0];  // Devuelve la fecha en formato "YYYY-MM-DD"
}

// Función para agregar ejercicio
function addExercise(event) {
    event.preventDefault();

    const exerciseName = document.getElementById("exercise").value;
    const sets = document.getElementById("sets").value;
    const reps = document.getElementById("reps").value;
    const weight = document.getElementById("weight").value;
    const date = document.getElementById("date").value || getCurrentDate(); // Si no se pone una fecha, usar la actual
    const imageFile = document.getElementById("image").files[0];

    // Crear un objeto para el ejercicio
    const exercise = {
        date: date,
        exerciseName: exerciseName,
        sets: sets,
        reps: reps,
        weight: weight,
        image: imageFile ? URL.createObjectURL(imageFile) : null
    };

    // Agregar ejercicio a la tabla
    addExerciseToTable(exercise);

    // Guardar en localStorage
    saveExercisesToLocalStorage();

    // Limpiar los campos del formulario
    exerciseForm.reset();
}

// Función para agregar un ejercicio a la tabla
function addExerciseToTable(exercise) {
    const newRow = exerciseTable.insertRow();

    newRow.insertCell(0).textContent = exercise.date;
    newRow.insertCell(1).textContent = exercise.exerciseName;
    newRow.insertCell(2).textContent = exercise.sets;
    newRow.insertCell(3).textContent = exercise.reps;
    newRow.insertCell(4).textContent = exercise.weight;

    // Mostrar imagen si existe
    const imgCell = newRow.insertCell(5);
    if (exercise.image) {
        const img = document.createElement("img");
        img.src = exercise.image;
        img.classList.add("exercise-img");
        imgCell.appendChild(img);
    }

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
            saveExercisesToLocalStorage(); // Guardar después de eliminar
        }
    };
    newRow.insertCell(6).appendChild(deleteButton);
}

// Función para guardar los ejercicios en localStorage
function saveExercisesToLocalStorage() {
    const exercises = [];
    const rows = exerciseTable.rows;

    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].cells;
        const exercise = {
            date: cells[0].textContent,
            exerciseName: cells[1].textContent,
            sets: cells[2].textContent,
            reps: cells[3].textContent,
            weight: cells[4].textContent,
            image: cells[5].querySelector("img") ? cells[5].querySelector("img").src : null
        };
        exercises.push(exercise);
    }

    // Guardar el array de ejercicios en localStorage
    localStorage.setItem("exercises", JSON.stringify(exercises));
}

// Función para cargar los ejercicios desde localStorage
function loadExercisesFromLocalStorage() {
    const exercises = JSON.parse(localStorage.getItem("exercises"));
    if (exercises) {
        exercises.forEach(exercise => addExerciseToTable(exercise));
    }
}

// Cargar los ejercicios cuando la página se carga
window.onload = loadExercisesFromLocalStorage;

// Escuchar el evento de envío del formulario
exerciseForm.addEventListener("submit", addExercise);
