<?php

class Cart {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function addToCart($user_id, $movie_id, $quantity) {
        // Check if the movie is already in the cart
        $stmt = $this->conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND movie_id = ?");
        $stmt->bind_param("ii", $user_id, $movie_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update quantity if the movie is already in the cart
            $cartItem = $result->fetch_assoc();
            $newQuantity = $cartItem['quantity'] + $quantity;
            $stmt = $this->conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND movie_id = ?");
            $stmt->bind_param("iii", $newQuantity, $user_id, $movie_id);
        } else {
            // Insert new movie into the cart
            $stmt = $this->conn->prepare("INSERT INTO cart (user_id, movie_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $movie_id, $quantity);
        }
        
        return $stmt->execute();
    }
}
?>
