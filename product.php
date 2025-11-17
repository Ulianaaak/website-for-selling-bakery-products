<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bakery_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$product = [];
$reviews = [];
$similar_products = [];

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {

        $stmt = $conn->prepare("SELECT * FROM reviews WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $reviews = $result->fetch_all(MYSQLI_ASSOC);

        $stmt = $conn->prepare("SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4");
        $stmt->bind_param("si", $product['category'], $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $similar_products = $result->fetch_all(MYSQLI_ASSOC);
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name'] ?? 'Продукт') ?> - Daily Bakery</title>
    <meta name="description" content="<?= htmlspecialchars($product['short_description'] ?? 'Описание продукта') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"  rel="stylesheet">
>
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

<?php if ($product): ?>
    <section class="hero-section" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?= htmlspecialchars($product['image_url']) ?>')">
        <div class="container text-center">
            <h1 class="hero-title"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="hero-subtitle"><?= htmlspecialchars($product['short_description']) ?></p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-5">
                <!-- Галерея -->
                <div class="col-lg-6">
                    <div class="product-gallery">
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" class="product-main-image" alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="product-thumbnails">
                            <?php for ($i = 0; $i < 3; $i++): ?>
                                <img src="<?= htmlspecialchars($product['image_url']) ?>" class="product-thumbnail" alt="">
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="product-details-card">
                        <h2 class="mb-4"><?= htmlspecialchars($product['name']) ?></h2>
                        <div class="d-flex align-items-center mb-4">
                            <span class="product-price fs-2 me-3"><?= number_format($product['price'], 2) ?> ₽</span>
                            <?php if (rand(0, 1)): ?>
                                <span class="badge bg-danger fs-6">Скидка <?= rand(10, 25) ?>%</span>
                            <?php endif; ?>
                        </div>
                        <div class="card mt-4">
                            <div class="card-body">
                                <form id="addToCartForm" method="post">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-auto">
                                            <label for="quantity" class="col-form-label">Количество:</label>
                                        </div>
                                        <div class="col-auto">
                                            <input type="number"
                                                   class="form-control"
                                                   id="quantity"
                                                   name="quantity"
                                                   value="1"
                                                   min="1"
                                                   max="99"
                                                   style="width: 80px">
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit"
                                                    class="btn btn-primary"
                                                <?= !isset($_SESSION['user_id']) ? 'disabled' : '' ?>>
                                                <i class="bi bi-cart-plus"></i> В корзину
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                <?php if (!isset($_SESSION['user_id'])): ?>
                                    <div class="mt-2 text-danger small">Требуется авторизация для добавления в корзину</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <h4 class="mt-5 mb-3">Описание</h4>
                        <p class="text-muted"><?= htmlspecialchars($product['description']) ?></p>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5>Характеристики</h5>
                                <ul class="list-unstyled text-muted">
                                    <li><strong>Вес:</strong> <?= $product['weight'] ?> г</li>
                                    <li><strong>Категория:</strong> <?= htmlspecialchars($product['category']) ?></li>
                                    <li><strong>Состав:</strong> <?= htmlspecialchars($product['ingredients']) ?></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Пищевая ценность</h5>
                                <ul class="list-unstyled text-muted">
                                    <?php if ($product['nutritional_value']): ?>
                                        <?php foreach (json_decode($product['nutritional_value'], true) as $item): ?>
                                            <li><strong><?= $item['name'] ?>:</strong> <?= $item['value'] ?></li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li>Информация отсутствует</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <section class="mt-5">
                <h3 class="section-title">Отзывы (<?= count($reviews) ?>)</h3>
                <?php if (isset($_SESSION['username'])): ?>
                    <div class="mb-5">
                        <h4>Оставить отзыв</h4>
                        <form id="reviewForm" method="POST">
                            <div class="mb-3">
                                <select class="form-select" id="rating" name="rating" required>
                                    <option value="">Выберите оценку</option>
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <option value="<?= $i ?>"><?= $i ?> звезд<?= $i !== 1 ? '' : '' ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" name="comment" placeholder="Ваш отзыв..." rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Отправить отзыв</button>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-0"><?= htmlspecialchars($review['user_id']) ?></h5>
                                    <small class="text-muted"><?= date('d.m.Y', strtotime($review['created_at'])) ?></small>
                                </div>
                                <div class="rating-stars">
                                    <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                        <i class="bi bi-star-fill"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="mb-0"><?= htmlspecialchars($review['comment']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <?php if (!empty($similar_products)): ?>
                <section class="mt-5">
                    <h3 class="section-title">Похожие товары</h3>
                    <div class="row">
                        <?php foreach ($similar_products as $prod): ?>
                            <div class="col-md-3">
                                <div class="product-card">
                                    <img src="<?= htmlspecialchars($prod['image_url']) ?>" class="product-img" alt="<?= htmlspecialchars($prod['name']) ?>">
                                    <div class="product-body">
                                        <h5 class="product-title"><?= htmlspecialchars($prod['name']) ?></h5>
                                        <p class="text-muted small"><?= htmlspecialchars($prod['short_description']) ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="product-price"><?= number_format($prod['price'], 2) ?> ₽</span>
                                            <a href="product.php?id=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-primary">Подробнее</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </section>
<?php else: ?>
    <section class="py-5">
        <div class="container text-center">
            <h2 class="text-danger">Продукт не найден</h2>
            <p>Запрошенный продукт не существует или был удален</p>
            <a href="catalog.php" class="btn btn-primary">Вернуться в каталог</a>
        </div>
    </section>
<?php endif; ?>

<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5 class="mb-4">Daily Bakery</h5>
                <p>Свежая выпечка и кондитерские изделия с доставкой по городу.</p>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById('reviewForm');
        if (!form) return;

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(form);
            formData.append('product_id', <?= $product['id'] ?>);

            try {
                const response = await fetch('submit_review.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    alert(result.message);
                    location.reload();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Ошибка сети');
            }
        });
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const cartCountElement = document.getElementById("cart-count");

        const addToCartForm = document.getElementById('addToCartForm');
        if (addToCartForm) {
            addToCartForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const productId = <?= json_encode($product['id'] ?? null) ?>;

                if (!productId) {
                    alert("Ошибка: ID товара не найден");
                    return;
                }

                formData.append('product_id', productId);

                try {
                    const response = await fetch('add_to_cart.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        cartCountElement.textContent = result.total_items;
                        alert(result.message);
                    } else {
                        alert(result.message || 'Ошибка при добавлении товара');
                    }
                } catch (error) {
                    alert('Ошибка сети или сервера');
                }
            });
        }
    });
</script>

</body>
</html>