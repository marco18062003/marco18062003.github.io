<?php
// views/register.php

// Iniciar sesión al principio, siempre es una buena práctica.
//session_start();

// Incluir el archivo de configuración de la base de datos para obtener la conexión.
// La ruta es relativa a register.php (está en views/, necesita subir 2 niveles para llegar a app/Config/)
require_once __DIR__ . '/../app/Config/db.php';

// Incluir el modelo de usuario.
// La ruta es relativa a register.php (está en views/, necesita subir 2 niveles para llegar a app/Models/)
require_once __DIR__ . '/../app/Models/User.php';

// Definir la ruta base de tu aplicación de forma dinámica
// Esto es CRUCIAL para que las redirecciones y enlaces funcionen correctamente en cualquier entorno.
// Asumiendo que tu carpeta 'public' está en '/don/manager-documents/public' en la URL.
// La misma lógica de $base_path que tienes en public/index.php
$request_uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Encuentra la posición de "/public" en la URI
$public_pos = strpos($request_uri_path, '/public');
if ($public_pos !== false) {
    // Extrae la parte base hasta e incluyendo "/public"
    $base_path = substr($request_uri_path, 0, $public_pos + strlen('/public'));
} else {
    // Fallback si por alguna razón '/public' no está en la URI (menos común)
    $base_path = ''; // Podría ser solo '/' si la app está en la raíz del dominio
}


$error_message = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener la conexión a la base de datos
    $db_connection = getDbConnection();
    // Instanciar el objeto User, INYECTANDO la conexión
    $user_model = new User($db_connection);

    // Obtener datos del formulario
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // --- Validación de Entrada ---
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Formato de email inválido.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 6) { // Ejemplo de validación de longitud de contraseña
        $error_message = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        // Verificar si el email ya existe usando el nuevo método
        if ($user_model->emailExists($email)) {
            $error_message = "El email ya está registrado. Intenta iniciar sesión.";
        } else {
            // Hashear la contraseña ANTES de pasarla al modelo
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Usa PASSWORD_DEFAULT para el algoritmo recomendado

            // Intentar crear el usuario
            // Pasar los datos directamente al método create()
            if ($user_model->create($username, $email, $hashed_password)) {
                // Registro exitoso, redirigir a login con un mensaje de éxito
                header("Location: " . $base_path . "/login?registered=true");
                exit();
            } else {
                // Si create() falla, significa un problema con la DB (ej. error en la consulta)
                $error_message = "Error al registrar usuario. Por favor, inténtalo de nuevo.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Registro de Usuario</h2>
        <?php if ($error_message): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form action="<?php echo $base_path; ?>/register" method="POST">
            <label for="username">Usuario:</label><br>
            <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($username); ?>"><br><br>

            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>"><br><br>

            <label for="password">Contraseña:</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <label for="confirm_password">Confirmar Contraseña:</label><br>
            <input type="password" id="confirm_password" name="confirm_password" required><br><br>

            <button type="submit">Registrarse</button>
        </form>
        <p>¿Ya tienes una cuenta? <a href="<?php echo $base_path; ?>/login">Inicia Sesión aquí</a></p>
    </div>
</body>
</html>