<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vocabulario Profesional</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/styles.css">
    <link rel="icon" href="../public/assents/icon/logo1.svg" type="image/x-icon">
</head>
<body>
    <div class="container">
        <header>
            <h1>Vocabulario Profesional</h1>
            <p>¡Crea y guarda tus palabras favoritas con definiciones!</p>
        </header>

        <main>
            <section class="form-container">
                <h2>Añadir Nueva Palabra</h2>
                <form id="addWordForm">
                    <div class="input-group">
                        <label for="word">Palabra</label>
                        <input type="text" id="word" placeholder="Introduce la palabra" required>
                    </div>
                    <div class="input-group">
                        <label for="definition">Definición</label>
                        <textarea id="definition" placeholder="Introduce la definición" required maxlength=""></textarea>
                    </div>
                    <button type="submit" class="btn">Añadir Palabra</button>
                </form>
            </section>

            <section class="word-list-container">
                <h2>Lista de Palabras</h2>
                <ul id="wordList"></ul>
            </section>
        </main>

        <footer>
            <p>&copy; 2024 Vocabulario Profesional. Todos los derechos reservados.</p>
        </footer>
    </div>

    <script src="../public/js/script.js"></script>
</body>
</html>
