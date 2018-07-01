<?php

function topRightMenu($required = false) {
    if(isset($_SESSION['logged'])) {
    $avatar_src = file_exists('avatars/'.$_SESSION['login'].'.jpg')?'avatars/'.$_SESSION['login'].'.jpg':'avatars/default.jpg';
    echo '<div id="rightTopProfile"><a href="profile.php"><img src="'.$avatar_src.'" /><text>'.$_SESSION['login'].'</text></a><br/>';
    if ($_SESSION['accountType']=='moderator') echo '<a href="moderatorPanel.php"><text style="font-size: 16px;">Panel administracyjny</text></a><br />';
    echo '<a href="logout.php"><text style="font-size: 16px;">Wyloguj się</text></a></div>';
}
else {
    if(!$required) echo '<div id="rightTopProfile"><a href="login.php">Zaloguj/Zarejestruj</a></div>';
    else {
        infoPage('Musisz być zalogowanym, by przeglądać tę stronę');
        exit();
    }
}
}

function infoPage($info = "") {
    echo<<<END
<!DOCTYPE HTML>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <title>Informacja - Książka kucharska</title>
    <link href="style.css" type="text/css" rel="stylesheet" />
</head>
<body>
END;
topRightMenu();
    echo<<<END
<div id="header">
    <div class="clear"></div>
    <a href="index.php"><div id="miniTitle">Książka kucharska</div></a>
    <div id="search">
        <form method="post" action="search.php">
            <input type="text" name="search" placeholder="Wyszukaj przepisu"/>
            <input type="image" src="img/search.png"/>
        </form>
    </div>
    <div class="clear"></div>
</div>
<div id="content">
END;
    echo $info;
    echo<<<END
<br/><br/>

    <a href="index.php">Przejdź do strony głównej</a>
</div>
</body>
</html>
END;

}

function connectDB() {
    require_once('config.php');
    $connection = new mysqli(DB_HOST,DB_USER,DB_PWD,DB_NAME);
    $connection->set_charset("utf8");
    return $connection;
}