<?php
session_start();
require 'db.php'; // Include database connection file
require 'User.php'; // Include User class file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch movies from the database, ordered by release date descending
$query = "SELECT movie_id, title, photo, price, description, release_date FROM movies ORDER BY release_date DESC";
$result = $conn->query($query);
$movies = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Brantflix - What's New</title>
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
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Brantflix</a>
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
                        <a class="nav-link item" href="#">Check Out</a>
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

    <!-- Movie Cards -->
    <div class="container">
        <div class="card-grid">
            <?php foreach ($movies as $movie) : ?>
                <div class="card">
                    <img src="<?= $movie['photo'] ?>" class="card-img-top" alt="<?= $movie['title'] ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= $movie['title'] ?></h5>
                        <details>
                            <summary>Description</summary>
                            <p class="card-text"><?= $movie['description'] ?></p>
                        </details>
                        <p class="card-text"><strong>Price:</strong> $<?= $movie['price'] ?></p>
                        <p class="card-text"><strong>Release Year:</strong> <?= date('Y', strtotime($movie['release_date'])) ?></p>
                        <form action="add_to_cart.php" method="POST">
                            <input type="hidden" name="movie_id" value="<?= $movie['movie_id'] ?>">
                            <div class="mb-3">
                                <label for="quantity_<?= $movie['movie_id'] ?>" class="form-label">Quantity</label>
                                <input type="number" style="width: 30%;" class="form-control" id="quantity_<?= $movie['movie_id'] ?>" name="quantity" value="1" min="1" required>
                            </div>
                            <button type="submit" class="btn btn-dark w-100"><img src="assets/cart.png" height="20" width="20" /> Add to Cart</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2024 Brantflix. All rights reserved.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
