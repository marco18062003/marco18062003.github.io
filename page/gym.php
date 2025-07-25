<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GYM</title>
    <link rel="stylesheet" href="../public/css/1.css">
    <link rel="icon" href="../publuc/assents/icon/logo1.svg" type="image/x-icon">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script defer src="../public/js/1.js"></script>
</head>
<body>
    <div class="container">
        <h1>Mi Registro en el Gimnasio</h1>

        <!-- Sección para seleccionar tipo de sección y la fecha -->
        <form id="section-form">
            <label for="section">Sección de Ejercicio:</label>
            <input type="text" id="section" placeholder="ingresar" required>
            
            <label for="section-date">Fecha de la Sección:</label>
            <input type="date" id="section-date" value="" required>

            <button type="submit">Iniciar Sección</button>
        </form>

        <!-- Formulario de Ejercicio -->
        <form id="exercise-form" style="display: none;">
            <label for="exercise">Ejercicio:</label>
            <input type="text" id="exercise" required>
            
            <label for="sets">Series:</label>
            <input type="number" id="sets" required>
            
            <label for="reps">Repeticiones:</label>
            <input type="text" id="reps" required>
            
            <label for="weight">Peso (kg):</label>
            <input type="number" id="weight" required>
            
            <button type="submit">Agregar Ejercicio</button>
        </form>

        <h2>Mi Historial de Ejercicios</h2>
        <table id="exercise-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Sección</th>
                    <th>Ejercicio</th>
                    <th>Series</th>
                    <th>Repeticiones</th>
                    <th>Peso (kg)</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <!-- Aquí se agregarán las filas de ejercicios -->
            </tbody>
        </table>

        <!-- Botón para generar el archivo Word -->
        <button id="generate-word-button">Generar Word</button>

        <!-- Botón para crear nueva sección -->
        <button id="new-section-button" style="display: none;">Crear Nueva Sección</button>
    </div>
    <!-- Incluir JSZip -->

</body>
</html>
