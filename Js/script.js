// script.js

// Función para cargar las palabras desde localStorage
function loadWords() {
    const wordList = JSON.parse(localStorage.getItem("words")) || [];
    const wordListElement = document.getElementById("wordList");
    
    // Limpiar la lista actual
    wordListElement.innerHTML = "";

    // Agregar las palabras guardadas a la lista
    wordList.forEach(item => {
        const listItem = document.createElement("li");
        listItem.innerHTML = `
            <strong>${item.word}</strong>: ${item.definition}
            <button class="delete-btn" onclick="deleteWord('${item.word}')">Eliminar</button>
        `;
        wordListElement.appendChild(listItem);
    });
}

// Función para eliminar una palabra con doble confirmación
function deleteWord(wordToDelete) {
    // Primer confirmación
    const confirmFirst = confirm("¿Estás seguro de que deseas eliminar esta palabra?");

    if (confirmFirst) {
        // Segunda confirmación
        const confirmSecond = confirm("¡Esta acción no se puede deshacer! ¿Seguro que deseas eliminarla?");

        if (confirmSecond) {
            // Eliminar la palabra
            let wordList = JSON.parse(localStorage.getItem("words")) || [];
            wordList = wordList.filter(item => item.word !== wordToDelete);

            localStorage.setItem("words", JSON.stringify(wordList));
            loadWords();
            alert("La palabra ha sido eliminada correctamente.");
        } else {
            alert("La eliminación ha sido cancelada.");
        }
    } else {
        alert("La eliminación ha sido cancelada.");
    }
}

// Cargar las palabras cuando la página se cargue
document.addEventListener("DOMContentLoaded", loadWords);

// Event listener para el formulario de añadir palabra
document.getElementById("addWordForm").addEventListener("submit", function(event) {
    event.preventDefault();

    const word = document.getElementById("word").value;
    const definition = document.getElementById("definition").value;

    if (word && definition) {
        // Crear un objeto con la palabra y la definición
        const newWord = { word, definition };

        // Obtener las palabras actuales de localStorage o crear una nueva lista vacía
        const wordList = JSON.parse(localStorage.getItem("words")) || [];

        // Agregar la nueva palabra a la lista
        wordList.push(newWord);

        // Guardar la lista actualizada en localStorage
        localStorage.setItem("words", JSON.stringify(wordList));

        // Cargar de nuevo la lista para mostrarla
        loadWords();

        // Limpiar los campos del formulario
        document.getElementById("word").value = "";
        document.getElementById("definition").value = "";
    }
});
