<?php
// public/index.php

session_start();

// 1. Incluir archivos de configuración y modelos
require_once __DIR__ . '/../app/Config/db.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Document.php'; // Incluimos el modelo Document

// 2. Definir la ruta base de tu aplicación
$request_uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$public_pos = strpos($request_uri_path, '/public');

if ($public_pos !== false) {
    $base_path = substr($request_uri_path, 0, $public_pos + strlen('/public'));
} else {
    $base_path = '/don/manager-documents/public'; // Fallback manual, ¡ajusta si es necesario!
}

// 3. Obtener la URL solicitada limpia
$request_uri = str_replace($base_path, '', $request_uri_path);


// --- Lógica de Enrutamiento ---

if ($request_uri === '/') {
    echo "<h1>Bienvenido a tu Gestor de Documentos</h1>";
    if (isset($_SESSION['user_id'])) {
        echo "<p>Hola, " . htmlspecialchars($_SESSION['username']) . "! <a href='" . $base_path . "/dashboard'>Ir a mi Dashboard</a> | <a href='" . $base_path . "/logout'>Cerrar Sesión</a></p>";
    } else {
        echo "<p><a href='" . $base_path . "/login'>Iniciar Sesión</a> | <a href='" . $base_path . "/register'>Registrarse</a></p>";
    }
}
elseif ($request_uri === '/register') {
    require_once '../views/register.php';
}
elseif ($request_uri === '/login') {
    require_once '../views/login.php';
}
elseif ($request_uri === '/dashboard') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . $base_path . "/login");
        exit();
    }

    $db_connection = getDbConnection();
    $document_model = new Document($db_connection);
    $documents = $document_model->findByUserId($_SESSION['user_id']);

    require_once '../views/dashboard.php';
}
elseif ($request_uri === '/uploads') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . $base_path . "/login");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file'])) {
        $upload_dir = __DIR__ . '/../uploads/';
        $user_id = $_SESSION['user_id'];
        $document_title = $_POST['document_title'] ?? 'Sin título';

        $file = $_FILES['document_file'];

        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $max_file_size = 5 * 1024 * 1024; // 5 MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['upload_message'] = "Error al subir el archivo: " . $file['error'];
        } elseif (!in_array($file['type'], $allowed_types)) {
            $_SESSION['upload_message'] = "Tipo de archivo no permitido: " . htmlspecialchars($file['type']);
        } elseif ($file['size'] > $max_file_size) {
            $_SESSION['upload_message'] = "El archivo es demasiado grande (máx. 5MB).";
        } elseif (!is_uploaded_file($file['tmp_name'])) {
            $_SESSION['upload_message'] = "Error de seguridad con el archivo subido.";
        } else {
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $unique_file_name = uniqid($user_id . '_', true) . '.' . $file_extension;
            $destination_path = $upload_dir . $unique_file_name;

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (move_uploaded_file($file['tmp_name'], $destination_path)) {
                $db_connection = getDbConnection();
                $document_model = new Document($db_connection);

                if ($document_model->create($user_id, $document_title, $unique_file_name, $file['type'], $file['size'])) {
                    $_SESSION['upload_message'] = "Documento subido y registrado con éxito.";
                } else {
                    unlink($destination_path); // Limpiar si falla la DB
                    $_SESSION['upload_message'] = "Error al registrar el documento en la base de datos.";
                }
            } else {
                $_SESSION['upload_message'] = "Error al mover el archivo subido al servidor.";
            }
        }
    } else {
        $_SESSION['upload_message'] = "No se ha seleccionado ningún archivo o la solicitud no es válida.";
    }

    header("Location: " . $base_path . "/dashboard");
    exit();
}
elseif ($request_uri === '/logout') {
    session_unset();
    session_destroy();
    header("Location: " . $base_path . "/login");
    exit();
}
// --- NUEVAS RUTAS PARA VER, DESCARGAR Y ELIMINAR DOCUMENTOS ---

