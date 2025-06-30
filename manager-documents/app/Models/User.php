<?php
// app/Models/User.php

// No es necesario incluir db.php aquí si la conexión PDO se va a inyectar
// desde el archivo que instancia User (por ejemplo, index.php, register.php, login.php).
// Si User.php se usa de forma independiente sin inyección, entonces sí sería necesario.
// Para este flujo de trabajo, lo ideal es inyectar la conexión.
// require_once __DIR__ . '/../Config/db.php'; // Comentado o eliminado

class User {
    private $conn;
    private $table_name = "users"; // Asegúrate de que este sea el nombre real de tu tabla en la DB

    // Propiedades del usuario (útiles para asignar los datos obtenidos de la DB o antes de crear)
    public $id;
    public $username;
    public $email;
    public $password; // Esta propiedad guardaría el hash de la contraseña
    public $created_at; // Si tienes este campo en tu tabla

    /**
     * Constructor que recibe la conexión PDO a la base de datos.
     * Esto es una "inyección de dependencia" y mejora la flexibilidad y la capacidad de prueba.
     * @param PDO $db Objeto de conexión PDO a la base de datos.
     */
    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     * La contraseña DEBE ser hasheada ANTES de pasarla a este método.
     *
     * @param string $username El nombre de usuario.
     * @param string $email El correo electrónico del usuario (debe ser único).
     * @param string $hashedPassword La contraseña hasheada (usando password_hash()).
     * @return bool True si el usuario se creó con éxito, False en caso contrario.
     */
    public function create($username, $email, $hashedPassword) {
        // Consulta SQL para insertar un nuevo usuario
        $query = "INSERT INTO " . $this->table_name . " (username, email, password) VALUES (:username, :email, :password)";

        // Prepara la consulta para ejecución
        $stmt = $this->conn->prepare($query);

        // Limpiar y sanitizar datos de entrada para evitar ataques XSS
        $username = htmlspecialchars(strip_tags($username));
        $email = htmlspecialchars(strip_tags($email));
        // La contraseña ya debe venir hasheada, no la sanitizamos con strip_tags/htmlspecialchars aquí

        // Vincular los parámetros a la consulta preparada
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $hashedPassword); // Aquí ya se espera el hash

        // Ejecutar la consulta
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId(); // Opcional: guardar el ID del nuevo usuario
            return true;
        }

        // Si la ejecución falla, puedes loggear el error para depuración
        // error_log("Error al crear usuario: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    /**
     * Encuentra un usuario en la base de datos por su dirección de correo electrónico.
     *
     * @param string $email La dirección de correo electrónico a buscar.
     * @return array|false Un array asociativo con los datos del usuario si se encuentra, o false si no.
     */
    public function findByEmail($email) {
        // Consulta SQL para seleccionar un usuario por email
        // Se seleccionan todos los campos necesarios para la autenticación y mostrar perfil
        $query = "SELECT id, username, email, password FROM " . $this->table_name . " WHERE email = :email LIMIT 0,1";

        // Prepara la consulta
        $stmt = $this->conn->prepare($query);

        // Limpiar y sanitizar el email para la consulta
        $email = htmlspecialchars(strip_tags($email));

        // Vincular el parámetro email
        $stmt->bindParam(":email", $email);

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener el resultado
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Opcional: Asignar los valores a las propiedades del objeto si el usuario fue encontrado
        if ($result) {
            $this->id = $result['id'];
            $this->username = $result['username'];
            $this->email = $result['email'];
            $this->password = $result['password']; // Aquí se almacena la contraseña hasheada
            // No asignamos created_at si no lo seleccionamos
        }

        return $result; // Retorna el array asociativo directamente
    }

    /**
     * Verifica si un correo electrónico ya existe en la base de datos.
     * Útil durante el registro para evitar duplicados.
     *
     * @param string $email El correo electrónico a verificar.
     * @return bool True si el email ya existe, False si no.
     */
    public function emailExists($email) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);

        $email = htmlspecialchars(strip_tags($email));
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }
}
?>