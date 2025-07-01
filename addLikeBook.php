<?php
require_once 'connect.php';
$book_id = $_GET['book_id'];
$user_id = $_GET['user_id'];

$date = "INSERT INTO `bookmarks`(`user_id`, `book_id`) VALUES ('$user_id','$book_id')";
$connect->query($date);