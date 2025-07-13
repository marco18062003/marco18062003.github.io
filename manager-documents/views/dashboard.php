<?php
// views/dashboard.php

// Asegurarse de que la sesión esté iniciada y $base_path esté definido.
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
$documents = $documents ?? [];
$csrf_token = $csrf_token ?? '';

// --- CONFIGURACIÓN DE TIPOS Y CATEGORÍAS (Mejor mover a un archivo de configuración) ---
$editable_text_types = [
    'text/plain', 'text/javascript', 'application/javascript', 'application/x-python',
    'text/x-python', 'text/html', 'text/css', 'application/sql', 'text/x-sql',
    'application/json', 'application/xml', 'text/xml'
];

$excel_types = [
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

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

// --- LÓGICA: Manejo de la barra de búsqueda ---
$search_query = $_GET['search_query'] ?? '';
$filtered_documents = $documents;

if (!empty($search_query)) {
    $search_query_lower = mb_strtolower($search_query, 'UTF-8');

    $filtered_documents = array_filter($documents, function($doc) use ($search_query_lower) {
        $title_lower = mb_strtolower($doc['title'] ?? '', 'UTF-8');
        $category_lower = mb_strtolower($doc['category_name'] ?? '', 'UTF-8');

        return strpos($title_lower, $search_query_lower) !== false ||
               strpos($category_lower, $search_query_lower) !== false;
    });
}
$documents_to_display = $filtered_documents;

// Determinar la pestaña activa basada en la URL o un parámetro
$active_tab = $_GET['tab'] ?? 'upload'; // Por defecto, la pestaña de subir

// Si se ha realizado una búsqueda, activa la pestaña de documentos
if (!empty($search_query) && $active_tab == 'upload') { // Solo cambia si la búsqueda se inicia desde otra pestaña
    $active_tab = 'documents';
}

// Si hay un mensaje de subida, activa la pestaña de subir por defecto
if (!empty($upload_message_html) && $active_tab == 'documents') { // Si ya se estaba en documentos, se queda
     $active_tab = 'upload';
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>G502 - Dashboard de Documentos</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($base_path); ?>/css/style.css">
    <style>
        /* Tus estilos CSS existentes (manténlos todos para la base) */
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

        /* --- Estilos para las Pestañas (Tabs) --- */
        .tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
        }
        .tab-button {
            padding: 15px 25px;
            cursor: pointer;
            border: none;
            background-color: transparent;
            font-size: 1.1em;
            font-weight: 600;
            color: #555;
            transition: color 0.3s ease, border-bottom 0.3s ease;
            position: relative;
            outline: none;
        }
        .tab-button:hover {
            color: #007bff;
        }
        .tab-button.active {
            color: #007bff;
            border-bottom: 3px solid #007bff;
        }
        .tab-content {
            display: none; /* Ocultar todas las pestañas por defecto */
            padding: 25px;
            border: 1px solid #e0e0e0;
            border-top: none; /* No queremos doble borde aquí si hay una barra de tabs */
            border-radius: 0 0 8px 8px;
            background-color: #fefefe;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .tab-content.active {
            display: block; /* Mostrar la pestaña activa */
        }

        /* Estilos para formularios y botones (existentes) */
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
        .search-form input[type="text"] {
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
        .btn-submit, .btn-search, .btn-clear-search { /* Añadido .btn-clear-search */
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
            text-align: center; /* Para los botones que son enlaces */
            text-decoration: none; /* Para los botones que son enlaces */
        }
        .btn-submit:hover {
            background-color: #218838;
        }
        .btn-search {
            background-color: #007bff;
        }
        .btn-search:hover {
            background-color: #0056b3;
        }
        .btn-clear-search {
            background-color: #6c757d; /* Gris para limpiar búsqueda */
            margin-top: 10px; /* Un poco de espacio */
        }
        .btn-clear-search:hover {
            background-color: #5a6268;
        }


        /* Estilos de la tabla de documentos (existentes) */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px; /* Ajustado para el contexto de las pestañas */
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

        /* Estilos para botones de acción (existentes) */
        .action-buttons a.button, .action-buttons button.button { /* Añadido button.button */
            padding: 7px 12px;
            margin-right: 5px;
            margin-bottom: 5px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.85em;
            transition: background-color 0.2s ease, transform 0.1s ease;
            white-space: nowrap;
            display: inline-block;
            border: none; /* Añadido para los botones */
            cursor: pointer; /* Añadido para los botones */
        }
        .action-buttons a.button:last-child, .action-buttons button.button:last-child {
            margin-right: 0;
        }

        /* Colores específicos para botones (existentes) */
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

        /* Estilos para el Modal de Contraseña */
        .modal {
            display: none; /* Oculto por defecto */
            position: fixed; /* Posición fija para cubrir toda la pantalla */
            z-index: 1000; /* Alto z-index para estar sobre todo lo demás */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto; /* Habilitar scroll si el contenido es grande */
            background-color: rgba(0,0,0,0.6); /* Fondo semi-transparente */
            backdrop-filter: blur(5px); /* Efecto de desenfoque */
            -webkit-backdrop-filter: blur(5px); /* Para compatibilidad */
            justify-content: center; /* Centrar el contenido horizontalmente */
            align-items: center; /* Centrar el contenido verticalmente */
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto; /* Centrar en la pantalla */
            padding: 30px;
            border: 1px solid #888;
            width: 80%; /* Ancho del modal */
            max-width: 500px; /* Ancho máximo para no ser demasiado grande */
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
            animation: fadeInScale 0.3s ease-out; /* Animación de entrada */
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            right: 20px;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .modal-close-button:hover,
        .modal-close-button:focus {
            color: #333;
            text-decoration: none;
            cursor: pointer;
        }

        .modal h3 {
            margin-top: 0;
            margin-bottom: 25px;
            color: #2c3e50;
            text-align: center;
        }

        .modal .form-group {
            margin-bottom: 20px;
        }

        .modal label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #4a4a4a;
        }

        .modal input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 1rem;
            line-height: 1.5;
        }

        .modal .btn-action {
            display: block;
            width: 100%;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-align: center;
            text-decoration: none;
            color: white;
        }
        .modal .btn-save { background-color: #007bff; }
        .modal .btn-save:hover { background-color: #0056b3; }
        .modal .btn-remove { background-color: #dc3545; margin-top: 15px; }
        .modal .btn-remove:hover { background-color: #c82333; }
        .modal .btn-cancel { background-color: #6c757d; margin-top: 15px; }
        .modal .btn-cancel:hover { background-color: #5a6268; }

        /* Estilo para el botón de proteger/desproteger */
        .button.protect { background-color: #ffc107; color: #333; } /* Amarillo */
        .button.protect:hover { background-color: #e0a800; transform: translateY(-1px); }
        .button.unprotect { background-color: #17a2b8; color: white; } /* Azul turquesa */
        .button.unprotect:hover { background-color: #138496; transform: translateY(-1px); }


        /* Responsive adjustments (existentes) */
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 20px;
            }
            .tabs {
                flex-direction: column;
                border-bottom: none;
            }
            .tab-button {
                width: 100%;
                border-bottom: 1px solid #e0e0e0;
                margin-bottom: 5px;
            }
            .tab-button.active {
                border-bottom: 3px solid #007bff;
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
            .action-buttons a.button, .action-buttons button.button {
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

        <div class="tabs">
            <button class="tab-button <?php echo ($active_tab === 'upload') ? 'active' : ''; ?>" onclick="showTab('upload')">Subir Documento</button>
            <button class="tab-button <?php echo ($active_tab === 'search') ? 'active' : ''; ?>" onclick="showTab('search')">Buscar Documentos</button>
            <button class="tab-button <?php echo ($active_tab === 'documents') ? 'active' : ''; ?>" onclick="showTab('documents')">Mis Documentos</button>
        </div>

        <div id="tab-upload" class="tab-content <?php echo ($active_tab === 'upload') ? 'active' : ''; ?>">
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
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.json,.xml,.html,.css,.js,.py,.sql,.cbr,.cbz">
                    <small class="form-text-help">Formatos permitidos: PDF, Word, Excel, PowerPoint, TXT, ZIP, código, cómics. (Máx. 10MB)</small>
                </div>

                <button type="submit" class="btn-submit">Subir Documento</button>
            </form>
        </div>

        <div id="tab-search" class="tab-content <?php echo ($active_tab === 'search') ? 'active' : ''; ?>">
            <h3>Buscar Documentos</h3>
            <form action="<?php echo htmlspecialchars($base_path); ?>/dashboard" method="GET" class="search-form">
                <input type="hidden" name="tab" value="documents"> <div class="form-group">
                    <label for="search_query">Buscar por Título o Categoría:</label>
                    <input type="text" id="search_query" name="search_query"
                            placeholder="Ingresa palabra clave..."
                            value="<?php echo htmlspecialchars($search_query); ?>">
                    <small class="form-text-help">Ej: "informe 2023", "contrato legal", "proyecto".</small>
                </div>
                <button type="submit" class="btn-search">Buscar</button>
                <?php if (!empty($search_query)): ?>
                    <a href="<?php echo htmlspecialchars($base_path); ?>/dashboard?tab=documents" class="btn-clear-search">Limpiar Búsqueda</a>
                <?php endif; ?>
            </form>
        </div>

        <div id="tab-documents" class="tab-content <?php echo ($active_tab === 'documents') ? 'active' : ''; ?>">
            <h3>Tus Documentos Almacenados</h3>
            <?php if (empty($documents_to_display)): ?>
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
                        <?php foreach ($documents_to_display as $doc): ?>
                            <tr>
                                <td data-label="Título"><?php echo htmlspecialchars($doc['title']); ?></td>
                                <td data-label="Tipo"><?php echo htmlspecialchars($doc['file_type']); ?></td>
                                <td data-label="Categoría"><?php echo htmlspecialchars($doc['category_name'] ?? 'N/A'); ?></td>
                                <td data-label="Tamaño"><?php echo round($doc['file_size'] / 1024, 2); ?> KB</td>
                                <td data-label="Fecha de Subida"><?php echo htmlspecialchars($doc['upload_date']); ?></td>
                                <td class="action-buttons" data-label="Acciones">
                                    <?php if ($doc['is_protected']): ?>
                                        <button type="button" class="button view"
                                            onclick="openPasswordModal(<?php echo htmlspecialchars($doc['id']); ?>, '<?php echo htmlspecialchars(addslashes($doc['title'])); ?>', true, 'verify_password')">
                                            Ver (Protegido)
                                        </button>
                                        <button type="button" class="button download"
                                            onclick="openPasswordModal(<?php echo htmlspecialchars($doc['id']); ?>, '<?php echo htmlspecialchars(addslashes($doc['title'])); ?>', true, 'verify_password')">
                                            Descargar (Protegido)
                                        </button>
                                    <?php else: ?>
                                        <a href="<?php echo htmlspecialchars($base_path); ?>/view_document/<?php echo htmlspecialchars($doc['id']); ?>" target="_blank" class="button view">Ver</a>
                                        <a href="<?php echo htmlspecialchars($base_path); ?>/download_document/<?php echo htmlspecialchars($doc['id']); ?>" class="button download">Descargar</a>
                                    <?php endif; ?>

                                    <?php if (in_array($doc['file_type'], $editable_text_types)): ?>
                                        <a href="<?php echo htmlspecialchars($base_path); ?>/edit_text_document/<?php echo htmlspecialchars($doc['id']); ?>" class="button edit-text">Editar Texto</a>
                                    <?php elseif (in_array($doc['file_type'], $excel_types)): ?>
                                        <a href="<?php echo htmlspecialchars($base_path); ?>/edit_excel_document/<?php echo htmlspecialchars($doc['id']); ?>" class="button edit-excel">Editar Excel</a>
                                    <?php endif; ?>

                                    <button type="button" class="button <?php echo $doc['is_protected'] ? 'unprotect' : 'protect'; ?>"
                                        onclick="openPasswordModal(<?php echo htmlspecialchars($doc['id']); ?>, '<?php echo htmlspecialchars(addslashes($doc['title'])); ?>', <?php echo $doc['is_protected'] ? 'true' : 'false'; ?>, 'set_password')">
                                        <?php echo $doc['is_protected'] ? 'Desproteger' : 'Proteger'; ?>
                                    </button>

                                    <a href="<?php echo htmlspecialchars($base_path); ?>/delete_document/<?php echo htmlspecialchars($doc['id']); ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar «<?php echo htmlspecialchars($doc['title']); ?>»?');" class="button delete">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <p class="text-center">
            <a href="<?php echo htmlspecialchars($base_path); ?>/logout" class="button logout">Cerrar Sesión</a>
        </p>
    </div>

    <div id="documentPasswordModal" class="modal">
        <div class="modal-content">
            <span class="modal-close-button" onclick="closePasswordModal()">&times;</span>
            <h3 id="modalTitle">Configurar Contraseña de Documento</h3>
            <form id="documentPasswordForm" action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <input type="hidden" name="document_id" id="modalDocumentId">
                <input type="hidden" name="action_type" id="modalActionType"> <div id="passwordInputGroup" class="form-group">
                    <label for="document_access_password">Contraseña:</label>
                    <input type="password" id="document_access_password" name="document_access_password" required>
                </div>

                <div id="confirmPasswordInputGroup" class="form-group" style="display: none;">
                    <label for="document_confirm_password">Confirmar Contraseña:</label>
                    <input type="password" id="document_confirm_password" name="document_confirm_password">
                </div>

                <button type="submit" class="btn-action btn-save" id="modalSubmitButton">Establecer Contraseña</button>
                <button type="button" class="btn-action btn-remove" id="removePasswordButton" style="display: none;" onclick="removeDocumentProtection()">Quitar Protección</button>
                <button type="button" class="btn-action btn-cancel" onclick="closePasswordModal()">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        // Tu función showTab existente...
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(function(tabContent) {
                tabContent.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(function(tabButton) {
                tabButton.classList.remove('active');
            });
            document.getElementById('tab-' + tabId).classList.add('active');
            document.querySelector('.tab-button[onclick="showTab(\'' + tabId + '\')"]').classList.add('active');
            history.pushState(null, '', '<?php echo htmlspecialchars($base_path); ?>/dashboard?tab=' + tabId);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const activeTabFromUrl = urlParams.get('tab');
            if (activeTabFromUrl) {
                showTab(activeTabFromUrl);
            } else {
                showTab('<?php echo $active_tab; ?>');
            }
        });

        // --- Funciones para el Modal de Contraseña ---
        const documentPasswordModal = document.getElementById('documentPasswordModal');
        const modalDocumentId = document.getElementById('modalDocumentId');
        const modalActionType = document.getElementById('modalActionType');
        const modalTitle = document.getElementById('modalTitle');
        const passwordInputGroup = document.getElementById('passwordInputGroup');
        const confirmPasswordInputGroup = document.getElementById('confirmPasswordInputGroup');
        const documentAccessPassword = document.getElementById('document_access_password');
        const documentConfirmPassword = document.getElementById('document_confirm_password');
        const modalSubmitButton = document.getElementById('modalSubmitButton');
        const removePasswordButton = document.getElementById('removePasswordButton');
        const documentPasswordForm = document.getElementById('documentPasswordForm');

        function openPasswordModal(docId, docTitle, currentProtectionStatus, action) {
            documentPasswordModal.style.display = 'flex'; // Usar flex para centrar
            modalDocumentId.value = docId;
            modalActionType.value = action;
            documentAccessPassword.value = ''; // Limpiar campos
            documentConfirmPassword.value = '';

            if (action === 'set_password') {
                modalTitle.innerText = `Proteger "${docTitle}" con Contraseña`;
                documentAccessPassword.placeholder = 'Nueva Contraseña';
                documentAccessPassword.removeAttribute('readonly');
                documentAccessPassword.setAttribute('required', 'required');
                confirmPasswordInputGroup.style.display = 'block';
                documentConfirmPassword.setAttribute('required', 'required');
                modalSubmitButton.innerText = 'Establecer Contraseña';
                modalSubmitButton.classList.add('btn-save');
                modalSubmitButton.classList.remove('btn-verify');
                removePasswordButton.style.display = currentProtectionStatus ? 'block' : 'none'; // Mostrar si ya está protegida
                documentPasswordForm.action = '<?php echo htmlspecialchars($base_path); ?>/protect_document';

                // Validación de confirmación de contraseña
                documentConfirmPassword.onkeyup = function() {
                    if (documentAccessPassword.value !== documentConfirmPassword.value) {
                        documentConfirmPassword.setCustomValidity("Las contraseñas no coinciden.");
                    } else {
                        documentConfirmPassword.setCustomValidity("");
                    }
                };
                documentAccessPassword.onkeyup = function() { // También al cambiar la primera
                     if (documentAccessPassword.value !== documentConfirmPassword.value) {
                        documentConfirmPassword.setCustomValidity("Las contraseñas no coinciden.");
                    } else {
                        documentConfirmPassword.setCustomValidity("");
                    }
                };

            } else if (action === 'verify_password') {
                modalTitle.innerText = `Acceder a "${docTitle}" (Protegido)`;
                documentAccessPassword.placeholder = 'Ingresa la contraseña';
                documentAccessPassword.removeAttribute('readonly');
                documentAccessPassword.setAttribute('required', 'required');
                confirmPasswordInputGroup.style.display = 'none';
                documentConfirmPassword.removeAttribute('required');
                modalSubmitButton.innerText = 'Acceder';
                modalSubmitButton.classList.remove('btn-save');
                modalSubmitButton.classList.add('btn-verify'); // Puedes añadir un estilo diferente si quieres
                removePasswordButton.style.display = 'none';
                documentPasswordForm.action = '<?php echo htmlspecialchars($base_path); ?>/verify_document_access'; // Nueva ruta para verificación
            }
        }

        function closePasswordModal() {
            documentPasswordModal.style.display = 'none';
            documentConfirmPassword.setCustomValidity(""); // Limpiar validación al cerrar
        }

        // Para cerrar el modal haciendo clic fuera de él
        window.onclick = function(event) {
            if (event.target == documentPasswordModal) {
                closePasswordModal();
            }
        };

        function removeDocumentProtection() {
            if (confirm('¿Estás seguro de que quieres quitar la protección de contraseña de este documento?')) {
                modalActionType.value = 'remove';
                // Aquí enviamos el formulario, pero sin contraseña
                // Puedes redirigir o hacer un submit AJAX
                documentPasswordForm.action = '<?php echo htmlspecialchars($base_path); ?>/unprotect_document'; // Nueva ruta para desproteger
                documentAccessPassword.removeAttribute('required'); // No se necesita contraseña para quitar
                documentConfirmPassword.removeAttribute('required');
                documentPasswordForm.submit();
            }
        }

    </script>
</body>
</html>