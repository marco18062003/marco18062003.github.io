<?php
// public/index.php (Actualizado para manejar logout directamente)
session_start(); // SIEMPRE al principio

// Incluir archivos de configuración y modelos
require_once '../app/Config/db.php'; // Cambiado a db.php
require_once '../app/Models/User.php';

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Ajusta esta línea si tu proyecto no está en la raíz del dominio
// Ejemplo: si accedes como http://localhost/document-manager-native/public/
$base_path = '/document-manager-native/public';
$request_uri = str_replace($base_path, '', $request_uri);


// --- Lógica de Enrutamiento ---
if ($request_uri == '/') {
    echo "<h1>Bienvenido a tu Gestor de Documentos</h1>";
    if (isset($_SESSION['user_id'])) {
        echo "<p>Hola, " . htmlspecialchars($_SESSION['username']) . "! <a href='" . $base_path . "/dashboard'>Ir a mi Dashboard</a> | <a href='" . $base_path . "/logout'>Cerrar Sesión</a></p>";
    } else {
        echo "<p><a href='" . $base_path . "/login'>Iniciar Sesión</a> | <a href='" . $base_path . "/register'>Registrarse</a></p>";
    }
} elseif ($request_uri == '/register') {
    require_once '../views/register.php';
} elseif ($request_uri == '/login') {
    require_once '../views/login.php';
} elseif ($request_uri == '/dashboard') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . $base_path . "/login");
        exit();
    }
    // Contenido del dashboard (idealmente en views/dashboard.php)
    echo "<h2>Dashboard de " . htmlspecialchars($_SESSION['username']) . "</h2>";
    echo "<p>Aquí verás tus documentos.</p>";
    echo "<p><a href='" . $base_path . "/logout'>Cerrar Sesión</a></p>";
} elseif ($request_uri == '/logout') { // AHORA MANEJAMOS LOGOUT AQUI
    session_unset();    // Elimina todas las variables de sesión
    session_destroy();  // Destruye la sesión
    header("Location: " . $base_path . "/login"); // Redirige a la página de login
    exit();
} else {
    http_response_code(404);
    echo "404 - Página no encontrada.";
}
?>