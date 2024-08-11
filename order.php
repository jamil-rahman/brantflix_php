<?php

session_start();
require 'db.php'; 
require 'data_classes/Cart.php'; 
require './fpdf186/fpdf.php'; 


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$cart = new Cart($conn);
$cartItems = $cart->getCartItems($user_id);
$cartTotal = $cart->getCartTotal($user_id);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    // Validate form inputs
    $full_name = $_POST['full_name'];
    $street_name = $_POST['street_name'];
    $unit_number = $_POST['unit_number'];
    $contact_info = $_POST['contact_info'];

    $errors = [];

    if (empty($full_name)) {
        $errors[] = "Full Name is required";
    }

    if (empty($street_name)) {
        $errors[] = "Street Name is required";
    }

    if (empty($contact_info)) {
        $errors[] = "Contact Info is required";
    }

    if (empty($errors)) {
        // Process order and clear cart
        if ($cart->checkout($user_id)) {
            // Store delivery details in orders table
            $stmt = $conn->prepare("INSERT INTO orders (user_id, full_name, street_name, unit_number, contact_info) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $user_id, $full_name, $street_name, $unit_number, $contact_info);
            $stmt->execute();

            $order_id = $stmt->insert_id;
            error_log("Starting PDF generation...");

            // Generate PDF invoice
            generatePDFInvoice($order_id, $cartItems, $full_name, $street_name, $unit_number, $contact_info, $cartTotal);


            header('Location: success.php');
            exit();
        } else {
            $errors[] = "Checkout failed. Please try again.";
        }
    }
}


function generatePDFInvoice($order_id, $cartItems, $full_name, $street_name, $unit_number, $contact_info, $total) {
    try {
        ob_start();
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Invoice Title
        $pdf->Cell(0, 10, 'Brantflix Invoice', 0, 1, 'C');

        // Order ID
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, "Order ID: $order_id", 0, 1);

        // Delivery Info
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, "Full Name: $full_name", 0, 1);
        $pdf->Cell(0, 10, "Street Name: $street_name", 0, 1);
        $pdf->Cell(0, 10, "Unit Number: $unit_number", 0, 1);
        $pdf->Cell(0, 10, "Contact Info: $contact_info", 0, 1);

        // Cart Items
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(100, 10, 'Movie', 1);
        $pdf->Cell(30, 10, 'Quantity', 1);
        $pdf->Cell(30, 10, 'Price', 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 12);
        foreach ($cartItems as $item) {
            $pdf->Cell(100, 10, $item['title'], 1);
            $pdf->Cell(30, 10, $item['quantity'], 1);
            $pdf->Cell(30, 10, '$' . $item['price'], 1);
            $pdf->Ln();
        }

        // Total
        $pdf->Cell(130, 10, 'Total', 1);
        $pdf->Cell(30, 10, '$' . $total, 1);
        $pdf->Ln();

        ob_end_clean();
        // Output PDF
        $pdf->Output('F', 'invoice.pdf');
    } catch (Exception $e) {
        error_log("PDF generation failed: " . $e->getMessage());
        throw $e;
    }
}


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
            background-color: #343a40;
            color: white;
            margin-top: 20px;
            text-align: center;
            padding: 10px;
            position: relative;
            width: 100%;
        }
        .label {
            color: white;
        }
        .cart-item {
            margin-bottom: 10px;
        }

        .btn-confirm {
            background-color: #28a745;
            border-color: #28a745;
            color: white
        }
        .btn-confirm:hover {
            transform: scale(1.05);
            border-color: #28a745;
            background-color: #28a745;
            color: darkgreen
        }
    </style>
</head>

<body>

    <!-- Navbar -->
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


    <div class="container w-50 mt-5">
        <h2 class="title">Checkout</h2>

        <hr style="color: white"/>

        <?php if (isset($errors) && count($errors) > 0): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (count($cartItems) > 0): ?>
            <div class="mb-4 w-50 container">
                <h4 class="title text-warning text-bold">Your Cart Items</h4>
                <ul class="list-group">
                    <?php foreach ($cartItems as $item): ?>
                        <li class="list-group-item cart-item">
                            <strong class="text-danger"><?= $item['title'] ?></strong>
                            <br>Quantity: <?= $item['quantity'] ?>
                            <br>Price: $<?= $item['price'] ?> ea
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="mb-4">
                <h3 class="title">Total: <span style="color:#28a745">$<?= $cartTotal ?></span></h3>
            </div>

            <hr style="color: white"/>

            <form method="POST">
                <h4 class="title text-warning">Delivery Instructions</h4>
                <div class="mb-3">
                    <label for="full_name" class="form-label label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                <div class="mb-3">
                    <label for="street_name" class="form-label label">Street Name</label>
                    <input type="text" class="form-control" id="street_name" name="street_name" required>
                </div>
                <div class="mb-3">
                    <label for="unit_number" class="form-label label">Unit Number</label>
                    <input type="text" class="form-control" id="unit_number" name="unit_number">
                </div>
                <div class="mb-3">
                    <label for="contact_info" class="form-label label">Contact Info</label>
                    <input type="text" class="form-control" id="contact_info" name="contact_info" required>
                </div>

               

                <button type="submit" name="confirm_order" class="btn btn-confirm">Confirm Order</button>
            </form>
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