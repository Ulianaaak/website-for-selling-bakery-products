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

if (isset($_GET['ajax']) && $_GET['ajax'] == 'true') {
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    $sql = "SELECT * FROM products";
    if (!empty($category)) {
        $sql .= " WHERE category = ?";
    }
    $stmt = $conn->prepare($sql);
    if (!empty($category)) {
        $stmt->bind_param("s", $category);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode($products);
    exit();
}

$sql_categories = "SELECT DISTINCT category FROM products";
$result_categories = $conn->query($sql_categories);

$sql_offers = "SELECT * FROM offers LIMIT 3";
$result_offers = $conn->query($sql_offers);

$sql_popular = "SELECT * FROM products WHERE is_popular = 1 LIMIT 4";
$result_popular = $conn->query($sql_popular);

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

<section class="hero-section">
    <div class="container">
        <h1 class="hero-title">Свежая выпечка с любовью</h1>
        <p class="hero-subtitle">Натуральные ингредиенты, традиционные рецепты и неповторимый вкус</p>
    </div>
</section>

<section class="features-section">
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="bi bi-basket"></i>
                    </div>
                    <h4>Свежие продукты</h4>
                    <p>Ежедневно свежая выпечка из натуральных ингредиентов</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h4>Быстрая доставка</h4>
                    <p>Доставка в течение 2 часов по всему городу</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="bi bi-coin"></i>
                    </div>
                    <h4>Доступные цены</h4>
                    <p>Качественная продукция по разумным ценам</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="bi bi-award"></i>
                    </div>
                    <h4>Гарантия качества</h4>
                    <p>Мы гарантируем качество всей нашей продукции</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="section-title">Специальные предложения</h2>
        <div class="row">
            <?php while ($row_offer = $result_offers->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="offer-card">
                        <div class="offer-body">
                            <span class="badge badge-discount">-<?php echo rand(10, 25); ?>%</span>
                            <h3 class="offer-title"><?php echo htmlspecialchars($row_offer['title']); ?></h3>
                            <p><?php echo htmlspecialchars($row_offer['description']); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="product-price"><?php echo number_format($row_offer['price'] * 0.8, 2); ?> ₽</span>
                                <span class="product-old-price"><?php echo number_format($row_offer['price'], 2); ?> ₽</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <h2 class="section-title">Популярные товары</h2>
        <div class="row" id="popular-products">
            <?php while ($row = $result_popular->fetch_assoc()): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($row['image_url']); ?>" class="product-img" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <div class="product-body">
                            <h5 class="product-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                            <p class="text-muted small"><?php echo htmlspecialchars($row['short_description']); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="product-price"><?php echo number_format($row['price'], 2); ?> ₽</span>
                                <button class="btn btn-sm btn-outline-primary"><i class="bi bi-cart-plus"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-4">
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="section-title">Наш ассортимент</h2>
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Категории</h5>
                        <div class="d-flex flex-wrap">
                            <button type="button" class="btn btn-outline-secondary category-filter active" data-category="">Все</button>
                            <?php
                            $result_categories->data_seek(0); // Сброс указателя результата
                            while ($row_category = $result_categories->fetch_assoc()): ?>
                                <button type="button" class="btn btn-outline-secondary category-filter" data-category="<?= htmlspecialchars($row_category['category']); ?>">
                                    <?= htmlspecialchars(ucfirst($row_category['category'])); ?>
                                </button>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="row" id="product-container">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="testimonials-section">
    <div class="container">
        <h2 class="section-title text-white">Отзывы наших клиентов</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="testimonial-card">
                    <p class="testimonial-text">"Лучшая выпечка в городе! Всегда свежая и вкусная. Особенно люблю их круассаны с миндальной начинкой."</p>
                    <p class="testimonial-author">- Анна Смирнова</p>
                    <div class="rating">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <p class="testimonial-text">"Заказываю хлеб каждую неделю. Качество неизменно высокое, а доставка всегда вовремя. Рекомендую!"</p>
                    <p class="testimonial-author">- Иван Петров</p>
                    <div class="rating">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <p class="testimonial-text">"Пироги просто восхитительные! Заказывала на день рождения, все гости были в восторге. Спасибо!"</p>
                    <p class="testimonial-author">- Елена Козлова</p>
                    <div class="rating">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-half text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="newsletter-section">
    <div class="container text-center">
        <h2 class="mb-4">Подпишитесь на наши новости</h2>
        <p class="mb-5">Получайте информацию о новых продуктах, специальных предложениях и акциях</p>
        <form id="newsletterForm" class="row g-3 justify-content-center">
            <div class="col-md-6">
                <input type="email" class="form-control newsletter-input" placeholder="Ваш email" name="email" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-newsletter w-100">Подписаться</button>
            </div>
        </form>
        <div id="newsletterResponse" class="mt-3"></div>
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
                        <li><a href="catalog.php">Каталог</a></li>
                        <li><a href="about.php">О нас</a></li>
                        <li><a href="contact.php">Контакты</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-3">
                <div class="footer-links">
                    <h5>Категории</h5>
                    <ul>
                        <?php
                        $result_categories->data_seek(0); // Сброс указателя результата
                        while ($row_category = $result_categories->fetch_assoc()): ?>
                            <li><a href="catalog.php?category=<?php echo htmlspecialchars($row_category['category']); ?>"><?php echo htmlspecialchars(ucfirst($row_category['category'])); ?></a></li>
                        <?php endwhile; ?>
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
        // Обработчики событий для кнопок "Добавить в корзину"
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.dataset.productId;
                addToCart(productId, 1);
            });
        });

        async function addToCart(productId, quantity) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            try {
                const response = await fetch('add_to_cart.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.status === 'success') {
                    alert(result.message);
                } else {
                    alert(result.message || 'Ошибка при добавлении товара');
                }
            } catch (error) {
                alert('Ошибка сети');
            }
        }
    });
