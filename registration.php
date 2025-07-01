<?php
session_start();
require_once 'connect.php';
$mysqli = "SELECT * FROM `janr`";
$results = $connect->query($mysqli)->fetch_all(MYSQLI_ASSOC);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/registration.css">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <title>Document</title>
</head>
<body>
<?php
require_once 'limbs/header.php';
?>
<main>
    <section>
        <form action="registrationActive.php" method="post">
            <div class="form-img">
                <p class="form-img-text">Регистрация</p>
            </div>
            <label class="label-name" for="name">Имя</label>
            <input type="text" name="name" id="name" required autocomplete="new-password">

            <label class="label-email" for="email">Email</label>
            <input type="email" name="email" id="email" required autocomplete="new-password">

            <label class="label-password" for="password">Пароль</label>
            <input type="password" name="password" id="password" autocomplete="new-password">

            <label class="label-password-return" for="password_return">Повторите пароль</label>
            <input type="password" name="password_return" id="password_return" autocomplete="new-password">

            <label class="label-select" for="janrs">Выберите любимые жанры:</label>
            <select name="janrs[]" id="janrs" multiple required>
                <?php foreach ($results as $result): ?>
                    <option value="<?= $result['janr_id'] ?>"><?= htmlspecialchars($result['title']) ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Регистрация</button>
            <a class="linc-registr" href="login.php">Вход</a>
            <?php if (isset($_SESSION["error"])){?>
                <p class="error">
                    <?php echo $_SESSION["error"];
                    unset($_SESSION["error"]);
                    ?>
                </p>
            <?php } ?>
        </form>
    </section>
</main>
<?php
require_once 'limbs/footer.php';
?>
<script defer src="js/registr.js"></script>
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