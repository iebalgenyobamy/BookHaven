<?php
session_start();
require_once 'connect.php';
if (empty($_SESSION['admin']) && empty($_SESSION['user'])) {
    header('location: 404.php');
}
if (empty($_SESSION['admin'])) {
    $id_user = $_SESSION['user']['user_id'];
} else {
    $id_user = $_SESSION['admin']['user_id'];
}
$mysqli = "SELECT * FROM `users` WHERE user_id = '$id_user'";
$result = $connect->query($mysqli)->fetch_assoc();

$mysqliJanrSUser = "SELECT * 
FROM preferencesUsers JOIN janr ON preferencesUsers.janr_id = janr.janr_id
WHERE user_id = '$id_user'";
$resultJanrSUser = $connect->query($mysqliJanrSUser)->fetch_all(MYSQLI_ASSOC);

$mysqliJanr = "SELECT * FROM `janr`";
$resultsJabrs = $connect->query($mysqliJanr)->fetch_all(MYSQLI_ASSOC);

$mysqliComment = "SELECT * 
FROM comments JOIN books ON comments.book_id = books.book_id
WHERE comments.user_id = '$id_user'";
$resultComments = $connect->query($mysqliComment)->fetch_all(MYSQLI_ASSOC);
$count = count($resultComments);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <title>Профиль пользователя</title>
</head>
<body>
<?php
require_once 'limbs/header.php';
?>
<main class="error-page">
    <div class="container">
        <h2>Обновить данные</h2>
        <?php if (isset($_SESSION["true"])) { ?>
            <p class="true">
                <?= $_SESSION["true"];
                unset($_SESSION["true"]); ?>
            </p>
        <?php } ?>

        <?php if (isset($_SESSION["error"])) { ?>
            <p class="error">
                <?= $_SESSION["error"];
                unset($_SESSION["error"]); ?>
            </p>
        <?php } ?>
        <form action="updateProfile.php" method="post">
            <div>
                <label for="username">Имя пользователя</label>
                <input type="text" value="<?=$result['username']?>" name="username" placeholder="Ваше имя" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" value="<?=$result['email']?>" name="email" placeholder="example@example.com" required>
            </div>
            <div>
                <label for="old-password">Старый пароль</label>
                <input type="password" id="old-password" name="old_password" placeholder="Введите старый пароль">
                <input type="hidden" value="<?=$result['password']?>" name="hided_password">
            </div>
            <div>
                <label for="new-password">Новый пароль</label>
                <input type="password" id="new-password" name="new_password" placeholder="Введите новый пароль">
            </div>
            <div>
                <label for="confirm-password">Подтвердите новый пароль</label>
                <input type="password" id="confirm-password" name="confirm_password" placeholder="Повторите новый пароль">
            </div>
            <div>
                <label for="confirm-password">Выберите любимые жанры</label>
                <select name="janrs[]" id="janrs" multiple>
                    <?php foreach ($resultsJabrs as $result): ?>
                        <option value="<?= $result['janr_id'] ?>"><?= htmlspecialchars($result['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="liked-genres">
                <h3>Уже выбранные жанры</h3>
                <div class="genre-tags">
                    <?php if (!empty($resultJanrSUser)): ?>
                        <?php foreach ($resultJanrSUser as $result): ?>
                            <a href="catalog.php?janr_id=<?= $result['janr_id'] ?>" class="genre-tag"><?= $result['title'] ?></a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-genres">Вы пока не выбрали любимые жанры</p>
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit">Сохранить изменения</button>
        </form>
    </div>
    <div class="container">
        <h2 style="margin-top: 100px;">Мои комментарии</h2>
        <?php if (!empty($resultComments)){ ?>
        <table class="comments-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Книга</th>
                <th>Комментарий</th>
                <th style="    display: flex;
    justify-content: end; margin-right: 15px;">Действия</th>
            </tr>
            </thead>
            <tbody>
                <?php foreach ($resultComments as $resultComment): ?>
                    <tr data-id="<?=$resultComment['comments_id']?>">
                        <td><?=$resultComment['comments_id']?></td>
                        <td><?=$resultComment['title']?></td>
                        <td class="comments-txt" title="<?=htmlspecialchars($resultComment['comment'])?>">
                            <?php
                            $comment = htmlspecialchars($resultComment['comment']);
                            echo mb_strlen($comment) > 85 ? mb_substr($comment, 0, 85) . '...' : $comment;
                            ?>
                        </td>
                        <td class="actions">
                            <button class="edit-btn" onclick="openModal('<?=$resultComment['comments_id']?>', '<?=htmlspecialchars($resultComment['title'], ENT_QUOTES, 'UTF-8')?>', '<?=htmlspecialchars($resultComment['comment'], ENT_QUOTES, 'UTF-8')?>')">Редактировать</button>
                            <button class="delete-btn" onclick="deleteComment(<?=$resultComment['comments_id']?>)">Удалить</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php } else { ?>
            <p class="pusto">Здесь пока пусто</p>
        <?php } ?>
        <?php if ($count > 10){?>
            <div class="pagination">

            </div>
        <?php } ?>
    </div>
    <div id="editModal" class="comments-modal">
        <div class="modal-content">
            <form action="updateComment.php" method="post">
                <span class="close" onclick="closeModal()">&times;</span>
                <input type="hidden" id="modal-id" name="id_comment">
                <p><strong>Книга:</strong> <span id="modal-book"></span></p>
                <label for="modal-comment">Комментарий:</label>
                <textarea name="comment" id="modal-comment"></textarea>
                <div class="modal-buttons">
                    <button class="cancel-btn" type="button" onclick="closeModal()">Отмена</button>
                    <button type="submit" class="save-btn">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</main>
<script>
    function deleteComment(id) {
        if (!confirm("Вы уверены, что хотите удалить этот комментарий?")) {
            return;
        }

        fetch('deleComment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + id
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Удаляем строку из таблицы
                    const row = document.querySelector(`tr[data-id='${id}']`);
                    if (row) {
                        row.remove();
                    }
                }
            });
    }
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


        document.addEventListener('click', function (e) {
        if (!nav.contains(e.target) && !burger.contains(e.target)) {
        nav.classList.remove('active');
        burger.classList.remove('active');
    }
    });
    });


    function openModal(id, book, comment) {
        const modal = document.getElementById('editModal');
        if (!modal) {
            console.error("Модальное окно не найдено!");
            return;
        }

        const modalIdInput = modal.querySelector("#modal-id");
        const modalBookSpan = modal.querySelector("#modal-book");
        const modalCommentTextarea = modal.querySelector("#modal-comment");

        if (!modalIdInput || !modalBookSpan || !modalCommentTextarea) {
            console.error("Элементы модального окна не найдены!");
            return;
        }

        modalIdInput.value = id;
        modalBookSpan.textContent = book;
        modalCommentTextarea.value = comment;

        modal.style.display = "block";
    }

    function closeModal() {
        const modal = document.getElementById('editModal');
        if (modal) modal.style.display = "none";
    }
    //пагинация
</script>
<script defer src="js/profile.js"></script>
<?php
require_once 'limbs/footer.php';
?>
</body>
</html>