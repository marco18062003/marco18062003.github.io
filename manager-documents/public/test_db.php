<?php
// public/test_db_connection.php

require_once __DIR__ . '/../app/Config/db.php'; // Ruta correcta desde public/

try {
    $conn = getDbConnection();
    echo "¡Conexión a la base de datos 'dios2' exitosa!";
    // Opcional: Ejecutar una consulta simple para verificar tablas
    // $stmt = $conn->query("SHOW TABLES");
    // $tables = $stmt->fetchAll();
    // echo "<pre>";
    // print_r($tables);
    // echo "</pre>";
} catch (PDOException $e) {
    echo "Error al conectar o consultar la base de datos: " . $e->getMessage();
}
?>