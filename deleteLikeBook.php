<?php
require_once 'connect.php';
$book_id = $_GET['book_id'];
$user_id = $_GET['user_id'];

$query = "DELETE
FROM bookmarks
WHERE user_id = '$user_id' AND book_id = '$book_id'";
$connect->query($query);
