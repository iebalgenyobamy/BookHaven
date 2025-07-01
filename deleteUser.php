<?php
session_start();
require_once 'connect.php';

$id = $_GET['id'];
$adminPssword = md5('admin100');
$query = "DELETE
FROM users
WHERE user_id = '$id'";
$mysqli = "SELECT * FROM `users` WHERE user_id = '$id'";
$result = $connect->query($mysqli)->fetch_assoc();
if ($result['email'] == 'admin@inbox.com' && $result['password'] == $adminPssword) {

}else{
    $connect->query($query);
}
header('location: admin.php');
exit();