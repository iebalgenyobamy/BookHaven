<?php
session_start();
if (empty($_SESSION['admin'])){
    header('location: 404.php');
}
require_once 'connect.php';
$mysqliAuthors = "SELECT * FROM `authors`";
$resultsAuthors = $connect->query($mysqliAuthors)->fetch_all(MYSQLI_ASSOC);
$mysqliBook = "SELECT * 
FROM books JOIN authors ON books.author_id = authors.author_id";
$resultsBooks = $connect->query($mysqliBook)->fetch_all(MYSQLI_ASSOC);
$mysqliJanr = "SELECT * FROM `janr`";
$resultsJanrs = $connect->query($mysqliJanr)->fetch_all(MYSQLI_ASSOC);
$mysqliUser = "SELECT * FROM `users`";
$resultsUsers = $connect->query($mysqliUser)->fetch_all(MYSQLI_ASSOC);


?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <title>Document</title>
</head>
<body>
<?php require_once 'limbs/header.php'?>
<main class="container">
    <section class="forms">
        <form action="addBook.php" method="POST" enctype="multipart/form-data">
            <h1>Добавить новую книгу</h1>
            <div class="form-group">
                <label>*Название книги</label>
                <input class="input" type="text" name="title" placeholder="Введите название книги..." required>
                <label>*Описание книги</label>
                <textarea class="input" name="opisanie" placeholder="Введите описание книги..." required></textarea>
                <label>*Автор книги</label>
                <select name="author" required>
                    <?php foreach ($resultsAuthors as $result): ?>
                        <option value="<?=$result['author_id']?>"><?=$result['name']?></option>
                    <?php endforeach; ?>
                </select>
                <label>*Выберите жанры:</label>
                <select name="janrs[]" multiple required>
                    <?php foreach ($resultsJanrs as $result): ?>
                        <option value="<?=$result['janr_id']?>"><?=$result['title']?></option>
                    <?php endforeach; ?>
                </select>
                <label>*Обложка книги</label>
                <input class="input-file" type="file" name="img" required>
                <label>*Pdf формат</label>
                <input class="input-file" type="file" name="pdf" required>
                <label>Epub формат</label>
                <input class="input-file" type="file" name="epub">
                <label>Txt формат</label>
                <input class="input-file" type="file" name="txt">
            </div>
            <button class="form-btn" type="submit">Отправить</button>
            <?php if (isset($_SESSION["addTrueBook"])){ ?>
                <p class="true">
                    <?php echo $_SESSION["addTrueBook"];
                    unset($_SESSION["addTrueBook"]);
                    ?>
                </p>
            <?php } ?>
            <?php if (isset($_SESSION["addErrorBook"])){ ?>
                <p class="error">
                    <?php echo $_SESSION["addErrorBook"];
                    unset($_SESSION["addErrorBook"]);
                    ?>
                </p>
            <?php } ?>
        </form>
        <div>
            <form action="addJanr.php" method="POST">
                <h1>Добавить новый жанр</h1>
                <div class="form-group">
                    <label>*Жанр</label>
                    <input class="input" type="text" id="email" name="janrName" placeholder="Введите жанр..." required>
                </div>
                <button class="form-btn" type="submit">Отправить</button>
                <?php if (isset($_SESSION["addTrueJanr"])){ ?>
                    <p class="true">
                        <?php echo $_SESSION["addTrueJanr"];
                        unset($_SESSION["addTrueJanr"]);
                        ?>
                    </p>
                <?php } ?>
                <?php if (isset($_SESSION["addErrorJanr"])){ ?>
                    <p class="error">
                        <?php echo $_SESSION["addErrorJanr"];
                        unset($_SESSION["addErrorJanr"]);
                        ?>
                    </p>
                <?php } ?>
            </form>
            <form action="addAuthor.php" method="POST" enctype="multipart/form-data">
                <h1>Добавить автора</h1>
                <div class="form-group">
                    <label>*Инициалы</label>
                    <input class="input" type="text" id="email" name="nameAuthor" placeholder="ФИО автора..." required>
                </div>
                <div class="form-group">
                    <label>*Выберите фото</label>
                    <input class="input-file" type="file" name="imgAuthor" required>
                </div>
                <button class="form-btn" type="submit">Отправить</button>
                <?php if (isset($_SESSION["addTrueAuthor"])){ ?>
                    <p class="true">
                        <?php echo $_SESSION["addTrueAuthor"];
                        unset($_SESSION["addTrueAuthor"]);
                        ?>
                    </p>
                <?php } ?>
                <?php if (isset($_SESSION["addErrorAuthor"])){ ?>
                    <p class="error">
                        <?php echo $_SESSION["addErrorAuthor"];
                        unset($_SESSION["addErrorAuthor"]);
                        ?>
                    </p>
                <?php } ?>
            </form>
        </div>
    </section>
    <section class="tabl-users">
        <h1>Список пользователей</h1>

        <table id="users-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Имя пользователя</th>
                <th>Email</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($resultsUsers as $result): ?>
                <tr class="card">
                    <td><?= htmlspecialchars($result['user_id']) ?></td>
                    <td><?= htmlspecialchars($result['username']) ?></td>
                    <td><?= htmlspecialchars($result['email']) ?></td>
                    <td><a href="deleteUser.php?id=<?=$result['user_id']?>" class="delete-btn">Удалить</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (count($resultsUsers) > 5): ?>
            <div class="pagination container" id="pagination-container">
            </div>
        <?php endif; ?>
    </section>
    <section class="tabl-users">
        <h1>Список авторов</h1>
        <table id="users-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($resultsAuthors as $result): ?>
                <tr class="card">
                    <td><?= ($result['author_id']) ?></td>
                    <td><?= ($result['name']) ?></td>
                    <td><a href="deleteAuthor.php?id=<?=$result['author_id']?>" class="delete-btn">Удалить</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <section class="tabl-users">
        <h1>Список книг</h1>
        <table id="users-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Автор</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($resultsBooks as $result): ?>
                <tr class="card">
                    <td><?= ($result['book_id']) ?></td>
                    <td><?= ($result['title']) ?></td>
                    <td><?= ($result['name']) ?></td>
                    <td><a href="deleteBook.php?id=<?=$result['book_id']?>" class="delete-btn">Удалить</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

</main>
<?php require_once 'limbs/footer.php'?>
<script defer src="js/admin.js"></script>
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