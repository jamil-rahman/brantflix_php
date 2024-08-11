<?php
session_start();
require 'db.php'; // Include database connection
require 'data_classes/Cart.php'; // Include Cart class

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$cart = new Cart($conn);
$cartItems = $cart->getCartItems($user_id);



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Brantflix - Checkout</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oxygen:wght@300;400;700&family=Raleway:ital,wght@0,100..900;1,100..900&family=Roboto&display=swap');

        body {
            background-color: #211F20;
            font-family: "Montserrat", sans-serif;
            font-optical-sizing: auto;
            font-style: normal;
        }

        .navbar {
            margin-bottom: 40px;
            border-bottom: 1px solid red;
            padding: 20px;
            background-color: black;
        }

        .item {
            position: relative;
            display: inline-block;
            font-size: 18px;
            font-weight: 800;
            color: white;
            overflow: hidden;
            background: linear-gradient(to right, white, salmon 50%, red 50%);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size: 200% 100%;
            background-position: 100%;
            transition: background-position 275ms ease;
            text-decoration: none;

            &:hover {
                background-position: 0 100%;
            }
        }

        #logout {
            color: white;
        }

        .card-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            justify-content: center;
        }

        .card {
            width: 18rem;
            background-color: #211F20;
            color: white;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.9);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
        }

        .card-title {
            font-family: "Oxygen", sans-serif;
            font-weight: 700;
            font-size: 18px;
            color: salmon;
            letter-spacing: 1px;
        }

        .card-img-top {
            width: 100%;
            height: 420px;
            object-fit: cover;
            object-position: center;
        }

        .footer {
            background-color: black;
            color: white;
            margin-top: 60px;
            text-align: center;
            padding: 10px;
            position: relative;
            width: 100%;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">

            <a class="navbar-brand" href="index.php"> <img src="assets/logo.png" height="100" width="100" alt="Brantflix logo"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item ">
                        <a class="nav-link item" aria-current="page" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link item active" href="whats_new.php">What's New</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link item" href="checkout.php">Cart</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <p class="nav-item">
                        <a class="nav-link" href="logout.php" id="logout">Logout</a>
                    </p>
                </ul>
            </div>
        </div>
    </nav>


    <div class="container mt-5">
        <h2 class="title">Your Cart</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if (count($cartItems) > 0): ?>
            <div class="row">
                <?php foreach ($cartItems as $item): ?>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <img src="<?= $item['photo'] ?>" class="card-img-top" alt="<?= $item['title'] ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= $item['title'] ?></h5>
                                <p class="card-text">Price: $<?= $item['price'] ?> ea</p>
                                <p class="card-text">Quantity: <?= $item['quantity'] ?></p>
                                <form method="POST">
                                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                    <button type="submit" name="remove_item" class="btn btn-remove w-100">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <a href="order.php"><button type="submit" name="checkout" class="mt-3 btn btn-success">Proceed to Checkout</button></a>

        <?php else: ?>
            <div class="alert alert-info">
                Your cart is empty.
            </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <img src="assets/logo.png" height="150" width="150" alt="Brantflix logo">
        <p>&copy; All rights reserved.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>