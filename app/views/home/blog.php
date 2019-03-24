<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Персональный сайт Герцовской Карины. Гостевая книга.</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <script src="/script/onMenuHoverBackgr.js"></script>
    <script src="/script/sessionStorageHistory.js"></script>
    <script src="/script/globalHistory.js"></script>
</head>
<body onload="sessionStorageHistory('Блог'); setCookie('Блог')">
<div class="wrapper">
    <div class="content">
        <div class="user-banner">
            <div class="currentTime" id="currentTime"></div>
            <div class="user-info">
                <?php
                echo $args["userInfo"];
                ?>
            </div>
        </div>
        <a name="top"></a>
        <header>
            <h1>Вы попали на персональный сайт <br> Герцовской Карины Владиславовны</h1>
        </header>
        <nav>
            <ul class="mainMenu">
                <li><a id="MainPage" href="/">Главная</a></li>
                <li><a id="AboutMe" href="/aboutMe" onmouseover="setBackground('AboutMe')" onmouseout="restore('AboutMe')">Обо мне</a></li>
                <li><a onclick = "showList()" id="myInterests" href="#" onmouseover="setBackground('myInterests')" onmouseout="restore('myInterests')">Мои интересы</a></li>
                <li><a id="Photoalbum" href="/photoalbum" onmouseover="setBackground('Photoalbum')" onmouseout="restore('Photoalbum')">Мой фотоальбом</a></li>
                <li><a id="Education" href="/education" onmouseover="setBackground('Education')" onmouseout="restore('Education')">Образование</a></li>
                <li><a id="Connection" href="/contacts" onmouseover="setBackground('Connection')" onmouseout="restore('Connection')">Связь со мной</a></li>
                <li><a id="Test" href="/test" onmouseover="setBackground('Test')" onmouseout="restore('Test')">Входное тестирование</a></li>
                <li><a id="Guest" href="/guest_book" onmouseover="setBackground('Guest')" onmouseout="restore('Guest_book')">Гостевая книга</a></li>
                <li class="active"><a id="Blog" href="/blog" onmouseover="setBackground('Blog')" onmouseout="restore('Blog')">Блог</a></li>
                <?php
                if($args["isAdmin"]) {
                    echo '<li><a href="/admin">Админ</a></li>';
                }
                ?>
                <li class="last"><a id="History" href="/history" onmouseover="setBackground('History')" onmouseout="restore('History')">История просмотров</a></li>
            </ul>
            <div class="mainMenu navigation" id ="inter">
                <script src="script/dropMenu.js"></script>
            </div>
        </nav>
        <section>
         <!--   <a class="loadRecordsBtn" href="/editBlog" role="button">Редактор блога</a>  -->
            <?php

        echo "<div class='pull-right'>";
        for( $i = 0; $i < $args["countPages"]; $i++ ) {
            $index = $i + 1;
            if($i == $args["page"]) {
                echo "<a class='pagesBtn' href='/blog?page=$i' role='button'>$index</a>";
            } else {
                echo "<a class='pagesBtn' href='/blog?page=$i' role='button'>$index</a>";
            }
        }

            echo "</div>";
            foreach($args["records"] as $value) {
                echo '<div class="blog-record">';
                echo '<div class="blog-topic">';
                echo $value->Topic;
                echo '</div>';
                echo '<div class="blog-content">';
                if(!empty($value->PathToPhoto)) {
                    echo '<div class="blog-photo">';
                    echo "<img class= 'img-thumbnail' src='$value->PathToPhoto' alt='$value->Topic'>";
                    echo '</div>';
                }
                echo '<div class="blog-message">';
                echo '<p>' . $value->Message . '</p>';
                echo '</div>';
                echo '<div class="clearfix"></div>';
                echo '<div class="blog-footer">';
                echo '<div class="blog-author">';
                echo $value->Author;
                echo '</div>';
                echo '<div class="blog-date">';
                echo $value->CurrentDate;
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </section>

</div>
<footer>
    <p class="footerNote1">&copy; 2019 GRIDI</p>
    <p class="footerNote2">г. Севастополь СевГУ</p>
    <p class="anchor1"><a href="#top">Наверх</a></p>
</footer>
</div>
<script src="/script/jquery-3.3.1.min.js"></script>
<script src="/script/currentTime.js"></script>
</body>
</html>