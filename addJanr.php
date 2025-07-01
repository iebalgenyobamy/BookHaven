<?php
session_start();
require_once 'connect.php';
$janrName = $_POST['janrName'];

$mysqli = "SELECT * 
FROM janr 
WHERE title = '$janrName'";
$result = $connect->query($mysqli)->fetch_assoc();

if (!empty($result)) {
    $_SESSION['addErrorJanr'] = 'Такой жанр уже существует!';
    header("location:admin.php");
    exit();
}else{
    $data = "INSERT INTO `janr`(`title`) VALUES ('$janrName')";
    $connect->query($data);
    $_SESSION['addTrueJanr'] = 'Жанр успешно добавлен!';
    header("location:admin.php");
    exit();
}
