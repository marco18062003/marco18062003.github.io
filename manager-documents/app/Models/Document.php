<?php
// app/Models/Document.php

class Document {
    private $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // Tu método create existente (sin cambios significativos necesarios, ya que is_protected y password_hash tienen valores por defecto)
    public function create($userId, $title, $fileName, $fileType, $fileSize, $categoryName) {
        $query = "INSERT INTO documents (user_id, title, file_name, file_type, file_size, category_name, upload_date) VALUES (:user_id, :title, :file_name, :file_type, :file_size, :category_name, NOW())";
        $stmt = $this->conn->prepare($query);

        $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT);
        $title = htmlspecialchars(strip_tags($title));
        $fileName = htmlspecialchars(strip_tags($fileName));
        $fileType = htmlspecialchars(strip_tags($fileType));
        $fileSize = filter_var($fileSize, FILTER_SANITIZE_NUMBER_INT);
        $categoryName = htmlspecialchars(strip_tags($categoryName));

        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':file_name', $fileName, PDO::PARAM_STR);
        $stmt->bindParam(':file_type', $fileType, PDO::PARAM_STR);
        $stmt->bindParam(':file_size', $fileSize, PDO::PARAM_INT);
        $stmt->bindParam(':category_name', $categoryName, PDO::PARAM_STR);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Error PDO al crear documento: " . $e->getMessage());
        }
        return false;
    }

    /**
     * Actualiza el estado de protección y la contraseña de un documento.
     *
     * @param int $documentId El ID del documento.
     * @param int $userId El ID del usuario propietario.
     * @param bool $isProtected Si el documento debe estar protegido.
     * @param string|null $password El password plano (se hasheará), o null si no se cambia.
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
    public function updateProtection($documentId, $userId, $isProtected, $password = null) {
        $query = "UPDATE documents SET is_protected = :is_protected, document_password_hash = :document_password_hash WHERE id = :document_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        $documentId = filter_var($documentId, FILTER_SANITIZE_NUMBER_INT);
        $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT);
        $isProtected = (bool)$isProtected; // Asegura que sea booleano

        $documentPasswordHash = null;
        if ($isProtected && $password !== null) {
            $documentPasswordHash = password_hash($password, PASSWORD_DEFAULT);
        }

        $stmt->bindParam(':is_protected', $isProtected, PDO::PARAM_BOOL);
        $stmt->bindParam(':document_password_hash', $documentPasswordHash, PDO::PARAM_STR);
        $stmt->bindParam(':document_id', $documentId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error PDO al actualizar protección del documento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica la contraseña de un documento.
     *
     * @param int $documentId El ID del documento.
     * @param string $password La contraseña plana proporcionada.
     * @return bool True si la contraseña es correcta, false en caso contrario.
     */
    public function verifyDocumentPassword($documentId, $password) {
        $query = "SELECT document_password_hash FROM documents WHERE id = :document_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':document_id', $documentId, PDO::PARAM_INT);

        try {
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && isset($result['document_password_hash'])) {
                return password_verify($password, $result['document_password_hash']);
            }
        } catch (PDOException $e) {
            error_log("Error PDO al verificar contraseña del documento: " . $e->getMessage());
        }
        return false;
    }


    // Modifica findByUserId para que también obtenga is_protected y document_password_hash
    public function findByUserId($userId) {
        $query = "SELECT id, user_id, title, file_name, file_type, file_size, category_name, is_protected, upload_date FROM documents WHERE user_id = :user_id ORDER BY upload_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener documentos por usuario: " . $e->getMessage());
        }
        return [];
    }

    // Modifica findByIdAndUserId para que también obtenga is_protected y document_password_hash
    public function findByIdAndUserId($documentId, $userId) {
        $query = "SELECT id, user_id, title, file_name, file_type, file_size, category_name, is_protected, document_password_hash FROM documents WHERE id = :document_id AND user_id = :user_id";
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

    // Tus otros métodos (delete) van aquí sin cambios
    public function delete($documentId, $userId) {
        $query = "DELETE FROM documents WHERE id = :document_id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':document_id', $documentId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return $stmt->rowCount() > 0;
            }
        } catch (PDOException $e) {
            error_log("Error al eliminar documento: " . $e->getMessage());
        }
        return false;
    }
}