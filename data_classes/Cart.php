<?php

class Cart {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Existing addToCart method
    public function addToCart($user_id, $movie_id, $quantity) {
        $stmt = $this->conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND movie_id = ?");
        $stmt->bind_param("ii", $user_id, $movie_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $cartItem = $result->fetch_assoc();
            $newQuantity = $cartItem['quantity'] + $quantity;
            $stmt = $this->conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND movie_id = ?");
            $stmt->bind_param("iii", $newQuantity, $user_id, $movie_id);
        } else {
            $stmt = $this->conn->prepare("INSERT INTO cart (user_id, movie_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $movie_id, $quantity);
        }
        
        return $stmt->execute();
    }

    public function getCartItems($user_id) {
        $stmt = $this->conn->prepare("SELECT c.cart_id, c.movie_id, c.quantity, m.title, m.photo, m.price 
                                      FROM cart c 
                                      JOIN movies m ON c.movie_id = m.movie_id 
                                      WHERE c.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function removeCartItem($cart_id) {
        $stmt = $this->conn->prepare("DELETE FROM cart WHERE cart_id = ?");
        $stmt->bind_param("i", $cart_id);
        return $stmt->execute();
    }

    public function getCartTotal($user_id) {
        $stmt = $this->conn->prepare("SELECT SUM(m.price * c.quantity) AS total 
                                      FROM cart c 
                                      JOIN movies m ON c.movie_id = m.movie_id 
                                      WHERE c.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    public function checkout($user_id) {
        // Start transaction
        $this->conn->begin_transaction();
    
        try {
            // Delete cart items for this user
            $stmt = $this->conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
    
            // Check if deletion was successful
            if ($stmt->affected_rows === 0) {
                throw new Exception("Cart deletion failed");
            }
    
            // Commit transaction
            $this->conn->commit();
            return true;
    
        } catch (Exception $e) {
            // Rollback transaction in case of error
            $this->conn->rollback();
            return false;
        }
    }
    
}
?>