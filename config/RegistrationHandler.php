<?php
require_once 'database.php';

class RegistrationHandler {
    private $conn;
    private $table_name = "reviewseller";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Register a new seller
     * @param array $userData
     * @return bool
     */
    public function registerSeller($userData) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                     (user_fname, user_mname, user_lname, bdate, contact, email, rsbsanum, idnum, username, password, docs_path) 
                     VALUES (:user_fname, :user_mname, :user_lname, :bdate, :contact, :email, :rsbsanum, :idnum, :username, :password, :docs_path)";

            $stmt = $this->conn->prepare($query);

            // Hash the password for security
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

            $stmt->bindParam(':user_fname', $userData['user_fname']);
            $stmt->bindParam(':user_mname', $userData['user_mname']);
            $stmt->bindParam(':user_lname', $userData['user_lname']);
            $stmt->bindParam(':bdate', $userData['bdate']);
            $stmt->bindParam(':contact', $userData['contact']);
            $stmt->bindParam(':email', $userData['email']);
            $stmt->bindParam(':rsbsanum', $userData['rsbsanum']);
            $stmt->bindParam(':idnum', $userData['idnum']);
            $stmt->bindParam(':username', $userData['username']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':docs_path', $userData['docs_path']);

            return $stmt->execute();
        } catch(PDOException $exception) {
            echo "Error registering seller: " . $exception->getMessage();
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
