<?php
session_start();
require_once 'connect.php';

$email = $_POST['email'];
$password = $_POST['password'];
$hashPassword = md5($password);

$mysqli = "SELECT *
FROM users
WHERE email = '$email' AND password = '$hashPassword'";

$result = $connect->query($mysqli)->fetch_assoc();

if(!empty($result) && $result['email'] == 'admin@inbox.com' && $result['password'] == md5('admin100')){
    $_SESSION['admin'] = [
        'user_id' => $result['user_id'],
        'username' => $result['username']
    ];
    header('Location: index.php');
    exit();
}elseif(!empty($result)) {
    $_SESSION['user'] = [
        'user_id' => $result['user_id'],
        'username' => $result['username']
    ];
    header('Location: index.php');
    exit();
}else{
    $_SESSION['error'] = 'Логин или пароль неверный!';
    header('Location: login.php');
    exit();
}