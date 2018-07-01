<?php
session_start();
require_once('functions.php');


?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <title>Książka kucharska</title>
    <link href="style.css" type="text/css" rel="stylesheet" />
</head>
<body>
<?php
topRightMenu();
?>
<div class="clear"></div>
    <div id="index-content">
        <a href="index.php"><div id="title">Książka kucharska</div></a>
        <div id="search" style="margin-top: 20px; float: none">
            <form method="post" action="search.php">
                <input type="text" name="search" placeholder="Wyszukaj przepisu"/>
                <input type="image" src="img/search.png"/>
           </form>
        </div>
        <div class="bottomButtons">
            <a href="explore.php"><div style="border-left: 1px solid #a9a9a9;" class="bottomButton">Przeglądaj przepisy</div></a>
            <a href="ingredients.php"><div class="bottomButton">Wybierz składniki</div></a>
<?php
if(isset($_SESSION['logged'])) {
    echo '<a href="addRecipe.php"><div class="bottomButton">Dodaj przepis';
}
else {
    echo '<a href="login.php"><div class="bottomButton">';
    echo 'Zaloguj/Zarejestruj';
}
echo '</div></a>'
?>
        </div>
        <div class="clear"></div>
    </div>
</body>
</html>
