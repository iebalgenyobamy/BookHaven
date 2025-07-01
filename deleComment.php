<?php
require_once 'connect.php';

$response = ['success' => false];

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $query = "DELETE FROM comments WHERE comments_id = ?";

    $stmt = $connect->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $response['success'] = true;
    }
}

echo json_encode($response);
exit();
?>