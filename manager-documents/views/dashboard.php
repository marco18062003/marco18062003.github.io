<?php
// views/dashboard.php

// Nota: Asumimos que public/index.php ya ha iniciado la sesión
// y ha definido la variable $base_path antes de incluir este archivo.
// También asumimos que public/index.php pasa la variable $documents a esta vista.

// Si el usuario no está logueado, redirigir al login.
// Esta es una doble verificación de seguridad, ya que index.php también debería hacerlo.
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_path . "/login");
    exit();
}

// Obtener la información del usuario logueado de la sesión
$username = htmlspecialchars($_SESSION['username'] ?? 'Usuario');

// La variable $documents ahora es pasada por public/index.php
// Si por alguna razón no se pasara, se inicializaría aquí como un array vacío para evitar errores.
if (!isset($documents)) {
    $documents = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestor de Documentos</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>/css/style.css">
</head>
<body>
    <div class="container">
        <?php
        // Mostrar el mensaje de subida (éxito o error) si existe en la sesión
        if (isset($_SESSION['upload_message'])) {
            $message_style = (strpos($_SESSION['upload_message'], 'Error') !== false || strpos($_SESSION['upload_message'], 'No se') !== false) ? 'color: red;' : 'color: green;';
            echo "<p style='{$message_style} font-weight: bold;'>". htmlspecialchars($_SESSION['upload_message']) ."</p>";
            unset($_SESSION['upload_message']); // Limpiar el mensaje después de mostrarlo una vez
        }
        ?>

        <h2>Bienvenido a tu Dashboard, <?php echo $username; ?>!</h2>
        <p>Aquí puedes ver, subir y gestionar tus documentos.</p>

        ---

        <h3>Tus Documentos</h3>
        <?php if (empty($documents)): // Si no hay documentos para mostrar ?>
            <p>Aún no tienes documentos subidos. ¡Es hora de subir el primero!</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Tamaño</th>
                        <th>Fecha de Subida</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($doc['title']); ?></td>
                            <td><?php echo htmlspecialchars($doc['file_type']); ?></td>
                            <td><?php echo round($doc['file_size'] / 1024, 2); ?> KB</td>
                            <td><?php echo htmlspecialchars($doc['upload_date']); ?></td>
                            <td>
                                <a href="<?php echo $base_path; ?>/view_document/<?php echo $doc['id']; ?>">Ver</a> |
                                <a href="<?php echo $base_path; ?>/download_document/<?php echo $doc['id']; ?>">Descargar</a> |
                                <a href="<?php echo $base_path; ?>/delete_document/<?php echo $doc['id']; ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar este documento?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        ---

        <h3>Subir Nuevo Documento</h3>
        <form action="<?php echo $base_path; ?>/uploads" method="POST" enctype="multipart/form-data">
            <label for="document_title">Título del Documento:</label><br>
            <input type="text" id="document_title" name="document_title" required><br><br>

            <label for="document_file">Seleccionar Archivo:</label><br>
            <input type="file" id="document_file" name="document_file" required><br><br>

            <button type="submit">Subir Documento</button>
        </form>

        ---

        <p><a href="<?php echo $base_path; ?>/logout">Cerrar Sesión</a></p>
    </div>
</body>
</html>