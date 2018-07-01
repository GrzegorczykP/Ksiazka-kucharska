<?php
session_start();
require_once('functions.php');
require_once('classes/recipe.php');

$error = NULL;

if (isset($_POST['recipeName'])) {
    $recipe = new Recipe($_POST['recipeName'], $_POST['instruction'], $_POST['category'], $_POST['prepTime'], $_POST['ingredient'], $_POST['quantity'], $_POST['unit'], $_FILES['picture']);
    $connection = connectDB();

    if ($connection->connect_errno) {
        $error = 'Nie udało się połączyć z bazą danych! Spróbuj ponownie później.';
    }
    else {
        if(strlen($err = $recipe->addRecipe($connection))>0) {
            $error = $err;
        }
        else {
            infoPage("Dodano przepis na ".$recipe->Name()." na listę oczekujących na akceptację");
            exit();
        }
    }
    $connection->close();
}

?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <title>Dodawanie przepisu - Książka kucharska</title>
    <link href="style.css" type="text/css" rel="stylesheet" />
    <script src="ingredients.js"></script>
</head>
<body>
<?php
topRightMenu(true);
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
<div id="content" >
    <div class="form">
        <form action="addRecipe.php" method="post" id="addRecipe" enctype="multipart/form-data">
            <div>
                <label>Nazwa dania:</label>
                <input type="text" name="recipeName" title="Podaj nazwę dania (tylko litery maksymalnie 30 znaków)" required pattern=".[A-Za-ząćęłńóśźżĄĆĘŁŃÓŚŹŻ ]{1,30}"/>
            </div>
            <div>
                <label>Składniki:</label>
                <div id="ingredient0">
                    <select name="ingredient[]" style="width: 65%; float: left" required>
<?php
$connection = connectDB();

if ($connection->connect_errno) {
    $error = 'Nie udało się połączyć z bazą danych! Spróbuj ponownie później.';
}
else {
    $sql = "SELECT ID_ingredient, name FROM ingredients ORDER BY name";
    if ($result = $connection->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            echo '<option value=' . $row['ID_ingredient'] . ' >' . $row['name'] . '</option>';
        }
    }
}

$connection->close();
?>
                    </select>
                    <input name="quantity[]" type="number" min="1" style="display: inline-block; margin-left: auto; margin-right: auto; width: 10%; border: 1px rgb(169, 169, 169) solid;" required/>
                    <select name="unit[]" style="width: 20%; float: right;" required>
                        <option value="ml">ml</option>
                        <option value="l">l</option>
                        <option value="g">g</option>
                        <option value="dag">dag</option>
                        <option value="kg">kg</option>
                        <option value="szt">sztuk</option>
                    </select>
                    <div class="clear"></div>
                </div>
            </div>
            <a href="#" onclick="addIngredient()" style="margin-right: 15px">+ Dodaj składnik</a>
            <a href="#" onclick="removeIngredient()">- Usuń ostatni składnik</a>
            <div>
                <label>Instrukcja przygotowania:</label>
                <textarea type="text" name="instruction" title="Podaj instrukcję przygotowania" form="addRecipe" cols="69" rows="10" style="resize: vertical; margin-top: 15px;" required ></textarea>
            </div>
            <div>
                <label>Czas przygotowania w minutach</label>
                <input type="number" name="prepTime" title="Podaj czas przygotowania w minutach" min="5" required/>
            </div>
            <div>
                <label>Kategoria</label>
                <select name="category" required>
<?php
$connection = connectDB();

if ($connection->connect_errno) {
    $error = 'Nie udało się połączyć z bazą danych! Spróbuj ponownie później.';
}
else {
    $sql = "SELECT ID_category, name FROM categories ORDER BY name";


    if ($result = $connection->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            echo '<option value=' . $row['ID_category'] . ' >' . $row['name'] . '</option>';
        }
    }

    $connection->close();
}
?>
                </select>
            </div>
            <div>
                <label style="width: 30%; float: left;">Zdjęcie potrawy (opcjonalne):</label>
                <input type="file" name="picture" accept="image/jpeg" style="width: 60%; float: right;"/>
                <div class="clear"></div>
            </div>
            <div id="err">
                <?php echo $error?>
            </div>
            <input type="submit" value="Dodaj przepis"/>
        </form>
    </div>
</div>
</body>
</html>