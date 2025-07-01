<?php
require_once 'connect.php';
$id_comment = $_POST['id_comment'];
$comment = $_POST['comment'];

if (empty($comment)) {
    header("Location: profile.php");
    exit();
}else{
    $date = "UPDATE `comments` 
SET comment = '$comment'
WHERE comments_id = '$id_comment'";
    $connect->query($date);
    header("location:profile.php");
    exit();
}