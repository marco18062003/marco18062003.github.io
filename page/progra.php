<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programaicion</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/finanzas.css">
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
                        <td>GIT Bash</td>
                        <td>COM</td>
                        <td>
                            <a href="git.html" target="_blank">www</a>
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

    <script src="../js/finanazas.js"></script>
</body>

</html>
