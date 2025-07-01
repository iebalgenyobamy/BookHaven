<?php
session_start();
require_once 'connect.php';
$book_id = $_GET['id_book'];
if (isset($_SESSION['user'])) {
    $id_user = $_SESSION['user']['user_id'];
    $username = $_SESSION['user']['username'];
    $date = date("Y-m-d");

    $mysqliBookMark = "SELECT * 
    FROM bookmarks 
    WHERE user_id = '$id_user' AND book_id = '$book_id'";
    $resultBookMark = $connect->query($mysqliBookMark)->fetch_assoc();
}elseif(isset($_SESSION['admin'])){
    $id_user = $_SESSION['admin']['user_id'];
    $username = $_SESSION['admin']['username'];
    $date = date("Y-m-d");

    $mysqliBookMark = "SELECT * 
    FROM bookmarks 
    WHERE user_id = '$id_user' AND book_id = '$book_id'";
    $resultBookMark = $connect->query($mysqliBookMark)->fetch_assoc();
}

$mysqliBook = "SELECT *
FROM books JOIN authors ON books.author_id = authors.author_id
WHERE books.book_id = '$book_id'";
$resultBook = $connect->query($mysqliBook)->fetch_assoc();


?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/book.css">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <title>Document</title>
</head>
<body>
<?php
require_once 'limbs/header.php';
?>
<main>
    <section class="book container">
        <img src="img/<?=$resultBook['image']?>" alt="img" class="book-img">
        <div class="book-content">
            <div class="book-content-top">
                <div class="book-content-row-title-liked">
                    <p class="auter"><?=$resultBook['name']?></p>
                    <div class="like_download">
                        <?php if (!empty($resultBook['pdf']) || !empty($resultBook['epub']) || !empty($resultBook['txt'])){ ?>
                            <a href="#" id="download-button">
                                <img src="img/icons8-download-64%20(1).png" alt="Download" class="download">
                            </a>
                            <div id="download-modal" class="modal">
                                <div class="modal-content">
                                    <span class="close">&times;</span>
                                    <p>Выберите формат для скачивания:</p>
                                    <?php if (!empty($resultBook['pdf'])){ ?>
                                        <div class="download-item">
                                            <a href="files/pdf/<?=$resultBook['pdf']?>" download="<?=$resultBook['pdf']?>">
                                                <img src="img/icons8-pdf-48.png" alt="PDF" class="download-option">
                                            </a>
                                            <a href="files/pdf/<?=$resultBook['pdf']?>" download="<?=$resultBook['pdf']?>"><span>PDF</span></a>
                                        </div>
                                    <?php } ?>
                                    <?php if (!empty($resultBook['epub'])){ ?>
                                        <div class="download-item">
                                            <a href="files/epub/<?=$resultBook['epub']?>" download="<?=$resultBook['epub']?>">
                                                <img src="img/icons8-epub-64.png" alt="EPUB" class="download-option">
                                            </a>
                                            <a href="files/epub/<?=$resultBook['epub']?>" download="<?=$resultBook['epub']?>"><span>EPUB</span></a>
                                        </div>
                                    <?php } ?>
                                    <?php if (!empty($resultBook['txt'])){ ?>
                                        <div class="download-item">
                                            <a href="files/txt/<?=$resultBook['txt']?>" download="<?=$resultBook['txt']?>">
                                                <img src="img/icons8-txt-64.png" alt="txt" class="download-option">
                                            </a>

                                            <a href="files/txt/<?=$resultBook['txt']?>" download="<?=$resultBook['txt']?>"><span>TXT</span></a>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if (isset($_SESSION['user']) || isset($_SESSION['admin'])){
                            if (!empty($resultBookMark)){?>
                                <a href="#" id="deleteLikeButton" data-book-id="<?=$book_id?>" data-user-id="<?=$id_user?>" onclick="toggleLike(this); return false;">
                                    <img src="img/likeTrue.png" alt="img" class="like">
                                </a>
                            <?php }else{ ?>
                                <a href="#" id="deleteLikeButton" data-book-id="<?=$book_id?>" data-user-id="<?=$id_user?>" onclick="toggleLike(this); return false;">
                                    <img src="img/likeFalse.png" alt="img" class="like">
                                </a>
                            <?php } ?>
                        <?php }; ?>
                    </div>
                </div>
                <p class="title-text"><?=$resultBook['title']?></p>
                <p class="book-content-main-text">
                    <?=$resultBook['opisanie']?>
                </p>
            </div>
            <a href="content.php?book_id=<?=$resultBook['book_id']?>" class="book-btn">Читать</a>
        </div>
    </section>
    <section class="categorys container">
        <?php
        $mysqliJanr = "SELECT * 
            FROM book_janr 
            JOIN janr ON book_janr.janr_id = janr.janr_id
            WHERE book_janr.book_id = '$book_id'";
        $resultJanrs = $connect->query($mysqliJanr)->fetch_all(MYSQLI_ASSOC);
        ?>
        <p class="categorys-text">Жанры</p>

        <div class="categorys-cards">
            <?php foreach ($resultJanrs as $resultJanr): ?>
                <a href="catalog.php?janr_id=<?= $resultJanr['janr_id'] ?>" class="categorys-card">
                    <?= htmlspecialchars($resultJanr['title']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="comments container">
        <p class="comment-text">Отзывы</p>
        <?php if (!empty($_SESSION['user']) || !empty($_SESSION['admin'])){?>
            <form class="comments-form" id="commentForm" method="post" onsubmit="addComment(this); return false;">
                <input type="text" placeholder="Введите ваш отзыв.." name="comment" required>
                <input type="hidden" value="<?=$id_user?>" name="user_id">
                <input type="hidden" value="<?=$book_id?>" name="book_id">
                <input type="hidden" value="<?=$username?>" name="username">
                <input type="hidden" value="<?=$date?>" name="date">
                <button><img src="img/samalet.png" alt="img"></button>
            </form>
        <?php } ?>
        <div class="comments-cards" id="commentsContainer">
            <?php
            $mysqliComments = "SELECT comments.*, users.username
            FROM comments
            JOIN users ON comments.user_id = users.user_id
            WHERE comments.book_id = '$book_id'
            ORDER BY comments.date DESC";
            $resultComments = $connect->query($mysqliComments)->fetch_all(MYSQLI_ASSOC);
            $countComments = count($resultComments);
            ?>
            <?php if ($countComments != 0){ ?>
                <?php
                $visibleCount = 0;
                foreach ($resultComments as $key => $resultComment):
                    $style = ($key > 2) ? 'style="display: none;"' : '';
                    ?>
                    <div class="comments-card comment-card" style="<?=$style?>">
                        <p class="comments-card-name"><?=$resultComment['username']?></p>
                        <hr>
                        <p class="comments-card-content"><?=$resultComment['comment']?></p>
                        <p class="comments-card-date"><?=$resultComment['date']?></p>
                    </div>
                <?php endforeach; ?>
            <?php }else{  ?>
                <p class="pusto">ЗДЕСЬ ПОКА ПУСТО</p>
            <?php } ?>
        </div>
        <?php if ($countComments > 3){?>
            <a href="#" class="comments-more" id="showMoreComments" onclick="toggleComments(this); return false;">еще <?=$countComments - 3 ?> отзыва</a>
        <?php } ?>
    </section>

</main>
<?php
require_once 'limbs/footer.php';
?>
<script defer src="js/book.js"></script>
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