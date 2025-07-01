<?php
session_start();
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nameAuthor = $_POST['nameAuthor'];
    $imgAuthor = $_FILES['imgAuthor']['name'];
    $target_dir = "img/";
    $target_file = $target_dir . basename($_FILES["imgAuthor"]["name"]);
    $uploadOk = 1;

    if ($_FILES["imgAuthor"]["error"] !== UPLOAD_ERR_OK) {
        $_SESSION['addErrorAuthor'] = "Ошибка при загрузке файла: " . $_FILES["imgAuthor"]["error"];
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        $check = @getimagesize($_FILES["imgAuthor"]["tmp_name"]);
        if ($check === false) {
            $_SESSION['addErrorAuthor'] = "Файл не является изображением.";
            $uploadOk = 0;
        }
    }

    if ($uploadOk == 1) {
        // Проверка на существование автора в БД
        $sql = "SELECT * FROM `authors` WHERE name = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("s", $nameAuthor);
        $stmt->execute();
        $result = $stmt->get_result();
        $author = $result->fetch_assoc();

        if ($author) {
            $_SESSION['addErrorAuthor'] = "Такой автор уже добавлен";
        } else {
            if (move_uploaded_file($_FILES["imgAuthor"]["tmp_name"], $target_file)) {
                $sql = "INSERT INTO `authors`(`name`, `img`) VALUES (?, ?)";
                $stmt = $connect->prepare($sql);
                $stmt->bind_param("ss", $nameAuthor, $imgAuthor);

                if ($stmt->execute()) {
                    $_SESSION['addTrueAuthor'] = 'Автор успешно добавлен!';
                } else {
                    $_SESSION['addErrorAuthor'] = "Ошибка при добавлении автора в БД: " . $stmt->error;
                    unlink($target_file);
                }
            } else {
                $_SESSION['addErrorAuthor'] = "Ошибка при загрузке файла.";
                $uploadOk = 0;
            }
        }
    }

    header("location: admin.php");
    exit();
}
?>