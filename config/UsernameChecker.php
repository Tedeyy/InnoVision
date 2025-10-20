<?php
require_once 'database.php';

class UsernameChecker {
    private $conn;
    
    // Define all user tables and their username columns
    private $userTables = [
        'reviewbuyer' => 'username',
        'reviewbat' => 'username', 
        'reviewadmin' => 'username',
        'reviewseller' => 'username'
    ];

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Check if username exists in any user table
     * @param string $username
     * @return bool
     */
    public function usernameExists($username) {
        if (!$this->conn) {
            return false;
        }

        try {
            foreach ($this->userTables as $table => $usernameColumn) {
                // Check if table exists first
                $stmt = $this->conn->prepare("SHOW TABLES LIKE :table");
                $stmt->execute([':table' => $table]);
                
                if ($stmt->fetch()) {
                    // Table exists, check for username
                    $query = "SELECT COUNT(*) FROM `{$table}` WHERE `{$usernameColumn}` = :username";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(':username', $username);
                    $stmt->execute();
                    
                    if ($stmt->fetchColumn() > 0) {
                        return true; // Username found in this table
                    }
                }
            }
            return false; // Username not found in any table
        } catch(PDOException $exception) {
            echo "Error checking username: " . $exception->getMessage();
            return false;
        }
    }

    /**
     * Check if email exists in any user table
     * @param string $email
     * @return bool
     */
    public function emailExists($email) {
        if (!$this->conn) {
            return false;
        }

        try {
            foreach ($this->userTables as $table => $usernameColumn) {
                // Check if table exists first
                $stmt = $this->conn->prepare("SHOW TABLES LIKE :table");
                $stmt->execute([':table' => $table]);
                
                if ($stmt->fetch()) {
                    // Table exists, check for email
                    $query = "SELECT COUNT(*) FROM `{$table}` WHERE `email` = :email";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(':email', $email);
                    $stmt->execute();
                    
                    if ($stmt->fetchColumn() > 0) {
                        return true; // Email found in this table
                    }
                }
            }
            return false; // Email not found in any table
        } catch(PDOException $exception) {
            echo "Error checking email: " . $exception->getMessage();
            return false;
        }
    }
}
?>
