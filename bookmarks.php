<?php
session_start();

if (empty($_SESSION['admin']) && empty($_SESSION['user'])) {
    header('location: 404.php');
    exit();
}

require_once 'connect.php';

if (!empty($_SESSION['user'])) {
    $user_id = $_SESSION['user']['user_id'];
} else {
    $user_id = $_SESSION['admin']['user_id'];
}
$mysqliUsersJanr = "SELECT * FROM `preferencesUsers` WHERE user_id = '$user_id'";
$resultUsersJanr = $connect->query($mysqliUsersJanr)->fetch_all(MYSQLI_ASSOC);
$randomResults = [];

if (!empty($_SESSION['user'])) {
    if (!empty($resultUsersJanr)) {
        $stmt = $connect->prepare("
        SELECT 
            b.*,
            a.name AS author_name,
            COUNT(p.janr_id) AS match_count
        FROM 
            books b
        JOIN book_janr bj ON b.book_id = bj.book_id
        JOIN preferencesUsers p ON bj.janr_id = p.janr_id AND p.user_id = ?
        JOIN authors a ON b.author_id = a.author_id
        GROUP BY b.book_id
        ORDER BY match_count DESC
        LIMIT 7;
    ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $randomResults = $result->fetch_all(MYSQLI_ASSOC);
    }else{
        $mysqli = "SELECT * 
               FROM books 
               JOIN authors ON books.author_id = authors.author_id
               ORDER BY RAND()
               LIMIT 8";
        $result = $connect->query($mysqli);
        $randomResults = $result->fetch_all(MYSQLI_ASSOC);
    }
} else {
    $mysqli = "SELECT * 
               FROM books 
               JOIN authors ON books.author_id = authors.author_id
               ORDER BY RAND()
               LIMIT 8";
    $result = $connect->query($mysqli);
    $randomResults = $result->fetch_all(MYSQLI_ASSOC);
}
$likeMysqli = "SELECT * 
FROM bookmarks 
JOIN books ON bookmarks.book_id = books.book_id
WHERE bookmarks.user_id = '$user_id'";

$resultLikes = $connect->query($likeMysqli)->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/bookmarks.css">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <title>Document</title>
</head>
<body>
<?php
require_once 'limbs/header.php';
?>
<main class="container">
    <p class="text-top">Избранное</p>
    <section class="table">
        <div class="table-top">
            <p class="table-top-text">Название книги</p>
            <p class="table-top-text">Удалить</p>
        </div>
        <div class="column-row" id="bookmarksContainer">
            <?php if (!empty($resultLikes)) { ?>
                <?php foreach ($resultLikes as $resultLike): ?>
                    <div class="row" id="bookmark_<?=$resultLike['bookmark_id']?>">
                        <a href="book.php?id_book=<?=$resultLike['book_id']?>">
                            <p class="row-text"><?=$resultLike['title']?></p>
                        </a>
                        <a href="#" onclick="deleteBookmark(<?=$resultLike['bookmark_id']?>); return false;">
                            <img src="img/delete.png" alt="img">
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php } else { ?>
                <p class="pusto">Здесь пока пусто</p>
            <?php } ?>
        </div>
    </section>

    <!-- === Рекомендации === -->
    <section class="container new-book">
        <p>Откройте для себя новую книгу</p>
        <div class="cards-book">
            <?php foreach ($randomResults as $randomResult): ?>
                <a href="book.php?id_book=<?=$randomResult['book_id']?>">
                    <div class="card">
                        <div class="image-container">
                            <img src="img/<?=$randomResult['image']?>" alt="img">
                            <div class="image-overlay">
                                <span class="image-overlay-title"><?=$randomResult['title']?></span>
                                <span class="image-overlay-author"><?=$randomResult['author_name'] ?? $randomResult['name']?></span>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php
require_once 'limbs/footer.php';
?>
<script src="js/bookmarks.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const burger = document.getElementById('burger');
        const nav = document.getElementById('nav');

        if (!burger || !nav) {
            console.warn("Элементы бургера или меню не найдены");
            return;
        }

        burger.addEventListener('click', () => {
            nav.classList.toggle('active');
            burger.classList.toggle('active');
        });
    });
</script>
</body>
</html>