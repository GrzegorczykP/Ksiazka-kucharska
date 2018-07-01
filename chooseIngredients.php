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

class ChooseIngredients {
    public function search($ingredient) {
        if (count($ingredient)<1) {
            echo 'Nie podano składników';
            $this->drawForm();
        } else {
            $connection = connectDB();

            $sql = "ALTER VIEW quantity_all AS SELECT cook_recipes.ID_recipe, COUNT(ingredients_quantity.ID_ingredient) AS 'quantity' FROM cook_recipes INNER JOIN ingredients_quantity ON cook_recipes.ID_recipe=ingredients_quantity.ID_recipe WHERE cook_recipes.checked=1 GROUP BY cook_recipes.ID_recipe;";
            $connection->query($sql);

            $add = strlen(str2url($ingredient[0]))>0?str2url($ingredient[0]):' ';
            $sql = "ALTER VIEW quantity_selected AS SELECT cook_recipes.ID_recipe, cook_recipes.name, cook_recipes.nick,cook_recipes.creation_date,cook_recipes.estimated_preparation_time, categories.name AS 'cat_name' ,COUNT(cook_recipes.ID_recipe) AS 'quantity' FROM cook_recipes INNER JOIN ingredients_quantity ON cook_recipes.ID_recipe=ingredients_quantity.ID_recipe INNER JOIN ingredients ON ingredients.ID_ingredient=ingredients_quantity.ID_ingredient INNER JOIN categories ON categories.ID_category=cook_recipes.ID_category WHERE cook_recipes.checked=1 AND (ingredients.name LIKE '".$add."'";
            for ($i = 1;$i<count($ingredient);$i++) {
                $add = strlen(str2url($ingredient[$i]))>0?str2url($ingredient[$i]):' ';
                $sql .=  (" OR ingredients.name LIKE '".$add."'");
            }
            $sql .= ") GROUP BY cook_recipes.ID_recipe";
            $connection->query($sql);

            $sql = "SELECT * FROM quantity_selected INNER JOIN quantity_all on quantity_selected.ID_recipe=quantity_all.ID_recipe WHERE quantity_all.quantity=quantity_selected.quantity";
            $result = $connection->query($sql);

            $this->drawResult($result);
        }
    }

    public function drawResult($result) {
        if ($result->num_rows > 0) {
            echo 'Z wybranych składników możesz przyrządzić:';
            echo '<table id="recipes" cellpadding="5px" cellspacing="0px" border="1px"><th><a href="chooseIngredients.php?order=name"> Nazwa dania </a></th><th><a href="chooseIngredients.php?order=time"> Czas przgotowania </a></th><th><a href="chooseIngredients.php?order=author">Autor</a></th><th><a href="chooseIngredients.php?order=date">Data dodania</a></th><th><a href="chooseIngredients.php?order=category">Kategoria</a></th>';
            for ($i = 0; ($row = $result->fetch_assoc()) != null; $i++) {
                $class = $i % 2 == 0 ? 'light' : 'normal';
                echo ' <tr class="' . $class . '"><td><a href="recipe.php?recipeId=' . $row['ID_recipe'] . '">' . $row['name'] . '</a></td><td>' . $row['estimated_preparation_time'] . ' minut</td><td><a href="profile.php?nick=' . rawurlencode($row['nick']) . '">' . $row['nick'] . '</td><td>' . substr($row['creation_date'], 0, 10) . '</td><td>'.$row['cat_name'].'</td></tr></a>';
            }
            echo '</table>';
        } else {
            echo "Nie znaleźiono żadnego przepisu do wykonania z podanych składników";
        }
    }

    public function drawForm() {
        echo<<<END
    <div class="form">
        <form action="chooseIngredients.php" method="post" id="addRecipe" enctype="multipart/form-data">
            <div>
                <label style="margin-bottom: 5px;">Podaj posiadane składniki,<br>a my powiemy co możesz z nich zrobić.</label>
                <div id="ingredient0">
                    <div class="number" style="float: left; margin-right: 15px;">1. </div>
                    <input type="text" placeholder="Nazwa składnika" name="ingredient[]" style="width: 65%; float: left" required />
                    <div class="clear"></div>
                </div>
            </div>
            <a href="#" onclick="addIngredientOne()" style="margin-right: 15px">+ Dodaj składnik</a>
            <a href="#" onclick="removeIngredient()">- Usuń ostatni składnik</a>

            <input type="submit" name="submit" value="Szukaj przepisów"/>
        </form>
    </div>
END;

    }
}

?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <title>Wybierz składniki - Książka kucharska</title>
    <link href="style.css" type="text/css" rel="stylesheet" />
    <script src="addIngredient.js"></script>
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
    $page = new ChooseIngredients();

    if (isset($_POST['submit'])) {
        $page->search($_POST['ingredient']);
    } else {
        $page->drawForm();
    }
    ?>
</div>
</body>
</html>