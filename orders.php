<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "bakery_db");

$user_id = $_SESSION['user_id'];

$cart_items = [];
$stmt = $conn->prepare("
    SELECT p.id, p.name, p.price, c.quantity, p.image_url 
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

if (empty($cart_items)) {
    $order_success = false;
} else {
    $order_success = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $total_amount = 0;
    foreach ($cart_items as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }

    $order_number = "ORD-" . time();

    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, order_number, total_amount, delivery_address, contact_phone, payment_method, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $delivery_address = "ул. Пекарская, д. 15";
    $contact_phone = "+7 (123) 456-78-90";
    $payment_method = "Наличные";
    $status = "pending";
    $stmt->bind_param(
        "ississs",
        $user_id,
        $order_number,
        $total_amount,
        $delivery_address,
        $contact_phone,
        $payment_method,
        $status
    );
    $stmt->execute();
    $order_id = $stmt->insert_id;

    foreach ($cart_items as $item) {
        $product_id = $item['id'];
        $product_name = $item['name'];
        $product_price = $item['price'];
        $quantity = $item['quantity'];
        $subtotal = $product_price * $quantity;

        $stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiidii", $order_id, $product_id, $product_name, $product_price, $quantity, $subtotal);
        $stmt->execute();
    }

    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $order_success = true;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои заказы</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"  rel="stylesheet">
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
                            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['username']); ?>
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

<div class="container mt-5">

    <?php if ($order_success === true): ?>
        <div class="alert alert-success text-center">
            <h4>Заказ успешно оформлен!</h4>
            <p>Номер вашего заказа: <strong><?= $order_number ?></strong></p>
            <a href="index.php" class="btn btn-primary">На главную</a>
        </div>
    <?php elseif ($order_success === false): ?>
        <div class="alert alert-info text-center">
            <h4>Ваша корзина пуста.</h4>
            <a href="index.php" class="btn btn-primary">Перейти в магазин</a>
        </div>
    <?php else: ?>

        <h2>Оформление заказа</h2>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Товар</th>
                <th>Цена</th>
                <th>Количество</th>
                <th>Сумма</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= number_format($item['price'], 2) ?> ₽</td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['price'] * $item['quantity'], 2) ?> ₽</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <form method="POST">
            <button type="submit" name="checkout" class="btn btn-success">Оформить заказ</button>
        </form>

    <?php endif; ?>

</div>

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