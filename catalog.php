<?php
session_start();
    require_once 'connect.php';
    $filtr = '';
    if (isset($_GET['janr_id'])){
        $filtr = $_GET['janr_id'];
    }
$Countmysqli = 'SELECT * FROM `books`';
$countResult = $connect->query($Countmysqli)->fetch_all(MYSQLI_ASSOC);

$lanrMysqli = "SELECT * FROM janr";
$lanrs = $connect->query($lanrMysqli);

if (isset($_GET['filtr'])){
    $filtr = $_GET['filtr'];
}
if(isset($_GET['poisk'])){
    $poisk = $_GET['poisk'];
}
if (!empty($filtr)){
    $mysqli = "SELECT * 
FROM books
JOIN book_janr ON books.book_id = book_janr.book_id
JOIN authors ON books.author_id = authors.author_id 
WHERE book_janr.janr_id = '$filtr' ORDER BY books.book_id DESC";
    $books = $connect->query($mysqli)->fetch_all(MYSQLI_ASSOC);
    $count = count($books);
}elseif (!empty($poisk)){
    $mysqli = "SELECT *
FROM books
JOIN authors ON books.author_id = authors.author_id
WHERE books.title LIKE '%$poisk' OR books.title LIKE '$poisk%' OR books.title LIKE '%$poisk%' OR authors.name LIKE '%$poisk%' ORDER BY books.book_id DESC";
    $books = $connect->query($mysqli)->fetch_all(MYSQLI_ASSOC);
    $count = count($books);
}else{
    $mysqli = "SELECT *
FROM books JOIN authors ON books.author_id = authors.author_id ORDER BY books.book_id DESC";
    $books = $connect->query($mysqli)->fetch_all(MYSQLI_ASSOC);
    $count = count($books);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/catalog.css">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <title>Document</title>
</head>
<body>
<?php
require_once 'limbs/header.php';
?>
<main>
    <section class="catalog-top container">
        <p class="main-text">Все книги</p>
        <p class="count-books"><?=$count?> книг</p>
    </section>
    <section class="sortag container">
        <div class="row-sorteg">
            <form action="catalog.php" method="get">
                <label for="#">Фильтр</label>
                <div class="select-btn">
                    <select name="filtr">
                        <option value="">Все</option>
                        <?php foreach ($lanrs as $lanr): ?>
                            <option value="<?=$lanr['janr_id']?>" <?= $filtr === $lanr['janr_id'] ? 'selected' : ''?>><?=$lanr['title']?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="form-btn" type="submit">Фильтровать</button>
                </div>
            </form>
            <form action="catalog.php" method="get">
                <label for="#">Поиск</label>
                <div class="select-btn">
                    <input class="poisk" type="text" name="poisk" placeholder="Найдите книгу..">
                    <button class="form-btn" type="submit">Найти</button>
                </div>
            </form>
        </div>
    </section>
    <section class="cards container">
        <?php if ($count > 0){?>
            <?php foreach ($books as $book): ?>
                <?php
                $book_id = $book['book_id'];
                if (!empty($_SESSION['user']) || !empty($_SESSION['admin'])) {
                    if (!empty($_SESSION['user'])){
                        $id_user = $_SESSION['user']['user_id'];
                    }else{
                        $id_user = $_SESSION['admin']['user_id'];
                    }
                    $mysqlitLike = "SELECT * FROM 
                    bookmarks WHERE user_id = '$id_user' AND book_id = '$book_id'";
                    $resultLike = $connect->query($mysqlitLike)->fetch_assoc();
                }
                ?>
                <div class="card" style="margin-bottom: 15px">
                    <div class="container-img">
                    <img class="card-main-img" src="img/<?=$book['image']?>" alt="img">
                    </div>
                    <div class="card-content">
                        <div class="card-content-jc">
                            <div class="card-content-top">
                                <p class="card-content-top-text"><?=$book['title']?></p>
                                <?php
                                if (isset($_SESSION['user']) || isset($_SESSION['admin'])) { ?>
                                    <?php if (!empty($resultLike)) { ?>
                                        <a href="#" id="deleteLikeButton" data-book-id="<?=$book_id?>" data-user-id="<?=$id_user?>" onclick="toggleLike(this); return false;">
                                            <img src="img/likeTrue.png" alt="img" class="like">
                                        </a>
                                    <?php } else { ?>
                                        <a href="#" id="deleteLikeButton" data-book-id="<?=$book_id?>" data-user-id="<?=$id_user?>" onclick="toggleLike(this); return false;">
                                            <img src="img/likeFalse.png" alt="img" class="like">
                                        </a>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                            <p class="auter-text"><?=$book['name']?></p>
                            <?php
                            $opisanie = $book['opisanie'];
                            $max_length = 350;

                            if (mb_strlen($opisanie, 'UTF-8') > $max_length) {
                                $opisanie = mb_substr($opisanie, 0, $max_length, 'UTF-8') . "...";
                            }
                            ?>
                            <p class="card-content-mid-text"><?=$opisanie?></p>
                        </div>
                        <div class="row-btn">
                            <a href="book.php?id_book=<?=$book['book_id']?>"  class="card-content-btn">Подробнее</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php }else{?>
            <p class="pysto">Такой книги нет</p>
        <?php } ?>
    </section>
    <?php if ($count > 5){?>
        <div class="pagination container">
        </div>
    <?php } ?>

</main>
<?php
require_once 'limbs/footer.php';
?>
<script src="js/catalog.js"></script>
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