<?php
session_start();
require_once 'connect.php';

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$password_return = $_POST["password_return"];
$janrs = isset($_POST['janrs']) ? $_POST['janrs'] : [];

$hashed_password = md5($password);

$mysqli = "SELECT * FROM users WHERE email = '$email'";
$result = $connect->query($mysqli)->fetch_all(MYSQLI_ASSOC);

if ($password != $password_return) {
    $_SESSION["error"] = "Пароли не совпадают!";
    header("location: registration.php");
    exit();
} elseif (!empty($result)) {
    $_SESSION["error"] = "Данная почта уже занята!";
    header("location: registration.php");
    exit();
} elseif (strlen($password) < 8) {
    $_SESSION["error"] = "Пароль должен содержать не менее 8 символов!";
    header("location: registration.php");
    exit();
} else {
    $createUser = "INSERT INTO `users`(`username`, `email`, `password`) VALUES ('$name','$email','$hashed_password')";
    if ($connect->query($createUser)) {
        $user_id = $connect->insert_id;

        if (!empty($janrs)) {
            foreach ($janrs as $janr_id) {
                $stmt = $connect->prepare("INSERT INTO preferencesUsers (user_id, janr_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $user_id, $janr_id);
                $stmt->execute();
                $stmt->close();
            }
        }

        $_SESSION["true"] = "Регистрация прошла успешно!";
        header("location: login.php");
        exit();
    } else {
        $_SESSION["error"] = "Ошибка при регистрации.";
        header("location: registration.php");
        exit();
    }
}
?>