<?php
session_start();
require_once "connect.php";

// ❌ Нельзя редактировать данные админа
if (!empty($_SESSION['admin'])) {
    $_SESSION['error'] = "Нельзя изменить данные администратора";
    header("location: profile.php");
    exit();
}

// ✅ Получаем ID пользователя из сессии
if (empty($_SESSION['user'])) {
    $_SESSION['error'] = "Вы не авторизованы";
    header("location: login.php");
    exit();
}

$user_id = $_SESSION['user']['user_id'];
$username = $connect->real_escape_string($_POST['username']);
$email = $connect->real_escape_string($_POST['email']);
$hided_password = $connect->real_escape_string($_POST['hided_password']);
$old_password = $connect->real_escape_string($_POST['old_password']);
$new_password = $connect->real_escape_string($_POST['new_password']);
$confirm_password = $connect->real_escape_string($_POST['confirm_password']);
$janrs = isset($_POST['janrs']) ? array_map('intval', $_POST['janrs']) : [];

$mysqli = "SELECT * FROM `users` WHERE user_id = '$user_id'";
$result = $connect->query($mysqli)->fetch_assoc();

if (empty($old_password)) {
    $update_sql = "UPDATE `users` SET username = '$username', email = '$email' WHERE user_id = '$user_id'";
    $connect->query($update_sql);
    if (!empty($janrs)) {
        $connect->query("DELETE FROM `preferencesUsers` WHERE user_id = '$user_id'");
        foreach ($janrs as $janr_id) {
            $stmt = $connect->prepare("INSERT INTO preferencesUsers (user_id, janr_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $janr_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    $_SESSION['true'] = "Данные успешно обновлены";
    header("location: profile.php");
    exit();
} elseif (md5($old_password) !== $result['password']) {
    $_SESSION['error'] = "Старый пароль неверен";
    header("location: profile.php");
    exit();
} elseif ($new_password !== $confirm_password) {
    $_SESSION['error'] = "Пароли не совпадают";
    header("location: profile.php");
    exit();
} elseif (strlen($new_password) < 8) {
    $_SESSION['error'] = "Пароль должен содержать минимум 8 символов";
    header("location: profile.php");
    exit();
} else {
    $hashNewPassword = md5($new_password);
    $update_sql = "UPDATE `users` 
                   SET username = '$username', email = '$email', password = '$hashNewPassword'
                   WHERE user_id = '$user_id'";
    $connect->query($update_sql);
    if (!empty($janrs)) {
        $connect->query("DELETE FROM `preferencesUsers` WHERE user_id = '$user_id'");
        foreach ($janrs as $janr_id) {
            $stmt = $connect->prepare("INSERT INTO preferencesUsers (user_id, janr_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $janr_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    $_SESSION['true'] = "Данные и пароль успешно обновлены";
    header("location: profile.php");
    exit();
}
?>