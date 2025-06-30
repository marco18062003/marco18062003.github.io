<?php
// views/login.php

// Iniciar sesión al principio
//session_start();

// Incluir el archivo de configuración de la base de datos.
require_once __DIR__ . '/../app/Config/db.php';

// Incluir el modelo de usuario.
require_once __DIR__ . '/../app/Models/User.php';

// Definir la ruta base de tu aplicación de forma dinámica (igual que en register.php)
$request_uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$public_pos = strpos($request_uri_path, '/public');
if ($public_pos !== false) {
    $base_path = substr($request_uri_path, 0, $public_pos + strlen('/public'));
} else {
    $base_path = '';
}


$error_message = '';
$success_message = '';
$email = ''; // Para mantener el email en el campo si hay error

if (isset($_GET['registered']) && $_GET['registered'] == 'true') {
    $success_message = "¡Registro exitoso! Por favor, inicia sesión.";
}

// Redirigir si el usuario ya está logueado
if (isset($_SESSION['user_id'])) {
    header("Location: " . $base_path . "/dashboard");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener la conexión a la base de datos
    $db_connection = getDbConnection();
    // Instanciar el objeto User, INYECTANDO la conexión
    $user_model = new User($db_connection);

    // Obtener datos del formulario
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // --- Validación de Entrada ---
    if (empty($email) || empty($password)) {
        $error_message = "Todos los campos son obligatorios.";
    } else {
        // Buscar el usuario por email
        // Pasar el email directamente al método findByEmail()
        $foundUser = $user_model->findByEmail($email);

        // Verificar si el usuario existe y si la contraseña es correcta
        // password_verify() es CRÍTICO para comprobar contraseñas hasheadas
        if ($foundUser && password_verify($password, $foundUser['password'])) {
            // Contraseña correcta, iniciar sesión
            $_SESSION['user_id'] = $foundUser['id'];
            $_SESSION['username'] = $foundUser['username']; // Guardar el nombre de usuario
            // No es buena práctica guardar el email directamente en $_SESSION['email'] si ya tienes username y id.
            // $_SESSION['email'] = $foundUser['email']; // Opcional, pero username y id suelen ser suficientes.

            // Redirigir al dashboard
            header("Location: " . $base_path . "/dashboard");
            exit();
        } else {
            // Credenciales incorrectas
            $error_message = "Email o contraseña incorrectos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Iniciar Sesión</h2>
        <?php if ($error_message): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <p style="color: green;"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>

        <form action="<?php echo $base_path; ?>/login" method="POST">
            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>"><br><br>

            <label for="password">Contraseña:</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <button type="submit">Iniciar Sesión</button>
        </form>
        <p>¿No tienes una cuenta? <a href="<?php echo $base_path; ?>/register">Regístrate aquí</a></p>
    </div>
</body>
</html>