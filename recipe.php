<?php
session_start();

require_once('functions.php');

if(!isset($_GET['recipeId'])) {
    infoPage("Nie istnieje taki przepis");
    exit;
}

$connection = connectDB();
$name = "";

if ($connection->connect_errno) {
    $error = 'Nie udało się połączyć z bazą danych! Spróbuj ponownie później.';
}
else {
    $sql = "SELECT * FROM cook_recipes WHERE ID_recipe = ?";
    $prep = $connection->prepare($sql);
    $prep->bind_param('i', $_GET['recipeId']);
    $prep->execute();
    $result = $prep->get_result();
    $name = $result->fetch_assoc()['name'];
        $prep->close();
    if($result->num_rows<1) {
        infoPage("Nie istnieje taki przepis");
        exit;
    }
}

$connection->close();

?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <?php echo '<title>'.$name.' - Książka kucharska</title>'?>
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

    if ($connection->connect_errno) {
        $error = 'Nie udało się połączyć z bazą danych! Spróbuj ponownie później.';
    }
    else {
        $sql = "SELECT ID_recipe, nick, cook_recipes.name, creation_date, instructions, estimated_preparation_time,categories.name AS 'cat_name' FROM cook_recipes INNER JOIN categories ON cook_recipes.ID_category=categories.ID_category WHERE cook_recipes.ID_recipe = ?";
        $prep = $connection->prepare($sql);
        $prep->bind_param('i', $_GET['recipeId']);
        $prep->execute();
        $result = $prep->get_result();
        $info = $result->fetch_assoc();
        $prep->close();

        echo '<div id="recipeHeader">';
        $path = file_exists('recipe_img/'.$info['name'].'.jpg')?'recipe_img/'.$info['name'].'.jpg':'recipe_img/default.png';
        echo '<img src="'.$path.'?='.filemtime($path).'" />';
        echo '<div id="recipeInfo" >';
        echo '<div id="recipeName" >'.$info['name'].'</div>';
        echo '<label>Autor: </label><a href=profile.php?nick='.rawurlencode($info['nick']).'>'.$info['nick'].'</a><br />';
        echo '<label>Data dodania: </label>'.substr($info['creation_date'],0,10).'<br />';
        echo '<label>Czas przygotowania: </label>'.$info['estimated_preparation_time'].' minut<br />';
        echo '<label>Kategoria: </label>'.$info['cat_name'].'<br />';
        echo '<div class="clear"></div> </div> <div class="clear"></div>';
        echo '</div>';
        echo '<div id="recipe" >';
        echo '<div id="ingredients" ><b>Składniki:</b>';

        $sql = "SELECT * FROM ingredients_quantity INNER JOIN ingredients ON ingredients_quantity.ID_ingredient=ingredients.ID_ingredient WHERE ingredients_quantity.ID_recipe = ?";
        $prep = $connection->prepare($sql);
        $prep->bind_param('i', $_GET['recipeId']);
        $prep->execute();
        $result = $prep->get_result();
        $prep->close();

        echo '<ul style="text-align: left">';
        while (($row = $result->fetch_assoc()) != null) {
            echo '<li><b>'.$row['quantity'].' '.$row['unit'].'</b> '.($row['show_name']!=''?$row['show_name']:str_replace('_',' ',$row['name'])).'</li>';
        }
        echo '</ul>';

        echo '<b>Akcje:</b>';

        echo '<a href="printPDF.php?recipeId=' . $info['ID_recipe'] . '" target="_blank"><div class="button">Wersja w PDF do wydruku</div></a> ';

        if (isset($_SESSION['login'])&&($_SESSION['login']==$info['nick']||$_SESSION['accountType']=='admin'||$_SESSION['accountType']=='moderator')) {
            echo '<a href="addPicture.php?recipeId=' . $info['ID_recipe'] . '"><div class="button">Dodaj zdjęcie do dania</div></a> ';
        }

        echo '</div>'; //ingredients
        echo '<div id="instructions"><b>Przepis:</b><br /><br />'.nl2br($info['instructions']).'</div><div class="clear"></div>';
        echo '</div>'; //recipe
    }

    $connection->close();

    ?>
</div>
</body>
</html>