<?php
session_start();
require_once('functions.php');

if (isset($_GET['recipeId'])) {
    $connection = connectDB();
    $sql = "SELECT ID_recipe, nick, cook_recipes.name FROM cook_recipes WHERE ID_recipe = ?";
    $prep = $connection->prepare($sql);
    $prep->bind_param('i',$_GET['recipeId']);
    $prep->execute();
    $result = $prep->get_result();
    $prep ->close();

    $row = $result->fetch_assoc();

    if (!isset($_SESSION['login'])||!($_SESSION['login']==$row['nick']||$_SESSION['accountType']=='admin'||$_SESSION['accountType']=='moderator')) {
        infoPage('Nie masz uprawnień do przeglądania tej strony');
        exit;
    } else {

        if ($result->num_rows == 1) {
            if (isset($_POST["submit"])) {
                if (strlen($err = uploadImage($row['name'])) > 0) {
                    echo $err;
                } else {
                    header('Location: recipe.php?recipeId=' . $_GET['recipeId']);
                }
            }
        } else {
            infoPage('Nie znaleziono przepisu');
            exit;
        }
    }
    $connection -> close();
} else {
    infoPage('Nie znaleziono przepisu');
    exit;
}

function uploadImage($name) {
    $target_dir = "recipe_img/";
    $target_file = $target_dir . $name . '.jpg';
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["picture"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            return "Wybrany plik nie jest zdjęciem";
        }
    }

    if ($_FILES["picture"]["size"] > 2097152) {
        return "Avatar nie może być większy niż 2 MB";
    }

    if ($imageFileType != "jpg") {
        return "Avatar może być tylko w formacie JPG";
    }

    if (file_exists($target_file)) {
        unlink($target_file);
    }

    if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
        return "";
    } else {
        return "Wystąpił błąd przy zmianie avatara";
    }
}

?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <title>Dodaj zdjęcie - Książka kucharska</title>
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
    <div class="form">
        <form method="post" action="addPicture.php?recipeId=<?php echo $_GET['recipeId'] ?>" enctype="multipart/form-data">
            <div>
                <label>Plik ze zdjęciem:</label>
                <input type="file" name="picture" "/>
            </div>
            <br /><input name="submit" type="submit" value="Zmień zdjęcie"/>
        </form>
    </div>
</div>
</body>
</html>