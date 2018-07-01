<?php
session_start();
require_once('functions.php');


if(!isset($_SESSION['accountType'])) {
    infoPage("Nie jesteś zalggowany");
    exit;
}
if($_SESSION['accountType']!='moderator') {
    infoPage("Nie jesteś moderatorem");
    exit;
}

$order = 'name';
//if(!isset($_SESSION['prev'])) $_SESSION['prev']='';

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
        default:
            $order = 'name';
    }

    if ($order == $_SESSION['prev']) $order = $order . ' DESC';
    $_SESSION['prev'] = $order;
}
else {
    if(!isset($_SESSION['prev'])) $order = $_SESSION['prev'];
    else $order = 'name';
}

if(isset($_GET['action'])&&isset($_GET['recipeId'])) {
    $sql = array();
    $error = '';
    switch ($_GET['action']) {
        case 'accept':
            $sql[] = "UPDATE cook_recipes SET checked = 1 WHERE cook_recipes.ID_recipe = ?";
            $error = ' zaakceptować';
            break;
        case 'delete':
            $sql[] = "DELETE FROM ingredients_quantity WHERE ingredients_quantity.ID_recipe = ?";
            $sql[] = "DELETE FROM cook_recipes WHERE cook_recipes.ID_recipe = ?";
            $error = ' usunąć';
            break;
    }
    $connection = connectDB();

    if ($connection->connect_errno) {
        $error = 'Nie udało się połączyć z bazą danych! Spróbuj ponownie później.';
    }
    else {
        $prep='';
        foreach ($sql as $query)
        {
            $prep = $connection->prepare($query);
            $prep->bind_param('i',$_GET['recipeId']);
            $prep->execute();
        }

        if ($prep->affected_rows==1) {
            $error = 'Udało się' . $error;
        }
        else {
            $error = 'Nie udało się' . $error;
        }

        $prep->close();
    }
    $connection->close();
}

?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <title>Panel administracyjny - Książka kucharska</title>
    <link href="style.css" type="text/css" rel="stylesheet" />
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
    if (isset($error)) echo '<div id="err">'.$error.'</div>';
    $connection = connectDB();

    if ($connection->connect_errno) {
        $error = 'Nie udało się połączyć z bazą danych! Spróbuj ponownie później.';
    }
    else {
        $sql = "SELECT * FROM cook_recipes WHERE checked=0 ORDER BY ".$order;
        $prep = $connection->prepare($sql);
        $prep->execute();
        $result = $prep->get_result();

        if ($result->num_rows>0) {
            echo '<h2>Przepisy oczekujące na sprawdzenie</h2>';
            echo '<table id="recipes" cellpadding="5px" cellspacing="0px" border="1px">';
            echo '<th><a href="moderatorPanel.php?order=name"> Nazwa dania </a></th>';
            echo '<th><a href="moderatorPanel.php?order=time"> Czas przgotowania </a></th>';
            echo '<th><a href="moderatorPanel.php?order=author">Autor</a></th>';
            echo '<th><a href="moderatorPanel.php?order=date">Data dodania</a></th>';
            echo '<th>Zakceptuj</th>';
            echo '<th>Usuń</th>';
            for ($i = 0; ($row = $result->fetch_assoc()) != null; $i++) {
                $class = $i % 2 == 0 ? 'light' : 'normal';
                echo '<tr class="' . $class . '">';
                echo '<td><a target="_blank" href="recipe.php?recipeId=' . $row['ID_recipe'] . '">' . $row['name'] . '</a></td>';
                echo '<td>' . $row['estimated_preparation_time'] . ' minut</td>';
                echo '<td><a target="_blank" href="profile.php?nick=' . $row['nick'] . '">' . $row['nick'] . '</a></td>';
                echo '<td>' . substr($row['creation_date'], 0, 10) . '</td>';
                echo '<td><a href="moderatorPanel.php?recipeId='.$row['ID_recipe'].'&action=accept">Akceptuj</a></td>';
                echo '<td><a href="moderatorPanel.php?recipeId='.$row['ID_recipe'].'&action=delete">Usuń</a></td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        else
            echo '<h2>Nie ma żadnych przepisów oczekujących na sprawdzenie</h2>';
    }
    $connection->close();
    ?>
</div>
</body>
</html>