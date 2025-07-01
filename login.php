<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/login.css">
    <title>Document</title>
</head>
<body>
<?php
require_once 'limbs/header.php';
?>
<main>
    <section>
        <form action="loginActive.php" method="post">
            <div class="form-img">
                <p class="form-img-text">Вход</p>
            </div>
            <label class="label-email" for="email">Email</label>
            <input type="email" name="email" id="email" autocomplete="new-password">

            <label class="label-password" for="password">Пароль</label>
            <input type="password" name="password" id="password" autocomplete="new-password">

            <button type="submit">Войти</button>
            <a class="linc-registr" href="registration.php">Регистрация</a>
            <?php if (isset($_SESSION["true"])){?>
                <p class="true">
                    <?php echo $_SESSION["true"];
                    unset($_SESSION["true"]);
                    ?>
                </p>
            <?php } ?>
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
<script defer src="js/login.js"></script>
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
<?php
require_once 'limbs/footer.php';
?>
</body>
</html>