<?php
// views/edit_excel_document.php
// Asegúrate de que $document, $excel_data y $base_path estén disponibles desde index.php

if (!isset($document) || !isset($excel_data) || !isset($base_path)) {
    // Si no hay datos, redirigir al dashboard con un mensaje de error
    $_SESSION['upload_message'] = "Error: Acceso no autorizado o datos incompletos para editar Excel.";
    header("Location: " . $base_path . "/dashboard");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Excel: <?php echo htmlspecialchars($document['title']); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Fuente más moderna */
            margin: 0;
            padding: 20px;
            background-color: #eef2f7; /* Fondo más suave */
            color: #333;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px; /* Bordes más redondeados */
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); /* Sombra más pronunciada */
            max-width: 1000px; /* Un poco más ancho */
            margin: 30px auto;
            overflow-x: auto; /* Para tablas anchas */
            border: 1px solid #e0e0e0;
        }
        h1 {
            color: #2c3e50; /* Color de título más oscuro */
            margin-bottom: 25px;
            text-align: center;
            font-size: 2.2em;
            font-weight: 600;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 30px;
            font-size: 0.9em;
        }
        th, td {
            border: 1px solid #dcdcdc; /* Bordes de tabla más suaves */
            padding: 10px 12px; /* Más padding */
            text-align: left;
            white-space: nowrap; /* Evita que el texto se rompa en varias líneas */
            vertical-align: top; /* Alinea el contenido arriba */
        }
        th {
            background-color: #f8f8f8; /* Fondo de encabezado más claro */
            font-weight: 700;
            color: #555;
            position: sticky; /* Encabezados pegajosos al hacer scroll */
            top: 0;
            z-index: 10;
        }
        /* Estilo para la primera columna con el número de fila */
        td:first-child, th:first-child {
            background-color: #f0f0f0; /* Fondo diferente para la columna de números */
            text-align: center;
            font-weight: bold;
            width: 40px; /* Ancho fijo para el número de fila */
        }
        /* QUITAMOS el width fijo de los inputs y los hacemos más flexibles */
        td input[type="text"] {
            width: 100%; /* El input ocupa todo el ancho de la celda */
            padding: 6px;
            border: 1px solid #c9d8e6; /* Borde más suave para los inputs */
            border-radius: 4px; /* Bordes ligeramente redondeados */
            box-sizing: border-box; /* Asegura que padding y borde estén dentro del ancho */
            font-size: 1em; /* Tamaño de fuente normal */
            transition: border-color 0.2s; /* Transición suave al enfocar */
        }
        td input[type="text"]:focus {
            border-color: #007bff; /* Borde azul al enfocar */
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25); /* Sombra suave al enfocar */
            outline: none; /* Eliminar el contorno por defecto del navegador */
        }
        tr:nth-child(even) { /* Filas impares con fondo ligeramente diferente */
            background-color: #fdfdfd;
        }
        tr:hover { /* Efecto hover en las filas */
            background-color: #f0f5ff;
        }
        .message {
            margin-bottom: 20px;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 500;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        }
        .success {
            background-color: #e6ffed;
            color: #1a6d32;
            border: 1px solid #a3e0b7;
        }
        .error {
            background-color: #ffe6e6;
            color: #d93030;
            border: 1px solid #f2a8a8;
        }
        button {
            background-color: #28a745; /* Botón de guardar verde */
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            margin-top: 30px;
            margin-right: 15px;
            transition: background-color 0.2s, transform 0.1s;
        }
        button:hover {
            background-color: #218838;
            transform: translateY(-1px);
        }
        button:active {
            transform: translateY(0);
        }
        a.cancel-link { /* Enlace de cancelar como un botón */
            background-color: #6c757d; /* Gris para cancelar */
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.1em;
            margin-top: 30px;
            display: inline-block;
            transition: background-color 0.2s, transform 0.1s;
        }
        a.cancel-link:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
        }
        .form-actions {
            text-align: right; /* Alinea los botones a la derecha */
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar Excel: <?php echo htmlspecialchars($document['title']); ?></h1>

        <?php
        if (isset($_SESSION['upload_message'])) {
            $message_class = (strpos($_SESSION['upload_message'], 'éxito') !== false) ? 'success' : 'error';
            echo "<p class='message {$message_class}'>" . htmlspecialchars($_SESSION['upload_message']) . "</p>";
            unset($_SESSION['upload_message']);
        }
        ?>

        <form action="<?php echo htmlspecialchars($base_path); ?>/update_excel_document" method="POST">
            <input type="hidden" name="document_id" value="<?php echo htmlspecialchars($document['id']); ?>">

            <table>
                <thead>
                    <tr>
                        <th></th> <?php
                        // Generar encabezados de columna (A, B, C, ...)
                        // Se asegura que haya al menos una fila en excel_data para determinar las columnas
                        if (!empty($excel_data)) {
                            $maxCols = 0;
                            foreach ($excel_data as $row) {
                                $maxCols = max($maxCols, count($row));
                            }
                            for ($col = 0; $col < $maxCols; $col++) {
                                echo '<th>' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '</th>';
                            }
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rowNum = 1;
                    // Solo si excel_data no está vacío, iterar sobre las filas
                    if (!empty($excel_data)) {
                        foreach ($excel_data as $rowIndex => $row):
                    ?>
                            <tr>
                                <td><?php echo $rowNum++; ?></td> <?php
                                // Asegúrate de que $maxCols esté definida para usarla aquí
                                $currentMaxCols = 0;
                                if (!empty($excel_data)) {
                                    foreach ($excel_data as $r) {
                                        $currentMaxCols = max($currentMaxCols, count($r));
                                    }
                                }

                                for ($colIndex = 0; $colIndex < $currentMaxCols; $colIndex++):
                                    $cellValue = $row[$colIndex] ?? ''; // Maneja si la celda no existe en la fila
                                ?>
                                    <td>
                                        <input type="text"
                                               name="excel_data[<?php echo $rowIndex; ?>][<?php echo $colIndex; ?>]"
                                               value="<?php echo htmlspecialchars($cellValue); ?>">
                                    </td>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach;
                    } else { ?>
                        <tr><td colspan="99">No hay datos en esta hoja de cálculo para mostrar.</td></tr>
                    <?php } ?>
                </tbody>
            </table>

            <div class="form-actions">
                <button type="submit">Guardar Cambios</button>
                <a href="<?php echo htmlspecialchars($base_path); ?>/dashboard" class="button cancel-link">Cancelar y Volver al Dashboard</a>
            </div>
        </form>
    </div>
</body>
</html>