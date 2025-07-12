<?php
// views/dashboard.php

// Asegurarse de que la sesión esté iniciada y $base_path esté definido.
// Esto es una salvaguarda. La lógica principal debe estar en public/index.php.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirigir si el usuario no está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_path . "/login");
    exit();
}

// Inicializar variables si no se han pasado desde el controlador
$username = htmlspecialchars($_SESSION['username'] ?? 'Usuario');
$documents = $documents ?? []; // Usa el operador null coalescing para mayor concisión
$csrf_token = $csrf_token ?? ''; // Asegúrate de que el token CSRF esté definido

// --- CONFIGURACIÓN DE TIPOS Y CATEGORÍAS (Mejor mover a un archivo de configuración) ---
// Tipos de archivo que pueden ser editados como texto
$editable_text_types = [
    'text/plain', 'text/javascript', 'application/javascript', 'application/x-python',
    'text/x-python', 'text/html', 'text/css', 'application/sql', 'text/x-sql',
    'application/json', 'application/xml', 'text/xml'
];

// Tipos de archivo Excel editables
$excel_types = [
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

// Categorías predefinidas para los documentos
$categories = [
    'General', 'Trabajo', 'Personal', 'Estudios', 'Finanzas', 'Legal',
    'Salud', 'Proyectos', 'Marketing', 'Desarrollo'
];

// Preparar el mensaje de subida si existe
$upload_message_html = '';
if (isset($_SESSION['upload_message'])) {
    $message_text = htmlspecialchars($_SESSION['upload_message']);
    $message_class = htmlspecialchars($_SESSION['upload_message_type'] ?? 'info');

    $upload_message_html = "<div class='message {$message_class}'>{$message_text}</div>";
    unset($_SESSION['upload_message']);
    unset($_SESSION['upload_message_type']);
}

// --- NUEVA LÓGICA: Manejo de la barra de búsqueda ---
$search_query = $_GET['search_query'] ?? ''; // Obtener la consulta de búsqueda desde la URL
$filtered_documents = $documents; // Inicialmente, todos los documentos

if (!empty($search_query)) {
    $search_query_lower = mb_strtolower($search_query, 'UTF-8'); // Convertir a minúsculas para búsqueda insensible a mayúsculas
    
    // Filtrar los documentos
    $filtered_documents = array_filter($documents, function($doc) use ($search_query_lower) {
        $title_lower = mb_strtolower($doc['title'] ?? '', 'UTF-8');
        $category_lower = mb_strtolower($doc['category_name'] ?? '', 'UTF-8'); // Asegurarse de que 'category_name' exista

        // Buscar si la consulta está en el título o en la categoría
        return strpos($title_lower, $search_query_lower) !== false ||
               strpos($category_lower, $search_query_lower) !== false;
    });
}
// Ahora, la tabla usará $filtered_documents para mostrar los resultados
$documents_to_display = $filtered_documents;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>G502 - Dashboard de Documentos</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_path); ?>/css/style.css">
    <style>
        /* Tus estilos CSS existentes */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f2f5;
            color: #333;
        }
        .container {
            max-width: 960px;
            margin: 20px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        h2, h3 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 25px;
        }
        .header-welcome {
            font-size: 1.8em;
            margin-bottom: 30px;
        }
        .message {
            padding: 12px 20px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            border: 1px solid transparent;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .message.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }

        .document-upload-section, .search-section {
            background-color: #fefefe;
            padding: 25px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .upload-form .form-group, .search-form .form-group {
            margin-bottom: 18px;
        }
        .upload-form label, .search-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #4a4a4a;
        }
        .upload-form input[type="text"],
        .upload-form select,
        .upload-form input[type="file"],
        .search-form input[type="text"] { /* Añadido selector para input de búsqueda */
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 1rem;
            line-height: 1.5;
        }
        .upload-form select {
            appearance: none;
            background-color: #fff;
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23000000%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-6.5%200-12.3%203.8-15.3%209.7a17.6%2017.6%200%200%200%203%2021.5l127.6%20127.9c5.7%205.7%2013.3%208.8%2021.2%208.8s15.5-3.1%2021.2-8.8l127.6-127.9c3.9-3.9%205.4-8.7%204.7-13.7.7-4.7-1.7-9.4-5.4-13z%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 12px;
            cursor: pointer;
        }
        .upload-form input[type="file"] {
            padding: 8px 0;
        }
        .form-text-help {
            font-size: 0.8em;
            color: #888;
            margin-top: 6px;
            display: block;
        }
        .btn-submit, .btn-search { /* Añadido selector para botón de búsqueda */
            display: block;
            width: 100%;
            padding: 12px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 25px;
        }
        .btn-submit:hover, .btn-search:hover {
            background-color: #218838;
        }
        .btn-search { /* Color específico para el botón de búsqueda */
            background-color: #007bff;
        }
        .btn-search:hover {
            background-color: #0056b3;
        }

        /* Estilos de la tabla de documentos */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            border: 1px solid #eee;
            padding: 12px 15px;
            text-align: left;
        }
        th {
            background-color: #e9ecef;
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #f1f3f5;
        }

        /* Estilos para botones de acción */
        .action-buttons a.button {
            padding: 7px 12px;
            margin-right: 5px;
            margin-bottom: 5px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.85em;
            transition: background-color 0.2s ease, transform 0.1s ease;
            white-space: nowrap;
            display: inline-block;
        }
        .action-buttons a.button:last-child {
            margin-right: 0;
        }

        /* Colores específicos para botones */
        .button.view { background-color: #007bff; color: white; }
        .button.view:hover { background-color: #0056b3; transform: translateY(-1px); }
        .button.download { background-color: #17a2b8; color: white; }
        .button.download:hover { background-color: #138496; transform: translateY(-1px); }
        .button.edit-text { background-color: #ffc107; color: #343a40; }
        .button.edit-text:hover { background-color: #e0a800; transform: translateY(-1px); }
        .button.edit-excel { background-color: #28a745; color: white; }
        .button.edit-excel:hover { background-color: #218838; transform: translateY(-1px); }
        .button.delete { background-color: #dc3545; color: white; }
        .button.delete:hover { background-color: #c82333; transform: translateY(-1px); }
        .button.logout {
            background-color: #6c757d;
            color: white;
            margin-top: 30px;
            display: block;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
            padding: 10px 25px;
            font-size: 1em;
        }
        .button.logout:hover { background-color: #5a6268; }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 20px;
            }
            table, thead, tbody, th, td, tr {
                display: block;
            }
            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            tr {
                border: 1px solid #ddd;
                margin-bottom: 15px;
                border-radius: 8px;
            }
            td {
                border: none;
                border-bottom: 1px solid #eee;
                position: relative;
                padding-left: 50%;
                text-align: right;
            }
            td:before {
                position: absolute;
                top: 0;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: bold;
                content: attr(data-label);
            }
            td:last-child {
                border-bottom: none;
            }
            .action-buttons {
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-end;
                padding-top: 10px;
                border-top: 1px solid #eee;
                margin-top: 10px;
            }
            .action-buttons a.button {
                flex-grow: 1;
                max-width: calc(50% - 10px);
                margin: 5px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php echo $upload_message_html; ?>

        <h2 class="header-welcome">Hola, <?php echo $username; ?>. ¡BIENVENIDO A G502!</h2>

        <div class="document-upload-section">
            <h3>Subir Nuevo Documento</h3>
            <form action="<?php echo htmlspecialchars($base_path); ?>/uploads" method="POST" enctype="multipart/form-data" class="upload-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="form-group">
                    <label for="document_title">Título del Documento:</label>
                    <input type="text" id="document_title" name="document_title" required maxlength="255" placeholder="Ej: Informe Anual 2024">
                    <small class="form-text-help">Ingresa un título claro y conciso para tu documento.</small>
                </div>

                <div class="form-group">
                    <label for="document_cat">Categoría:</label>
                    <select id="document_cat" name="document_cat" required>
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($categories as $cat_name): ?>
                            <option value="<?php echo htmlspecialchars($cat_name); ?>">
                                <?php echo htmlspecialchars($cat_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text-help">Organiza tus documentos por categoría.</small>
                </div>

                <div class="form-group">
                    <label for="document_file">Seleccionar Archivo:</label>
                    <input type="file" id="document_file" name="document_file" required
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.json,.xml,.html,.css,.js,.py,.sql">
                    <small class="form-text-help">Formatos permitidos: PDF, Word, Excel, PowerPoint, TXT, ZIP, y varios códigos. (Máx. 10MB)</small>
                </div>

                <button type="submit" class="btn-submit">Subir Documento</button>
            </form>
        </div>

        <hr>

        <div class="search-section">
            <h3>Buscar Documentos</h3>
            <form action="<?php echo htmlspecialchars($base_path); ?>/dashboard" method="GET" class="search-form">
                <div class="form-group">
                    <label for="search_query">Buscar por Título o Categoría:</label>
                    <input type="text" id="search_query" name="search_query"
                           placeholder="Ingresa palabra clave..."
                           value="<?php echo htmlspecialchars($search_query); ?>">
                    <small class="form-text-help">Ej: "informe 2023", "contrato legal", "proyecto".</small>
                </div>
                <button type="submit" class="btn-search">Buscar</button>
                <?php if (!empty($search_query)): ?>
                    <a href="<?php echo htmlspecialchars($base_path); ?>/dashboard" class="button btn-clear-search">Limpiar Búsqueda</a>
                <?php endif; ?>
            </form>
        </div>

        <hr>

        <h3>Tus Documentos Almacenados</h3>
        <?php if (empty($documents_to_display)): // Usar la variable filtrada aquí ?>
            <p>No se encontraron documentos que coincidan con tu búsqueda. ¡Prueba otra palabra clave o sube tu primer documento!</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Categoría</th>
                        <th>Tamaño</th>
                        <th>Fecha de Subida</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents_to_display as $doc): // Iterar sobre los documentos filtrados ?>
                        <tr>
                            <td data-label="Título"><?php echo htmlspecialchars($doc['title']); ?></td>
                            <td data-label="Tipo"><?php echo htmlspecialchars($doc['file_type']); ?></td>
                            <td data-label="Categoría"><?php echo htmlspecialchars($doc['category_name'] ?? 'N/A'); ?></td>
                            <td data-label="Tamaño"><?php echo round($doc['file_size'] / 1024, 2); ?> KB</td>
                            <td data-label="Fecha de Subida"><?php echo htmlspecialchars($doc['upload_date']); ?></td>
                            <td class="action-buttons" data-label="Acciones">
                                <a href="<?php echo htmlspecialchars($base_path); ?>/view_document/<?php echo htmlspecialchars($doc['id']); ?>" target="_blank" class="button view">Ver</a>
                                <a href="<?php echo htmlspecialchars($base_path); ?>/download_document/<?php echo htmlspecialchars($doc['id']); ?>" class="button download">Descargar</a>
                                <?php if (in_array($doc['file_type'], $editable_text_types)): ?>
                                    <a href="<?php echo htmlspecialchars($base_path); ?>/edit_text_document/<?php echo htmlspecialchars($doc['id']); ?>" class="button edit-text">Editar Texto</a>
                                <?php elseif (in_array($doc['file_type'], $excel_types)): ?>
                                    <a href="<?php echo htmlspecialchars($base_path); ?>/edit_excel_document/<?php echo htmlspecialchars($doc['id']); ?>" class="button edit-excel">Editar Excel</a>
                                <?php endif; ?>
                                <a href="<?php echo htmlspecialchars($base_path); ?>/delete_document/<?php echo htmlspecialchars($doc['id']); ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar «<?php echo htmlspecialchars($doc['title']); ?>»?');" class="button delete">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <p class="text-center">
            <a href="<?php echo htmlspecialchars($base_path); ?>/logout" class="button logout">Cerrar Sesión</a>
        </p>
    </div>
</body>
</html>