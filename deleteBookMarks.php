<?php
require_once 'connect.php';
$bookmark_id = $_GET['bookmark_id'];
$mysqli = "DELETE FROM bookmarks WHERE bookmark_id = '$bookmark_id'";
if ($connect->query($mysqli) === TRUE) {
    echo "success";
} else {
    echo "error: " . $connect->error;
}