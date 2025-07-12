<?php
// app/Models/Document.php

class Document {
    private $conn; // Conexión a la base de datos

    // El constructor recibe la conexión PDO para inyección de dependencia
    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /**
     * Crea un nuevo registro de documento en la base de datos.
     *
     * @param int $userId El ID del usuario que subió el documento.
     * @param string $title El título del documento.
     * @param string $fileName El nombre del archivo tal como se guardará en el servidor.
     * @param string $fileType El tipo MIME del archivo.
     * @param int $fileSize El tamaño del archivo en bytes.
     * @param string $categoryName El nombre de la categoría del documento. // <-- ¡NUEVO PARÁMETRO!
     * @return bool True si el documento se creó con éxito, false en caso contrario.
     */
    public function create($userId, $title, $fileName, $fileType, $fileSize, $categoryName) { // <-- ¡Añade $categoryName aquí!
        // Asegúrate de que category_name esté en la lista de columnas y en VALUES
        $query = "INSERT INTO documents (user_id, title, file_name, file_type, file_size, category_name, upload_date) VALUES (:user_id, :title, :file_name, :file_type, :file_size, :category_name, NOW())";
                                                                                         // ^^^^^^^^^^^ Añadido 'category_name'
                                                                                                                   // ^^^^^^^^^^^ Añadido ':category_name' y NOW() para upload_date
        $stmt = $this->conn->prepare($query);

        // Limpiar y vincular parámetros
        // No es necesario htmlspecialchars y strip_tags aquí si ya lo haces en la capa del controlador
        // y PDO::bindParam ya maneja la seguridad contra inyección SQL.
        // Pero si quieres mantenerlos por consistencia, asegúrate de que $fileSize sea int y no string.
        $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT); // Mejor para int
        $title = htmlspecialchars(strip_tags($title));
        $fileName = htmlspecialchars(strip_tags($fileName));
        $fileType = htmlspecialchars(strip_tags($fileType));
        $fileSize = filter_var($fileSize, FILTER_SANITIZE_NUMBER_INT); // Asegurar que sea int
        $categoryName = htmlspecialchars(strip_tags($categoryName)); // <-- ¡NUEVA SANITIZACIÓN!


        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT); // Especifica tipo INT
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':file_name', $fileName, PDO::PARAM_STR);
        $stmt->bindParam(':file_type', $fileType, PDO::PARAM_STR);
        $stmt->bindParam(':file_size', $fileSize, PDO::PARAM_INT); // Especifica tipo INT
        $stmt->bindParam(':category_name', $categoryName, PDO::PARAM_STR); // <-- ¡Vinculado correctamente!

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            // Esto registrará el error exacto de la base de datos en los logs de PHP.
            error_log("Error PDO al crear documento: " . $e->getMessage());
            // En desarrollo, puedes añadir temporalmente un echo para ver el error directamente
            // echo "Error PDO: " . $e->getMessage();
        }
        return false;
    }

    /**
     * Obtiene todos los documentos para un usuario específico.
     *
     * @param int $userId El ID del usuario.
     * @return array Un array de documentos o un array vacío si no se encuentran.
     */
    public function findByUserId($userId) {
        // Asegúrate de que category_name esté seleccionado aquí también
        $query = "SELECT id, user_id, title, file_name, file_type, file_size, category_name, upload_date FROM documents WHERE user_id = :user_id ORDER BY upload_date DESC";
                                                                                         // ^^^^^^^^^^^ Asegúrate que esté aquí
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT); // Asegurar que es un entero

        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Devolver todos los resultados como un array asociativo
        } catch (PDOException $e) {
            error_log("Error al obtener documentos por usuario: " . $e->getMessage());
        }
        return [];
    }

    /**
     * Elimina un documento por su ID y el ID del usuario (para seguridad).
     *
     * @param int $documentId El ID del documento a eliminar.
     * @param int $userId El ID del usuario propietario del documento.
     * @return bool True si el documento fue eliminado, false en caso contrario.
     */
    public function delete($documentId, $userId) {
        $query = "DELETE FROM documents WHERE id = :document_id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':document_id', $documentId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return $stmt->rowCount() > 0; // Verifica si se eliminó alguna fila
            }
        } catch (PDOException $e) {
            error_log("Error al eliminar documento: " . $e->getMessage());
        }
        return false;
    }

    /**
     * Obtiene un solo documento por su ID y el ID del usuario (para seguridad).
     * Útil para descargar o ver documentos específicos.
     *
     * @param int $documentId El ID del documento.
     * @param int $userId El ID del usuario propietario del documento.
     * @return array|false Un array asociativo con los datos del documento o false si no se encuentra.
     */
    public function findByIdAndUserId($documentId, $userId) {
        // Si necesitas category_name aquí, añádelo a la SELECT
        $query = "SELECT id, user_id, title, file_name, file_type, file_size, category_name FROM documents WHERE id = :document_id AND user_id = :user_id";
                                                                                            // ^^^^^^^^^^^ ¡Añadido 'category_name' aquí también!
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':document_id', $documentId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        try {
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al buscar documento por ID de usuario: " . $e->getMessage());
        }
        return false;
    }
}