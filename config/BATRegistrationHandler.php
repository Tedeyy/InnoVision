<?php
require_once 'database.php';

class BATRegistrationHandler {
    private $conn;
    private $table_name = 'reviewbat';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function emailExists(string $email): bool {
        $sql = "SELECT bat_id FROM {$this->table_name} WHERE email = :e LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':e' => $email]);
        return (bool)$stmt->fetch();
    }

    public function registerBAT(array $data): bool {
        $sql = "INSERT INTO {$this->table_name}
                (name, email, password_hash, assigned_barangay, docs_path)
                VALUES (:name, :email, :password_hash, :assigned_barangay, :docs_path)";

        $stmt = $this->conn->prepare($sql);
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password_hash', $hashedPassword);
        $stmt->bindParam(':assigned_barangay', $data['assigned_barangay']);
        $stmt->bindParam(':docs_path', $data['docs_path']);
        return $stmt->execute();
    }
}
?>


