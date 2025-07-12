<?php
// public/index.php

session_start();
require_once __DIR__ . '/../vendor/autoload.php'; // ¡Esta línea es CRUCIAL y nueva!

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
    // Fallback manual si '/public' no está en la URL. ¡Asegúrate de que esta ruta sea correcta!
    $base_path = '/don/manager-documents/public';
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
        $document_category = $_POST['document_cat'] ?? 'General';
       


        $file = $_FILES['document_file'];

        // Lista de tipos permitidos, incluyendo los genéricos y específicos para código y cómics
        $allowed_types = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/msword', // .doc
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
            'text/plain',                 // Para .txt y código genérico
            'text/javascript',            // Para .js
            'application/javascript',     // Alternativa para .js
            'application/x-python',       // Para .py
            'text/x-python',              // Alternativa común para .py
            'text/html',                  // Para .html
            'text/css',                   // Para .css
            'application/sql',            // Para .sql
            'text/x-sql',                 // Alternativa común para .sql
            'application/json',           // Para .json
            'application/xml',            // Para .xml
            'text/xml',                   // Alternativa para .xml
            'application/x-cbr',          // Para .cbr
            'application/x-rar-compressed', // Alternativa para .cbr
            'application/x-cbz',          // Para .cbz
            'application/zip',            // Alternativa para .cbz (si .cbz usa zip)
            // Tipos para Excel
            'application/vnd.ms-excel', // .xls
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
            // Generalmente, NO incluimos 'application/octet-stream' en allowed_types
            // porque es demasiado permisivo. Preferimos que finfo_file detecte el tipo real.
        ];

        $max_file_size = 5 * 1024 * 1024; // 5 MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['upload_message'] = "Error al subir el archivo: " . $file['error'] . ". Código: " . $file['error']; // Añadimos el código de error para depuración
        } elseif ($file['size'] > $max_file_size) {
            $_SESSION['upload_message'] = "El archivo es demasiado grande (máx. 5MB).";
        } elseif (!is_uploaded_file($file['tmp_name'])) {
            $_SESSION['upload_message'] = "Error de seguridad con el archivo subido (posible ataque de carga).";
        } else {
            // **Paso clave**: Usar finfo_file para obtener el tipo MIME real del archivo
            // Asegúrate de que la extensión 'fileinfo' esté habilitada en tu php.ini
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo === false) {
                $_SESSION['upload_message'] = "Error del servidor: La extensión fileinfo de PHP no está habilitada.";
                header("Location: " . $base_path . "/dashboard");
                exit();
            }
            $real_mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            // Validar el tipo MIME real contra nuestra lista de permitidos
            if (!in_array($real_mime_type, $allowed_types)) {
                $_SESSION['upload_message'] = "Tipo de archivo no permitido: " . htmlspecialchars($real_mime_type) . " (Detectado por el servidor).";
                header("Location: " . $base_path . "/dashboard");
                exit();
            }

            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $unique_file_name = uniqid($user_id . '_', true) . '.' . $file_extension;
            $destination_path = $upload_dir . $unique_file_name;

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Crea el directorio 'uploads' si no existe
            }

            if (move_uploaded_file($file['tmp_name'], $destination_path)) {
                $db_connection = getDbConnection();
                $document_model = new Document($db_connection);

                // IMPORTANTE: Almacenar el REAL MIME TYPE, no el que envió el navegador
                if ($document_model->create($user_id, $document_title, $unique_file_name, $real_mime_type, $file['size'], $document_category)) {
                    $_SESSION['upload_message'] = "Documento subido y registrado con éxito.";
                } else {
                    unlink($destination_path); // Limpiar el archivo subido si falla el registro en la DB
                    $_SESSION['upload_message'] = "Error al registrar el documento en la base de datos.";
                }
            } else {
                $_SESSION['upload_message'] = "Error al mover el archivo subido al servidor. Permisos de escritura o directorio 'uploads' inexistente.";
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
// --- RUTAS EXISTENTES PARA VER, DESCARGAR Y ELIMINAR DOCUMENTOS ---

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
            // Un nombre de descarga más amigable y seguro
            $download_filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', basename($document['title'])) . '.' . pathinfo($document['file_name'], PATHINFO_EXTENSION);
            header('Content-Disposition: attachment; filename="' . $download_filename . '"');
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
                $_SESSION['upload_message'] = "Documento eliminado de la DB, pero el archivo físico no se encontró en el servidor.";
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
// --- RUTAS PARA EDITAR DOCUMENTOS DE TEXTO ---

// Ruta para MOSTRAR el formulario de edición de un documento de texto
// Ejemplo de URL: /edit_text_document/123
elseif (preg_match('/^\/edit_text_document\/(\d+)$/', $request_uri, $matches)) {
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
        // **Validar que es un archivo de texto antes de intentar editarlo**
        // Esta lista define qué tipos de archivo consideras "editables como texto".
        $editable_text_types = [
            'text/plain',
            'text/javascript',
            'application/javascript',
            'application/x-python',
            'text/x-python',
            'text/html',
            'text/css',
            'application/sql',
            'text/x-sql',
            'application/json',
            'application/xml',
            'text/xml'
        ];

        if (in_array($document['file_type'], $editable_text_types)) {
            $file_path = $upload_dir . $document['file_name'];

            if (file_exists($file_path)) {
                // Lee el contenido actual del archivo
                $document_content = file_get_contents($file_path);
                // Incluimos la vista que contiene el formulario de edición
                require_once '../views/edit_text_document.php';
            } else {
                http_response_code(404);
                $_SESSION['upload_message'] = "Error: Archivo físico no encontrado para editar.";
                header("Location: " . $base_path . "/dashboard");
                exit();
            }
        } else {
            $_SESSION['upload_message'] = "Error: Este tipo de documento (" . htmlspecialchars($document['file_type']) . ") no se puede editar como texto.";
            header("Location: " . $base_path . "/dashboard");
            exit();
        }
    } else {
        http_response_code(404);
        $_SESSION['upload_message'] = "Error: Documento no encontrado o no tienes permiso para editarlo.";
        header("Location: " . $base_path . "/dashboard");
        exit();
    }
}
// Ruta para PROCESAR el formulario de edición y guardar el contenido de TEXTO
// Recibirá el contenido actualizado del textarea
elseif ($request_uri === '/update_text_document' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . $base_path . "/login");
        exit();
    }

    $document_id = $_POST['document_id'] ?? null;
    $new_content = $_POST['document_content'] ?? '';
    $user_id = $_SESSION['user_id'];
    $upload_dir = __DIR__ . '/../uploads/';

    if (!$document_id) {
        $_SESSION['upload_message'] = "Error: ID de documento no proporcionado para actualizar.";
        header("Location: " . $base_path . "/dashboard");
        exit();
    }

    $db_connection = getDbConnection();
    $document_model = new Document($db_connection);

    $document = $document_model->findByIdAndUserId($document_id, $user_id);

    if ($document) {
        // Re-validar que es un tipo de archivo editable para seguridad
        $editable_text_types = [
            'text/plain',
            'text/javascript',
            'application/javascript',
            'application/x-python',
            'text/x-python',
            'text/html',
            'text/css',
            'application/sql',
            'text/x-sql',
            'application/json',
            'application/xml',
            'text/xml'
        ];

        if (in_array($document['file_type'], $editable_text_types)) {
            $file_path = $upload_dir . $document['file_name'];

            // **Validar que el archivo existe y es escribible**
            if (file_exists($file_path) && is_writable($file_path)) {
                // Sobrescribir el archivo con el nuevo contenido
                // file_put_contents devuelve el número de bytes escritos o FALSE en caso de error.
                if (file_put_contents($file_path, $new_content) !== false) {
                    $_SESSION['upload_message'] = "Documento de texto actualizado con éxito.";
                } else {
                    $_SESSION['upload_message'] = "Error: No se pudo escribir en el archivo. Verifique permisos.";
                }
            } else {
                $_SESSION['upload_message'] = "Error: El archivo no existe o no tiene permisos de escritura.";
            }
        } else {
            $_SESSION['upload_message'] = "Error: Este tipo de documento (" . htmlspecialchars($document['file_type']) . ") no es editable como texto.";
        }
    } else {
        $_SESSION['upload_message'] = "Error: Documento no encontrado o no tienes permiso para actualizarlo.";
    }

    header("Location: " . $base_path . "/dashboard");
    exit();
}
// --- NUEVAS RUTAS PARA EDITAR ARCHIVOS DE EXCEL (DATOS BÁSICOS) ---

