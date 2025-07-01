<?php
session_start();
require_once 'connect.php';

function isAllowedExtension($filename, $allowedExtensions) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $allowedExtensions);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $opisanie = $_POST['opisanie'];
    $author_id = $_POST['author'];
    $janrs = isset($_POST['janrs']) ? $_POST['janrs'] : [];

    $sql = "SELECT * FROM `books` WHERE `title` = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("s", $title);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['addErrorBook'] = "Книга с таким названием уже существует!";
        header("location: admin.php");
        exit();
    }

    $img_target_dir = "img/";
    $pdf_target_dir = "files/pdf/";
    $epub_target_dir = "files/epub/";
    $txt_target_dir = "files/txt/";

    if (empty($_FILES['img']['name'])) {
        $_SESSION['addErrorBook'] = "Необходимо загрузить обложку книги!";
        header("location: admin.php");
        exit();
    }

    $img_name = $_FILES['img']['name'];
    $img_tmp_name = $_FILES['img']['tmp_name'];
    $img_target_file = $img_target_dir . basename($img_name);
    $img_filename = basename($img_name);

    if (isAllowedExtension($img_name, ['jpg', 'jpeg', 'png', 'gif'])) {
        if (move_uploaded_file($img_tmp_name, $img_target_file)) {
            $img_path = $img_filename;
        } else {
            $_SESSION['addErrorBook'] = "Ошибка при загрузке обложки книги.";
            header("location: admin.php");
            exit();
        }
    } else {
        $_SESSION['addErrorBook'] = "Обложка книги должна быть в формате JPG, JPEG, PNG или GIF.";
        header("location: admin.php");
        exit();
    }
    $pdf_path = null;

    if (!empty($_FILES['pdf']['name'])) {
        $pdf_name = $_FILES['pdf']['name'];
        $pdf_tmp_name = $_FILES['pdf']['tmp_name'];
        $pdf_target_file = $pdf_target_dir . basename($pdf_name);
        $pdf_filename = basename($pdf_name);

        if (isAllowedExtension($pdf_name, ['pdf'])) {
            if (move_uploaded_file($pdf_tmp_name, $pdf_target_file)) {
                $pdf_path = $pdf_filename;
            } else {
                $_SESSION['addErrorBook'] = "Ошибка при загрузке PDF файла.";
                header("location: admin.php");
                exit();
            }
        } else {
            $_SESSION['addErrorBook'] = "PDF файл должен быть в формате PDF.";
            header("location: admin.php");
            exit();
        }
    } else {
        $_SESSION['addErrorBook'] = "Необходимо загрузить PDF файл.";
        header("location: admin.php");
        exit();
    }

    $epub_path = null;
    if (!empty($_FILES['epub']['name'])) {
        $epub_name = $_FILES['epub']['name'];
        $epub_tmp_name = $_FILES['epub']['tmp_name'];
        $epub_target_file = $epub_target_dir . basename($epub_name);
        $epub_filename = basename($epub_name);

        if (isAllowedExtension($epub_name, ['epub'])) {
            if (move_uploaded_file($epub_tmp_name, $epub_target_file)) {
                $epub_path = $epub_filename;
            } else {
                $_SESSION['addErrorBook'] = "Ошибка при загрузке EPUB файла.";
                header("location: admin.php");
                exit();
            }
        } else {
            $_SESSION['addErrorBook'] = "EPUB файл должен быть в формате EPUB.";
            header("location: admin.php");
            exit();
        }
    }
    $txt_path = null;
    if (!empty($_FILES['txt']['name'])) {
        $txt_name = $_FILES['txt']['name'];
        $txt_tmp_name = $_FILES['txt']['tmp_name'];
        $txt_target_file = $txt_target_dir . basename($txt_name);
        $txt_filename = basename($txt_name);

        if (isAllowedExtension($txt_name, ['txt'])) {
            if (move_uploaded_file($txt_tmp_name, $txt_target_file)) {
                $txt_path = $txt_filename;
            } else {
                $_SESSION['addErrorBook'] = "Ошибка при загрузке TXT файла.";
                header("location: admin.php");
                exit();
            }
        } else {
            $_SESSION['addErrorBook'] = "TXT файл должен быть в формате TXT.";
            header("location: admin.php");
            exit();
        }
    }

    $sql = "INSERT INTO `books` (`image`, `title`, `opisanie`, `author_id`, `pdf`, `epub`, `txt`) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("sssisss", $img_name, $title, $opisanie, $author_id, $pdf_name, $epub_name, $txt_name);

    if ($stmt->execute()) {
        $book_id = $connect->insert_id;
        foreach ($janrs as $janr_id) {
            $sql = "INSERT INTO `book_janr` (`janr_id`, `book_id`) VALUES (?, ?)";
            $stmt2 = $connect->prepare($sql);
            $stmt2->bind_param("ii", $janr_id, $book_id);
            $stmt2->execute();
        }

        $_SESSION['addTrueBook'] = 'Книга успешно добавлена!';
    } else {
        $_SESSION['addErrorBook'] = "Ошибка при добавлении книги в БД: " . $stmt->error;
        //  При ошибке - удаляем загруженные файлы
        if ($img_path && file_exists($img_path)) {
            unlink($img_path);
        }
        if ($pdf_path && file_exists($pdf_path)) unlink($pdf_path);
        if ($epub_path && file_exists($epub_path)) unlink($epub_path);
        if ($txt_path && file_exists($txt_path)) unlink($txt_path);
    }

    header("location: admin.php");
    exit();
} else {
    header("location: admin.php");
    exit();
}
?>