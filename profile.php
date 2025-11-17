<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bakery_db";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = [];
$sql = "SELECT username, email, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Bakery - Свежая выпечка каждый день</title>
    <meta name="description" content="Свежая выпечка, хлеб и кондитерские изделия с доставкой и самовывозом. Натуральные ингредиенты, традиционные рецепты.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#">Daily Bakery</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Главная</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">О нас</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Контакты</a>
                </li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Профиль</a></li>
                            <li><a class="dropdown-item" href="orders.php"><i class="bi bi-bag me-2"></i>Мои заказы</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Выйти</a></li>
                        </ul>
                    </li>

                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Войти</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php"><i class="bi bi-person-plus me-1"></i>Регистрация</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="section-title mb-4">Профиль пользователя</h2>
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="mb-3">
                                    <i class="bi bi-person-circle" style="font-size: 5rem;"></i>
                                </div>
                                <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                            </div>
                            <div class="col-md-8">
                                <dl class="row">
                                    <dt class="col-sm-4">Email:</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($user['email']); ?></dd>

                                    <dt class="col-sm-4">Дата регистрации:</dt>
                                    <dd class="col-sm-8"><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></dd>
                                </dl>
                                <div class="mt-4">
                                    <a href="logout.php" class="btn btn-danger">
                                        <i class="bi bi-box-arrow-right"></i> Выйти
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5 class="mb-4">Daily Bakery</h5>
                <p>Свежая выпечка и кондитерские изделия с доставкой по городу. Натуральные ингредиенты, традиционные рецепты.</p>
                <div class="social-icons">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-twitter"></i></a>
                    <a href="#"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
            <div class="col-md-2">
                <div class="footer-links">
                    <h5>Меню</h5>
                    <ul>
                        <li><a href="index.php">Главная</a></li>
                        <li><a href="about.php">О нас</a></li>
                        <li><a href="contact.php">Контакты</a></li>
                    </ul>
                </div>
            </div>

            <div class="col-md-3">
                <div class="footer-links">
                    <h5>Контакты</h5>
                    <ul>
                        <li><i class="bi bi-geo-alt me-2"></i>ул. Пекарская, 15</li>
                        <li><i class="bi bi-telephone me-2"></i>+7 (123) 456-78-90</li>
                        <li><i class="bi bi-envelope me-2"></i>info@dailybakery.ru</li>
                        <li><i class="bi bi-clock me-2"></i>Пн-Пт: 8:00 - 20:00</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="copyright text-center">
            <p>&copy; 2023 Daily Bakery. Все права защищены.</p>
        </div>
    </div>
</footer>
</body>
</html>