// Ruta para VER un documento
// Ejemplo de URL: /view_document/123
elseif (preg_match('/^\/view_document\/(\d+)$/', $request_uri, $matches)) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . $base_path . "/login");
        exit();
    }

    $document_id = (int) $matches[1]; // Captura el ID del documento de la URL
    $user_id = $_SESSION['user_id'];
    $upload_dir = __DIR__ . '/../uploads/';

    $db_connection = getDbConnection();
    $document_model = new Document($db_connection);

    $document = $document_model->findByIdAndUserId($document_id, $user_id);

    if ($document) {
        $file_path = $upload_dir . $document['file_name'];

        if (file_exists($file_path)) {
            // Establecer cabeceras para mostrar el archivo en el navegador
            header('Content-Type: ' . $document['file_type']);
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path); // Leer y enviar el contenido del archivo
            exit();
        } else {
            http_response_code(404);
            echo "Error: Archivo físico no encontrado en el servidor.";
        }
    } else {
        http_response_code(404);
        echo "Error: Documento no encontrado o no tienes permiso para verlo.";
    }
}
// Ruta para DESCARGAR un documento
// Ejemplo de URL: /download_document/123
elseif (preg_match('/^\/download_document\/(\d+)$/', $request_uri, $matches)) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . $base_path . "/login");
        exit();
    }

    $document_id = (int) $matches[1];
    $user_id = $_SESSION['user_id'];
    $upload_dir = __DIR__ . '/../uploads/';

    $db_connection = getDbConnection();
    $document_model = new Document($db_connection);

    $document = $document_model->findByIdAndUserId($document_id, $user_id);

    if ($document) {
        $file_path = $upload_dir . $document['file_name'];

        if (file_exists($file_path)) {
            // Establecer cabeceras para forzar la descarga
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $document['file_type']);
            header('Content-Disposition: attachment; filename="' . basename($document['title'] . '.' . pathinfo($document['file_name'], PATHINFO_EXTENSION)) . '"'); // Nombre de descarga amigable
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit();
        } else {
            http_response_code(404);
            echo "Error: Archivo físico no encontrado para descargar.";
        }
    } else {
        http_response_code(404);
        echo "Error: Documento no encontrado o no tienes permiso para descargarlo.";
    }
}
// Ruta para ELIMINAR un documento
// Ejemplo de URL: /delete_document/123
elseif (preg_match('/^\/delete_document\/(\d+)$/', $request_uri, $matches)) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . $base_path . "/login");
        exit();
    }

    $document_id = (int) $matches[1];
    $user_id = $_SESSION['user_id'];
    $upload_dir = __DIR__ . '/../uploads/';

    $db_connection = getDbConnection();
    $document_model = new Document($db_connection);

    // 1. Obtener información del documento para saber el nombre del archivo físico
    $document = $document_model->findByIdAndUserId($document_id, $user_id);

    if ($document) {
        $file_path = $upload_dir . $document['file_name'];

        // 2. Eliminar el registro de la base de datos
        if ($document_model->delete($document_id, $user_id)) {
            // 3. Si se eliminó de la DB, intentar eliminar el archivo físico
            if (file_exists($file_path)) {
                unlink($file_path); // Eliminar el archivo físico
                $_SESSION['upload_message'] = "Documento y archivo eliminados con éxito.";
            } else {
                $_SESSION['upload_message'] = "Documento eliminado de la DB, pero el archivo físico no se encontró.";
            }
        } else {
            $_SESSION['upload_message'] = "Error al eliminar el documento de la base de datos.";
        }
    } else {
        $_SESSION['upload_message'] = "Documento no encontrado o no tienes permiso para eliminarlo.";
    }

    header("Location: " . $base_path . "/dashboard"); // Redirigir de vuelta al dashboard
    exit();
}
// Manejo de Rutas No Encontradas (si ninguna de las anteriores coincide)
else {
    http_response_code(404);
    echo "404 - Página no encontrada.";
}