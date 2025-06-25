<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entretenimiento</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/entre.css">
    <link rel="icon" href="../Assents/icon/logo1.svg" type="image/x-icon">
</head>

<body>
    <div class="container">
        <header>
            <h1>Gestión de Archivos</h1>
            <p>Visualiza y descarga archivos de forma eficiente.</p>
        </header>

        <!-- Tabla de archivos -->
        <section id="file-table">
            <table>
                <thead>
                    <tr>
                        <th>Nombre del Archivo</th>
                        <th>Tipo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="file-list">
                    <!-- Los archivos se cargarán aquí dinámicamente -->
                    <tr>
                        <td>IRON MAN 160<br> JULY 1982</td>
                        <td>PDF</td>
                        <td>
                            <button onclick="window.open('https://github.com/Huesos64/Huesos64.github.io/blob/master/Assents/Entretenimiento/Iron%20Man%20160%20(July%201982).pdf', '_blank')">
                                <i class="fas fa-eye"></i> Ver
                            </button>
                            
                            <a href="Assents/Entretenimiento/Iron Man 160 (July 1982).pdf" download>
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>cancion.mp3</td>
                        <td>MP3</td>
                        <td>
                            <button onclick="viewFile('http://localhost/mi-app/mis-archivos/cancion.mp3')">
                                <i class="fas fa-eye"></i> Ver
                            </button>
                            <a href="http://localhost/mi-app/mis-archivos/cancion.mp3" download>
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </div>

    <!-- Modal para visualizar los archivos -->
    <div id="file-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <div id="modal-body"></div>
        </div>
    </div>

    <script src="../Js/entre.js"></script>
</body>

</html>
