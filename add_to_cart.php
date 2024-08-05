<?php
session_start();
require 'db.php'; // Include database connection file
require 'Cart.php'; // Include Cart class file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    // Get form data
    $user_id = $_SESSION['user_id'];
    $movie_id = $_POST['movie_id'];
    $quantity = $_POST['quantity'];

    // Create a new Cart object
    $cart = new Cart($conn);
    
    // Add the movie to the cart
    if ($cart->addToCart($user_id, $movie_id, $quantity)) {
        header('Location: index.php?status=success');
    } else {
        header('Location: index.php?status=error');
    }
    exit();
}
?>
