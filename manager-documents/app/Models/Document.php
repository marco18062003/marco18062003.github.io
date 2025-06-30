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
     * @return bool True si el documento se creó con éxito, false en caso contrario.
     */
    public function create($userId, $title, $fileName, $fileType, $fileSize) {
        $query = "INSERT INTO documents (user_id, title, file_name, file_type, file_size) VALUES (:user_id, :title, :file_name, :file_type, :file_size)";

        $stmt = $this->conn->prepare($query);

        // Limpiar y vincular parámetros
        $userId = htmlspecialchars(strip_tags($userId));
        $title = htmlspecialchars(strip_tags($title));
        $fileName = htmlspecialchars(strip_tags($fileName));
        $fileType = htmlspecialchars(strip_tags($fileType));
        $fileSize = htmlspecialchars(strip_tags($fileSize));

        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':file_name', $fileName);
        $stmt->bindParam(':file_type', $fileType);
        $stmt->bindParam(':file_size', $fileSize);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            // En un entorno de producción, logear el error en lugar de mostrarlo
            error_log("Error al crear documento: " . $e->getMessage());
            // Opcional: throw $e; si quieres que el error se propague
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
        $query = "SELECT id, user_id, title, file_name, file_type, file_size, upload_date FROM documents WHERE user_id = :user_id ORDER BY upload_date DESC";

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
        $query = "SELECT id, user_id, title, file_name, file_type, file_size FROM documents WHERE id = :document_id AND user_id = :user_id";

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