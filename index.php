<?php
    require_once 'connect.php';
    $randomMysqli = "SELECT *
    FROM books
    JOIN authors ON books.author_id = authors.author_id
    ORDER BY RAND()
    LIMIT 7;";
    $randomResults = $connect->query($randomMysqli)->fetch_all(MYSQLI_ASSOC);

    $popylarMysqli = "SELECT books.*, COUNT(bookmarks.book_id) AS bookmark_count, authors.*
FROM books
JOIN bookmarks ON books.book_id = bookmarks.book_id
JOIN authors ON authors.author_id = books.author_id
GROUP BY books.book_id
ORDER BY bookmark_count DESC
LIMIT 1;";
    $popylarResult = $connect->query($popylarMysqli)->fetch_assoc();

    $authorsMysqli = "SELECT * FROM `authors`";
    $resultAuthors = $connect->query($authorsMysqli)->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <title>Document</title>
</head>
<body>
<?php
require_once 'limbs/header.php';
?>
<main>
    <section class="banner">
        <div class="container banner-content">
            <h1>Добро пожаловать на <span>BookHaven</span></h1>
            <p class="banner-subtitle">Найдите свою книгу. Читайте, обсуждайте, вдохновляйтесь.</p>
            <a href="catalog.php" class="btn-hero">Просмотреть книги</a>
        </div>
    </section>
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
                        <span class="image-overlay-author"><?=$randomResult['name']?></span>
                    </div>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<section class="back-color-popular-book">
    <div class="container">
        <p class="popular-book-text">Самая популярная книга</p>
        <div class="popular-book-content">
            <img src="img/<?=$popylarResult['image']?>" alt="img">
            <div>
                <p class="popular-book-author"><?=$popylarResult['name']?></p>
                <p class="popular-book-title"><?=$popylarResult['title']?></p>
                <p class="popular-book-summary">
                    <?=$popylarResult['opisanie']?>
                </p>
                <a href="book.php?id_book=<?=$popylarResult['book_id']?>">
                    <button>
                        Узнать подробнее
                    </button>
                </a>
            </div>
        </div>
    </div>
</section>
    <section class="container">
        <p class="author-text">Наши авторы</p>
        <div class="slider-container">
            <button class="prev-button">&#10094;</button>
            <div class="slider-wrapper">
                <?php foreach ($resultAuthors as $resultAuthor): ?>
                    <div class="slide">
                        <img src="img/<?=$resultAuthor['img']?>" alt="Автор">
                        <p><?=$resultAuthor['name']?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="next-button">&#10095;</button>
        </div>
    </section>
</main>
<?php
require_once 'limbs/footer.php';
?>
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
<script defer src="js/main.js"></script>
</body>
</html>
