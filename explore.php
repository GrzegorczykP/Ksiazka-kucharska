<?php
session_start();
require_once('functions.php');

if(!isset($_SESSION['prev'])) $_SESSION['prev']='';

if(isset($_GET['order'])) {
    switch ($_GET['order']) {
        case 'name':
            $order = 'name';
            break;
        case 'time':
            $order = 'estimated_preparation_time';
            break;
        case 'author':
            $order = 'nick';
            break;
        case 'date':
            $order = 'creation_date';
            break;
        case 'category':
            $order = 'cat_name';
            break;
        default:
            $order = 'name';
    }
    if ($order == $_SESSION['prev']) $order = $order . ' DESC';
    $_SESSION['prev'] = $order;
}
else $order = 'name';
?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <title>Przeglądaj - Książka kucharska</title>
    <link href="style.css" type="text/css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Lato&amp;subset=latin-ext" rel="stylesheet">
</head>
<body>
<?php
topRightMenu();
?>
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
    <?php
    $connection = connectDB();
    $name = "";

    if ($connection->connect_errno) {
        $error = 'Nie udało się połączyć z bazą danych! Spróbuj ponownie później.';
    }
    else {
        $sql = "SELECT cook_recipes.ID_recipe,cook_recipes.name,cook_recipes.estimated_preparation_time,cook_recipes.nick,cook_recipes.creation_date, categories.name as 'cat_name' FROM cook_recipes INNER JOIN categories on categories.ID_category=cook_recipes.ID_category WHERE checked=1 ORDER BY ".$order;
        $prep = $connection->prepare($sql);
        $prep->execute();
        $result = $prep->get_result();

        if ($result->num_rows>0) {
            echo '<table id="recipes" cellpadding="5px" cellspacing="0px" border="1px"><th><a href="explore.php?order=name"> Nazwa dania </a></th><th><a href="explore.php?order=time"> Czas przgotowania </a></th><th><a href="explore.php?order=author">Autor</a></th><th><a href="explore.php?order=date">Data dodania</a></th><th><a href="explore.php?order=category">Kategoria</a></th>';
            for ($i = 0; ($row = $result->fetch_assoc()) != null; $i++) {
                $class = $i % 2 == 0 ? 'light' : 'normal';
                echo ' <tr class="' . $class . '"><td><a href="recipe.php?recipeId=' . $row['ID_recipe'] . '">' . $row['name'] . '</a></td><td>' . $row['estimated_preparation_time'] . ' minut</td><td><a href="profile.php?nick=' . rawurlencode($row['nick']) . '">' . $row['nick'] . '</td><td>' . substr($row['creation_date'], 0, 10) . '</td><td>' . $row['cat_name'] . '</td></tr></a>';
            }
            echo '</table>';
        }
        else
            echo 'Nie ma żadnych zakceptowanych przepisów w bazie';
    }
    ?>
</div>
</body>
</html>