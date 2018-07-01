<?php
session_start();
require_once('functions.php');
require_once('classes/recipe.php');

$error = NULL;

if (isset($_POST['recipeName'])) {
    $recipe = new Recipe($_POST['recipeName'], $_POST['instruction'], $_POST['category'], $_POST['prepTime'], $_POST['ingredient'], $_POST['quantity'], $_POST['unit']);
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
    <script src="addIngredient.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Lato&amp;subset=latin-ext" rel="stylesheet">
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
                <input type="text" name="recipeName" title="Podaj nazwę dania (tylko litery maksymalnie 30 znaków)" required pattern=".[A-Za-ząćęłńóśźżĄĆĘŁŃÓŚŹŻ ]{1,30}" value="<?php if(isset($_POST['recipeName'])) echo $_POST['recipeName'] ?>"/>
            </div>
            <div>
                <label>Składniki:</label>
                <div id="ingredient0">
                    <input type="text" placeholder="Nazwa składnika" name="ingredient[]" style="width: 65%; float: left" required />
                    <input name="quantity[]" type="number" placeholder="ilość" min="0" step="0.01" style="display: inline-block; margin-left: auto; margin-right: auto; width: 15%; border: 1px rgb(169, 169, 169) solid;" />
                    <input type="text" placeholder="jednostka" name="unit[]" style="width: 15%; float: right;" />
                    <div class="clear"></div>
                </div>
            </div>
            <a href="#" onclick="addIngredient()" style="margin-right: 15px">+ Dodaj składnik</a>
            <a href="#" onclick="removeIngredient()">- Usuń ostatni składnik</a>
            <div>
                <label>Instrukcja przygotowania:</label>
                <textarea type="text" name="instruction" title="Podaj instrukcję przygotowania" form="addRecipe" cols="69" rows="10" style="resize: vertical; margin-top: 15px; width: 500px;" required value="<?php if(isset($_POST['instruction'])) echo $_POST['instruction'] ?>"></textarea>
            </div>
            <div>
                <label>Czas przygotowania w minutach</label>
                <input type="number" name="prepTime" title="Podaj czas przygotowania w minutach" min="5" required value="<?php if(isset($_POST['prepTime'])) echo $_POST['prepTime'] ?>"/>
            </div>
            <div>
                <label>Kategoria</label>
                <select name="category" required value="<?php if(isset($_POST['category'])) echo $_POST['category'] ?>">
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
            <div class="err">
                <?php echo $error?>
            </div>
            <input type="submit" value="Dodaj przepis"/>
        </form>
    </div>
</div>
</body>
</html>