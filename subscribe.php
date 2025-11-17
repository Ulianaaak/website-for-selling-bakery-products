<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "bakery_db");
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка подключения к БД']);
    exit();
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

if (!$email) {
    echo json_encode(['status' => 'error', 'message' => 'Некорректный email']);
    exit();
}

$stmt = $conn->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Вы уже подписаны']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
$stmt->bind_param("s", $email);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Вы успешно подписались!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка при подписке']);
}