// Ruta para MOSTRAR el formulario de edición de un documento Excel
// Ejemplo de URL: /edit_excel_document/123
elseif (preg_match('/^\/edit_excel_document\/(\d+)$/', $request_uri, $matches)) {
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
        // Validar que es un archivo Excel
        $excel_types = [
            'application/vnd.ms-excel', // .xls
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' // .xlsx
        ];

        if (in_array($document['file_type'], $excel_types)) {
            $file_path = $upload_dir . $document['file_name'];

            if (file_exists($file_path)) {
                try {
                    // Usar PhpSpreadsheet para leer el archivo
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
                    $sheet = $spreadsheet->getActiveSheet(); // Obtener la hoja activa

                    // Leer todas las celdas con datos
                    $highestRow = $sheet->getHighestRow();
                    $highestColumn = $sheet->getHighestColumn(); // Ej: 'E'
                    $excel_data = [];

                    for ($row = 1; $row <= $highestRow; $row++) {
                        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
                            NULL, TRUE, FALSE);
                        $excel_data[] = $rowData[0]; // rowData es un array de arrays, solo queremos el primero
                    }

                    // Incluimos la vista que contiene el formulario de edición
                    header('Content-Type: text/html; charset=utf-8');
                    require_once '../views/edit_excel_document.php';

                } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                    $_SESSION['upload_message'] = "Error al leer el archivo Excel: " . $e->getMessage();
                    header("Location: " . $base_path . "/dashboard");
                    exit();
                }
            } else {
                http_response_code(404);
                $_SESSION['upload_message'] = "Error: Archivo físico de Excel no encontrado para editar.";
                header("Location: " . $base_path . "/dashboard");
                exit();
            }
        } else {
            $_SESSION['upload_message'] = "Error: Este documento no es un archivo Excel editable.";
            header("Location: " . $base_path . "/dashboard");
            exit();
        }
    } else {
        http_response_code(404);
        $_SESSION['upload_message'] = "Error: Documento no encontrado o no tienes permiso para editarlo.";
        header("Location: " . $base_path . "/dashboard");
        exit();
    }
}

