<?php
require_once 'database.php';

class AdminRegistrationHandler {
    private $conn;
    private $table_name = 'reviewadmin';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function usernameExists(string $username): bool {
        $sql = "SELECT user_id FROM {$this->table_name} WHERE username = :u LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':u' => $username]);
        return (bool)$stmt->fetch();
    }

    public function emailExists(string $email): bool {
        $sql = "SELECT user_id FROM {$this->table_name} WHERE email = :e LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':e' => $email]);
        return (bool)$stmt->fetch();
    }

    public function registerAdmin(array $data): bool {
        $sql = "INSERT INTO {$this->table_name}
                (user_fname, user_mname, user_lname, bdate, contact, email, office, role, supdoctype, username, password, docs_path)
                VALUES (:user_fname, :user_mname, :user_lname, :bdate, :contact, :email, :office, :role, :supdoctype, :username, :password, :docs_path)";

        $stmt = $this->conn->prepare($sql);
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->bindParam(':user_fname', $data['user_fname']);
        $stmt->bindParam(':user_mname', $data['user_mname']);
        $stmt->bindParam(':user_lname', $data['user_lname']);
        $stmt->bindParam(':bdate', $data['bdate']);
        $stmt->bindParam(':contact', $data['contact']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':office', $data['office']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':supdoctype', $data['supdoctype']);
        $stmt->bindParam(':username', $data['username']);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':docs_path', $data['docs_path']);
        return $stmt->execute();
    }
}
?>


