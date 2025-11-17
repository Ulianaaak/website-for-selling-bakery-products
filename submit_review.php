<?php
session_start();

$conn = new mysqli("localhost", "root", "", "bakery_db");

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка подключения к БД']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Требуется авторизация']);
    exit();
}

$product_id = intval($_POST['product_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if (!$product_id || !$rating || $rating < 1 || $rating > 5) {
    echo json_encode(['status' => 'error', 'message' => 'Некорректные данные']);
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    INSERT INTO reviews (user_id, product_id, rating, comment)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("iiis", $user_id, $product_id, $rating, $comment);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Отзыв успешно добавлен'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Не удалось сохранить отзыв'
    ]);
}
?>