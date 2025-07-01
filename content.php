<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';
require_once 'connect.php';

$book_id = $_GET['book_id'];
$mysqli = "SELECT * FROM books JOIN authors ON books.author_id = authors.author_id WHERE books.book_id = '$book_id'";
$result = $connect->query($mysqli)->fetch_assoc();
$title = $result['title'];
$author = $result['name'];
$pdf = $result['pdf'];
$pdf_file_path = 'files/pdf/' . $pdf;
if (empty($book_id)){
    header('location: 404.php');
}
class PdfParser {
    private $pdfPath;
    public function __construct($pdfPath) {
        $this->pdfPath = $pdfPath;
    }
    public function extractText() {
        $text = '';
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($this->pdfPath);
            $text = $pdf->getText();

        } catch (Exception $e) {
            $errorMessage = "Error extracting text from file: " . $this->pdfPath . ". Message: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
            error_log($errorMessage);
            return "";
        }

        return $text;
    }
}

try {
    $pdfParser = new PdfParser($pdf_file_path);
    $text = $pdfParser->extractText();
    $author = 'Неизвестный автор';

    function formatChapter($chapterText) {
        $html = "<div class='chapter'>";
        $chapterText = trim($chapterText);

        $chapterText = preg_replace("/\r\n|\r|\n/", "\n", $chapterText);
        $chapterText = preg_replace("/(\n\s*){2,}/", "\n\n", $chapterText);

        $paragraphs = explode("\n\n", $chapterText);

        foreach ($paragraphs as $para) {
            $para = trim($para);
            if (empty($para)) continue;
            $sentences = preg_split('/(?<=[.!?;])\s+(?=(?:[^\"]*\"[^\"]*\")*[^\"]*$)/u', $para, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($sentences as $sentence) {
                $trimmed = trim($sentence);

                if (preg_match('/^[\s“”«»"\'—–]–\s/u', $trimmed)) {
                    $html .= "<p class=\"dialog\">" . htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8') . "</p>";
                } else {
                    $html .= "<p>" . htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8') . "</p>";
                }
            }
        }

        $html .= "</div>";
        return $html;
    }
    function splitIntoPages($text, $charsPerPage = 9000) {
        $pages = [];
        $start = 0;

        while ($start < strlen($text)) {
            $end = min($start + $charsPerPage, strlen($text));
            if ($end < strlen($text)) {
                $lastDot = strrpos(substr($text, $start, $end - $start), '.');
                $lastBreak = strrpos(substr($text, $start, $end - $start), "\n\n");
                $lastSpace = strrpos(substr($text, $start, $end - $start), ' ');

                $end = $lastDot !== false ? $start + $lastDot + 1 : (
                $lastBreak !== false ? $start + $lastBreak + 2 : (
                $lastSpace !== false ? $start + $lastSpace : $end
                )
                );
            }
            $pageText = substr($text, $start, $end - $start);
            if (strlen(preg_replace('/\s+/', '', strip_tags(trim($pageText)))) > 10) {
                $pages[] = $pageText;
            }
            $start = $end;
        }
        return $pages;
    }

    $pages = splitIntoPages($text);
    $pagesHtml = array_map(function($page) {
        return "<div class='page'>" . formatChapter($page) . "</div>";
    }, $pages);
    $pages_json = json_encode($pagesHtml, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log("Ошибка парсинга PDF: " . $e->getMessage());
    $pages_json = json_encode([]);
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/content.css">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <title><?=$title?></title>
</head>
<body data-book-id="<?=$book_id?>">

<header class="header">
    <div class="container header__inner">
        <a class="logo" href="index.php">
            <img class="img_logo" src="img/vecteezy_open-book-open-book-png-open-book-icon-open-book-clipart_29285212%201.png" alt="Логотип">
            <p class="txt_logo">BookHaven</p>
        </a>

        <button class="burger" id="burger">
            <span></span><span></span><span></span>
        </button>

        <nav class="nav" id="nav">
            <a href="index.php">Главная</a>
            <a href="catalog.php">Книги</a>
            <?php if (!empty($_SESSION['user']) || !empty($_SESSION['admin'])){?>
                <a href="bookmarks.php">Избранное</a>
                <a href="profile.php">Профиль</a>
            <?php } ?>
            <?php if (!empty($_SESSION['admin'])){?>
                <a href="admin.php">Админка</a>
            <?php } ?>
            <?php if (!empty($_SESSION['user']) || !empty($_SESSION['admin'])){?>
                <a href="logout.php" class="btn btn--outline">Выход</a>
            <?php } else { ?>
                <a href="login.php" class="btn btn--outline">Вход</a>
                <a href="registration.php" class="btn btn--primary">Регистрация</a>
            <?php } ?>
        </nav>
    </div>
</header>
<main class="container">
    <div id="chat-widget">
        <div id="chat-header">
            <h3>Чат с ИИ</h3>
            <button id="toggle-chat">Свернуть</button>
        </div>
        <div id="chat-container">
            <div id="chat-history">
            </div>
            <div class="input-area">
                <input type="text" id="userInput" placeholder="Введите сообщение..." autocomplete="off">
                <button class="chat-btn" onclick="sendMessage()">Отправить</button>
            </div>
            <div id="response"></div>
        </div>
    </div>
    <div class="controls">
        <div>
            <label for="font-size">Размер шрифта:</label>
            <select id="font-size">
                <option value="14px">14px</option>
                <option value="15px">15px</option>
                <option value="16px">16px</option>
                <option value="17px">17px</option>
                <option value="18px" selected>18px</option>
                <option value="19px">19px</option>
                <option value="20px">20px</option>
            </select>
        </div>
        <div>
            <label for="font-weight">Толщина шрифта:</label>
            <select id="font-weight">
                <option value="lighter">Lighter</option>
                <option value="normal" selected>Normal</option>
                <option value="bold">Bold</option>
            </select>
        </div>
        <div>
            <label for="line-height">Межстрочный интервал:</label>
            <select id="line-height">
                <option value="0.8">0.8</option>
                <option value="1.0">1.0</option>
                <option value="1.2">1.2</option>
                <option value="1.4" selected>1.4</option>
                <option value="1.6">1.6</option>
                <option value="1.8">1.8</option>
                <option value="2.0">2.0</option>
            </select>
        </div>
        <button id="reset-styles">Очистить</button>
    </div>
    <div class="book-container" id="book-container">
        <div>
            <p class="content" id="book-viewer"></p>
        </div>
        <div class="pagination container">
        </div>
    </div>
</main>
<footer class="footer">
    <div class="container">
        <div class="footer-brand">
            <a href="index.php" class="footer-logo">
                <img src="img/vecteezy_open-book-open-book-png-open-book-icon-open-book-clipart_29285212%201.png" alt="BookHaven Logo">
                <span class="footer-title">BookHaven</span>
            </a>
            <p class="footer-description">
                Ваш личный уголок для чтения, обсуждений и открытий.
            </p>
        </div>
        <nav class="footer-nav">
            <h4>Навигация</h4>
            <ul>
                <li><a href="index.php">Главная</a></li>
                <li><a href="catalog.php">Книги</a></li>
                <?php if (!empty($_SESSION['user']) || !empty($_SESSION['admin'])){?>
                    <li><a href="bookmarks.php">Избранное</a></li>
                    <li><a href="profile.php">Профиль</a></li>
                <?php } ?>
                <?php if (!empty($_SESSION['admin'])){?>
                    <li><a href="admin.php">Админка</a></li>
                <?php } ?>
            </ul>
        </nav>
        <div class="footer-actions">
            <?php if (!empty($_SESSION['user']) || !empty($_SESSION['admin'])){?>
                <h4>Уже уходите?</h4>
                <a href="logout.php" class="btn">Выйти</a>
            <?php } else { ?>
                <h4>Присоединяйтесь</h4>
                <a href="login.php" class="btn">Вход</a>
                <a href="registration.php" class="btn btn--primary">Регистрация</a>
            <?php } ?>
        </div>
    </div>
    <div class="footer-bottom container">
        <p>&copy; 2025 BookHaven. Все права защищены.</p>
    </div>
</footer>
<script>
    const pages = <?php echo $pages_json; ?>;
    let currentPage = 0;
    const bookViewer = document.getElementById('book-viewer');
    const paginationContainer = document.querySelector('.pagination');

    function displayPage(index) {
        if (index >= 0 && index < pages.length) {
            bookViewer.innerHTML = pages[index];
        }
    }

    function createPaginationLinks() {
        paginationContainer.innerHTML = '';

        const maxLinks = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxLinks / 2));
        let endPage = Math.min(pages.length, startPage + maxLinks - 1);

        if (startPage > 1) {
            createLink(1, 0); // First page
            if (startPage > 2) {
                createDots();
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            createLink(i, i - 1, i - 1 === currentPage);
        }

        if (endPage < pages.length) {
            if (endPage < pages.length - 1) {
                createDots();
            }
            createLink(pages.length, pages.length - 1); // Last page
        }
    }

    function createLink(text, pageIndex, isActive = false) {
        const link = document.createElement('a');
        link.href = '#';
        link.textContent = text;
        link.addEventListener('click', (event) => {
            event.preventDefault();
            currentPage = pageIndex;
            displayPage(currentPage);
            updatePagination();
        });
        if (isActive) {
            link.classList.add('active');
        }
        paginationContainer.appendChild(link);
    }

    function createDots() {
        const dots = document.createElement('span');
        dots.textContent = '...';
        paginationContainer.appendChild(dots);
    }

    function updatePagination() {
        createPaginationLinks();
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (pages.length > 0) {
            displayPage(0);
            updatePagination();
        } else {
            bookViewer.innerHTML = "<p>Ошибка загрузки книги.</p>";
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
        const fontSizeSelect = document.getElementById('font-size');
        const fontWeightSelect = document.getElementById('font-weight');
        const lineHeightSelect = document.getElementById('line-height');
        const bookViewer = document.getElementById('book-viewer');
        const resetButton = document.getElementById('reset-styles');

        const defaultFontSize = '18px';
        const defaultFontWeight = 'normal';
        const defaultLineHeight = '1.4';
        const defaultColor = '#444';

        function applyStyles() {
            bookViewer.style.fontSize = fontSizeSelect.value;
            bookViewer.style.fontWeight = fontWeightSelect.value;
            bookViewer.style.lineHeight = lineHeightSelect.value;
            bookViewer.style.color = defaultColor;
        }

        function resetStyles() {
            fontSizeSelect.value = defaultFontSize;
            fontWeightSelect.value = defaultFontWeight;
            lineHeightSelect.value = defaultLineHeight;
            applyStyles();
        }

        bookViewer.style.fontSize = defaultFontSize;
        bookViewer.style.fontWeight = defaultFontWeight;
        bookViewer.style.lineHeight = defaultLineHeight;
        bookViewer.style.color = defaultColor;

        fontSizeSelect.addEventListener('change', applyStyles);
        fontWeightSelect.addEventListener('change', applyStyles);
        lineHeightSelect.addEventListener('change', applyStyles);

        resetButton.addEventListener('click', resetStyles);
    });


    // 1. Переменная для хранения истории сообщений
    const chatContainer = document.getElementById('chat-container');
    const toggleButton = document.getElementById('toggle-chat');

    toggleButton.addEventListener('click', function () {
        if (chatContainer.classList.contains('hidden')) {
            chatContainer.classList.remove('hidden');
            toggleButton.textContent = 'Свернуть';
        } else {
            chatContainer.classList.add('hidden');
            toggleButton.textContent = 'Развернуть';
        }
    });
    let chatHistory = [];
    let systemPromptSent = false;

    const bookTitle = "<?= addslashes(htmlspecialchars($title ?? '', ENT_QUOTES, 'UTF-8')) ?>";
    const bookAuthor = "<?= addslashes(htmlspecialchars($author ?? 'Неизвестный автор', ENT_QUOTES, 'UTF-8')) ?>";
    const chatHistoryDiv = document.getElementById('chat-history');
    const responseDiv = document.getElementById('response');

    async function sendMessage() {
        const input = document.getElementById('userInput').value;

        if (!input) {
            responseDiv.innerHTML = 'Пожалуйста, введите сообщение.';
            return;
        }

        if (!systemPromptSent) {
            const systemPrompt = `Пользователь сейчас читает книгу "${bookTitle}" (${bookAuthor}).
Это начало его взаимодействия с книгой. Подготовься к обсуждению сюжета, персонажей, тем и анализу текста.`;

            chatHistory.push({
                role: 'system',
                content: systemPrompt
            });

            systemPromptSent = true;
        }

        chatHistory.push({ role: 'user', content: input });
        displayMessage('user', input);

        responseDiv.innerHTML = 'Обработка...';

        try {
            const response = await fetch('https://openrouter.ai/api/v1/chat/completions',  {
                method: 'POST',
                headers: {
                    Authorization: 'Bearer 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',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    model: 'qwen/qwen2.5-vl-72b-instruct:free',
                    messages: chatHistory,
                })
            });

            const data = await response.json();
            const reply = data.choices?.[0]?.message?.content || 'Ответ не получен.';

            // Добавляем ответ ИИ в историю
            chatHistory.push({ role: 'assistant', content: reply });
            displayMessage('assistant', reply);
            responseDiv.innerHTML = '';

        } catch (error) {
            responseDiv.innerHTML = 'Ошибка: ' + error.message;
        }

        document.getElementById('userInput').value = '';
    }

    function displayMessage(role, content) {
        const messageDiv = document.createElement('p');
        messageDiv.textContent = content;
        messageDiv.classList.add(role);
        chatHistoryDiv.appendChild(messageDiv);
        chatHistoryDiv.scrollTop = chatHistoryDiv.scrollHeight;
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
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

</body>
</html>