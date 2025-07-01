<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/404.css">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <title>Document</title>
</head>
<body>
<?php
require_once 'limbs/header.php';
?>
<main class="error-page">
    <div class="container error-container">
        <h1 class="error-code">404</h1>
        <p class="error-text">Кажется, что-то пошло не так</p>
        <a href="index.php" class="btn-hero">Вернуться на главную</a>
    </div>
</main>
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