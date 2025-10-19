<?php
require_once 'database.php';

class BuyerRegistrationHandler {
    private $conn;
    private $table_name = "reviewbuyer";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Register a new buyer
     * @param array $userData
     * @return bool
     */
    public function registerBuyer($userData) {
        try {
            // Check if database connection exists
            if (!$this->conn) {
                echo "Database connection failed!<br>";
                return false;
            }
            
            echo "Database connection successful!<br>";
            echo "Table name: " . $this->table_name . "<br>";
            
            $query = "INSERT INTO " . $this->table_name . " 
                     (user_fname, user_mname, user_lname, bdate, contact, email, supdoctype, supdocnum, username, password, docs_path) 
                     VALUES (:user_fname, :user_mname, :user_lname, :bdate, :contact, :email, :supdoctype, :supdocnum, :username, :password, :docs_path)";

            echo "SQL Query: " . $query . "<br>";
            
            $stmt = $this->conn->prepare($query);

            // Hash the password for security
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

            $stmt->bindParam(':user_fname', $userData['user_fname']);
            $stmt->bindParam(':user_mname', $userData['user_mname']);
            $stmt->bindParam(':user_lname', $userData['user_lname']);
            $stmt->bindParam(':bdate', $userData['bdate']);
            $stmt->bindParam(':contact', $userData['contact']);
            $stmt->bindParam(':email', $userData['email']);
            $stmt->bindParam(':supdoctype', $userData['supdoctype']);
            $stmt->bindParam(':supdocnum', $userData['supdocnum']);
            $stmt->bindParam(':username', $userData['username']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':docs_path', $userData['docs_path']);

            $result = $stmt->execute();
            echo "Execute result: " . ($result ? "SUCCESS" : "FAILED") . "<br>";
            
            if (!$result) {
                echo "PDO Error Info: <pre>" . print_r($stmt->errorInfo(), true) . "</pre>";
            }
            
            return $result;
        } catch(PDOException $exception) {
            echo "Error registering buyer: " . $exception->getMessage() . "<br>";
            echo "Error Code: " . $exception->getCode() . "<br>";
            return false;
        }
    }

    /**
     * Check if username already exists
     * @param string $username
     * @return bool
     */
    public function usernameExists($username) {
        try {
            $query = "SELECT user_id FROM " . $this->table_name . " WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch(PDOException $exception) {
            echo "Error checking username: " . $exception->getMessage();
            return false;
        }
    }

    /**
     * Check if email already exists
     * @param string $email
     * @return bool
     */
    public function emailExists($email) {
        try {
            $query = "SELECT user_id FROM " . $this->table_name . " WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch(PDOException $exception) {
            echo "Error checking email: " . $exception->getMessage();
            return false;
        }
    }
}
?>