</script>
<script>
    document.getElementById('newsletterForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const response = await fetch('subscribe.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            const responseDiv = document.getElementById('newsletterResponse');

            if (result.status === 'success') {
                responseDiv.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                this.reset(); // Очистить форму
            } else {
                responseDiv.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
            }
        } catch (error) {
            console.error(error);
            document.getElementById('newsletterResponse').innerHTML = '<div class="alert alert-danger">Ошибка сети</div>';
        }
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const categoryButtons = document.querySelectorAll('.category-filter');
        const productContainer = document.getElementById('product-container');

        function loadProducts(category = '') {
            fetch(`index.php?ajax=true&category=${category}`)
                .then(response => response.json())
                .then(products => {
                    productContainer.innerHTML = '';
                    if (products.length > 0) {
                        products.forEach(product => {
                            const discount = Math.random() > 0.7 ? `<span class="badge badge-discount">-${Math.floor(Math.random() * 20) + 10}%</span>` : '';
                            const oldPrice = discount ? `<span class="product-old-price">${product.price} ₽</span>` : '';
                            const displayPrice = discount ? (product.price * 0.8).toFixed(2) : product.price;
                            productContainer.innerHTML += `
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="product-card">
                                <img src="${product.image_url}" class="product-img" alt="${product.name}">
                                ${discount}
                                <div class="product-body">
                                    <h5 class="product-title">${product.name}</h5>
                                    <p class="text-muted small">${product.short_description || product.description.substring(0, 60)}...</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="product-price">${displayPrice} ₽</span>
                                            ${oldPrice}
                                        </div>
                                        <button class="btn btn-sm btn-primary add-to-cart" data-product-id="${product.id}">
                                            <i class="bi bi-cart-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <a href="product.php?id=${product.id}" class="btn btn-outline-primary w-100 mt-2">Подробнее</a>
                            </div>
                        </div>
                        `;
                        });
                    } else {
                        productContainer.innerHTML = '<div class="col-12 text-center py-5"><h4>Товары не найдены</h4><p>Попробуйте выбрать другую категорию</p></div>';
                    }
                    attachCartListeners();
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                    productContainer.innerHTML = '<div class="col-12 text-center py-5"><h4>Произошла ошибка</h4><p>Попробуйте обновить страницу</p></div>';
                });
        }

        categoryButtons.forEach(button => {
            button.addEventListener('click', function () {
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                loadProducts(this.dataset.category);
            });
        });

        loadProducts();

        function attachCartListeners() {
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', function () {
                    const productId = this.dataset.productId;
                    addToCart(productId, 1);
                });
            });
        }

        async function addToCart(productId, quantity) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            try {
                const response = await fetch('add_to_cart.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.status === 'success') {
                    updateCartCount(result.total_items);
                    alert(result.message);
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Ошибка сети при добавлении в корзину.');
            }
        }

        function updateCartCount(count) {
            document.getElementById('cart-count').textContent = count;
        }
    });
</script>
</body>
</html>