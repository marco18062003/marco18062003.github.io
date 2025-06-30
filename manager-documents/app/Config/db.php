<?php
// app/Config/db.php

// 1. DB_HOST: La dirección del servidor de tu base de datos.
//    Para XAMPP en tu propia computadora, 'localhost' es casi siempre lo correcto.
define('DB_HOST', 'localhost');

// 2. DB_NAME: El nombre de la base de datos que creaste.
//    Recuerda que en el Paso 3.1 de la Fase 1, te pedí crear una base de datos en phpMyAdmin.
//    Deberías haberle dado un nombre, por ejemplo, 'document_manager_native_db'.
//    Si le diste un nombre diferente (por ejemplo, 'mis_documentos_db'), DEBES poner ESE nombre aquí.
define('DB_NAME', 'dios2'); // <-- ¡AJUSTA ESTO CON EL NOMBRE EXACTO DE TU DB!

// 3. DB_USER: El nombre de usuario para acceder a tu base de datos.
//    Para la configuración por defecto de MySQL en XAMPP, el usuario es 'root'.
//    Es raro que lo hayas cambiado, así que 'root' suele ser correcto.
define('DB_USER', 'root');

// 4. DB_PASS: La contraseña para el usuario de la base de datos.
//    Para la configuración por defecto de MySQL en XAMPP, la contraseña está VACÍA.
//    Por eso, las comillas simples están vacías: ''. NO pongas un espacio dentro.
//    Si por alguna razón le pusiste una contraseña a tu usuario 'root' de MySQL,
//    DEBES poner esa contraseña aquí entre las comillas.
define('DB_PASS', ''); // <-- ¡AJUSTA ESTO SI LE PUSISTE CONTRASEÑA A 'root'!

// --- Función para Obtener la Conexión a la Base de Datos ---

// La función 'getDbConnection()' es la que realmente establece la conexión a MySQL
// usando la Extensión de Objetos de Datos de PHP (PDO).
function getDbConnection() {
    try {
        // Intenta crear una nueva conexión PDO con las constantes definidas arriba.
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);

        // Establece un atributo de PDO para que, si ocurre un error en la base de datos,
        // PDO lance una excepción (un error de PHP), lo que facilita depurar.
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Si la conexión es exitosa, devuelve el objeto de conexión.
        return $conn;
    } catch (PDOException $e) {
        // Si hay un problema al conectar (por ejemplo, credenciales incorrectas, DB no existe),
        // detiene el script y muestra un mensaje de error.
        die("Error de conexión a la base de datos: " . $e->getMessage());
    }
}
?>