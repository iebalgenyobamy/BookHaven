<?php
session_start();
require_once 'connect.php';

$id = $_GET['id'];
$query = "DELETE
FROM books
WHERE book_id = '$id'";
$connect->query($query);
header('location: admin.php');
exit();