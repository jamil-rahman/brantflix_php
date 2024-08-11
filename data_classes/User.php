<?php
class User {
    private $conn;
    public $error;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Method to check if a username already exists
    public function usernameExists($username) {
        $query = "SELECT user_id FROM users WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // Method to check if an email already exists
    public function emailExists($email) {
        $query = "SELECT user_id FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // Method to create a new user
    public function createUser($username, $email, $password) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user
        $query = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        if ($stmt->execute()) {
            return true;
        } else {
            $this->error = "Failed to register user.";
            return false;
        }
    }

    // Method to get user by username
    public function getUserByUsername($username) {
        $query = "SELECT user_id, username, email, password_hash FROM users WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>