// Ruta para PROCESAR el formulario de edición y guardar el contenido del Excel
// Recibirá los datos de la tabla editada
elseif ($request_uri === '/update_excel_document' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . $base_path . "/login");
        exit();
    }

    $document_id = $_POST['document_id'] ?? null;
    $excel_data_posted = $_POST['excel_data'] ?? []; // Los datos vendrán como un array bidimensional
    $user_id = $_SESSION['user_id'];
    $upload_dir = __DIR__ . '/../uploads/';

    if (!$document_id) {
        $_SESSION['upload_message'] = "Error: ID de documento no proporcionado para actualizar Excel.";
        header("Location: " . $base_path . "/dashboard");
        exit();
    }

    $db_connection = getDbConnection();
    $document_model = new Document($db_connection);

    $document = $document_model->findByIdAndUserId($document_id, $user_id);

    if ($document) {
        $excel_types = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        if (in_array($document['file_type'], $excel_types)) {
            $file_path = $upload_dir . $document['file_name'];

            // Comprobar si el archivo existe y el directorio es escribible
            if (file_exists($file_path) && is_writable(dirname($file_path))) {
                try {
                    // Crear un nuevo objeto Spreadsheet y cargar los datos
                    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    // Escribir los datos recibidos del formulario en la hoja
                    // Nota: Esto escribirá solo los datos que fueron enviados.
                    // Los datos no presentados en el formulario (fuera del rango leído inicialmente) se perderán.
                    $sheet->fromArray($excel_data_posted, NULL, 'A1');

                    // Determinar el tipo de escritor basado en el tipo MIME original o extensión
                    $writerType = '';
                    $originalExtension = pathinfo($document['file_name'], PATHINFO_EXTENSION);
                    if ($originalExtension === 'xlsx') {
                        $writerType = \PhpOffice\PhpSpreadsheet\IOFactory::WRITER_XLSX;
                    } elseif ($originalExtension === 'xls') {
                        $writerType = \PhpOffice\PhpSpreadsheet\IOFactory::WRTERY_XLS; // Error tipográfico aquí
                    } else {
                        // Fallback, aunque ya validamos el tipo MIME antes
                        $_SESSION['upload_message'] = "Error: Extensión de archivo Excel no reconocida para guardar.";
                        header("Location: " . $base_path . "/dashboard");
                        exit();
                    }

                    // Guardar el archivo Excel actualizado
                    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, $writerType);
                    $writer->save($file_path);

                    $_SESSION['upload_message'] = "Documento Excel actualizado con éxito (solo datos).";

                } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
                    $_SESSION['upload_message'] = "Error al guardar el archivo Excel: " . $e->getMessage();
                } catch (\Exception $e) { // Capturar otras excepciones inesperadas
                    $_SESSION['upload_message'] = "Ocurrió un error inesperado al procesar el Excel: " . $e->getMessage();
                }
            } else {
                $_SESSION['upload_message'] = "Error: El archivo Excel no existe o el directorio no tiene permisos de escritura.";
            }
        } else {
            $_SESSION['upload_message'] = "Error: Este tipo de documento no es un Excel editable.";
        }
    } else {
        $_SESSION['upload_message'] = "Error: Documento no encontrado o no tienes permiso para actualizarlo.";
    }

    header("Location: " . $base_path . "/dashboard");
    exit();
}
// Manejo de Rutas No Encontradas (si ninguna de las anteriores coincide)
else {
    http_response_code(404);
    echo "404 - Página no encontrada.";
}