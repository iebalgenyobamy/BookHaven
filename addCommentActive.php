<?php
require_once "connect.php";

header('Content-Type: application/json');

// Включаем обработку ошибок
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Получаем и проверяем данные
    $user_id = $_POST['user_id'] ?? null;
    $book_id = $_POST['book_id'] ?? null;
    $comment = trim($_POST['comment'] ?? '');
    $username = $_POST['username'] ?? 'Аноним';
    $date = date("Y-m-d");

    if (empty($user_id) || empty($book_id) || empty($comment)) {
        throw new Exception('Не все обязательные поля заполнены');
    }

    $stmt = $connect->prepare("INSERT INTO `comments`(`user_id`, `book_id`, `comment`, `date`) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $book_id, $comment, $date);
    $stmt->execute();

    echo json_encode([
        'status' => 'success',
        'message' => 'Комментарий добавлен',
        'date' => $date,
        'username' => $username
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Ошибка: ' . $e->getMessage()
    ]);
}

$stmt->close();
$connect->close();
?>