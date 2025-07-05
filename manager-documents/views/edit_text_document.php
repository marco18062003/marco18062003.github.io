<?php
// views/edit_text_document.php
// Asegúrate de que $document y $document_content estén disponibles desde index.php
// y que $base_path también esté disponible para los enlaces.

if (!isset($document) || !isset($document_content) || !isset($base_path)) {
    // Redirigir o mostrar un error si las variables no están definidas
    header("Location: " . $base_path . "/dashboard"); // Intentar redirigir, aunque base_path podría no estar definido
    exit("Acceso no autorizado o datos incompletos.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Documento: <?php echo htmlspecialchars($document['title']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        .container { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 800px; margin: auto; }
        textarea { width: 100%; height: 400px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-family: 'Courier New', Courier, monospace; font-size: 0.9em; resize: vertical; /* Permite redimensionar verticalmente */ }
        button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-size: 1em; margin-right: 10px;}
        button:hover { background-color: #0056b3; }
        a { text-decoration: none; color: #007bff; margin-top: 10px; display: inline-block; }
        a:hover { text-decoration: underline; }
        h1 { color: #333; }
        .message { margin-bottom: 15px; padding: 10px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar Documento: <?php echo htmlspecialchars($document['title']); ?></h1>

        <?php
        // Mostrar mensajes de sesión si existen
        if (isset($_SESSION['upload_message'])) { // Usamos upload_message para consistencia
            // Determinar si es un mensaje de éxito o error para el estilo
            $message_class = (strpos($_SESSION['upload_message'], 'éxito') !== false) ? 'success' : 'error';
            echo "<p class='message {$message_class}'>" . htmlspecialchars($_SESSION['upload_message']) . "</p>";
            unset($_SESSION['upload_message']); // Limpiar el mensaje después de mostrarlo
        }
        ?>

        <form action="<?php echo htmlspecialchars($base_path); ?>/update_text_document" method="POST">
            <input type="hidden" name="document_id" value="<?php echo htmlspecialchars($document['id']); ?>">
            <textarea name="document_content"><?php echo htmlspecialchars($document_content); ?></textarea><br><br>
            <button type="submit">Guardar Cambios</button>
            <a href="<?php echo htmlspecialchars($base_path); ?>/dashboard">Cancelar y Volver al Dashboard</a>
        </form>
    </div>
</body>
</html>