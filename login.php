<?php
error_reporting(E_ERROR);
session_start();
require_once('functions.php');

if(isset($_SESSION['logged'])) {
    infoPage('Już jesteś zalogowany');
    exit();
}

if(isset($_POST['nick'])) {
    $connection = connectDB();

    if (mysqli_connect_errno() != 0)
    {
        $error = 'Nie udało się połączyć z bazą danych! Spróbuj ponownie później.';
    }
    else {
        $login = $_POST['nick'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE nick = ?";

        $prep = $connection->prepare($sql);
        $prep->bind_param('s',$login);
        $prep->execute();
        $rs = $prep->get_result();
        $numRows = $rs->num_rows;
        $prep->close();

        if($numRows  == 1) {
            $row = $rs->fetch_assoc();
            if(password_verify($password,$row['password'])){
                $_SESSION['logged'] = true;
                $_SESSION['login'] = $login;
                $_SESSION['accountType'] = $row['account_type'];
                infoPage('Zalogowano');
                exit();
            }
            else $error = "Nieprawidłowe hasło";
        }
        else {
            $error = "Nieprawidłowa nazwa użytkownika";
        }
    }
    $connection->close();
}
?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <title>Logowanie - Książka kucharska</title>
    <link href="style.css" type="text/css" rel="stylesheet" />
</head>
<body>
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
<div id="content" class="form">
    <form method="post" action="login.php">
        <div>
            <label>Nazwa użytkownika:</label>
            <input type="text" name="nick" />
        </div>
        <div>
            <label>Hasło:</label>
            <input type="password" name="password" />
        </div>
        <br /><input type="submit" value="Zaloguj"/>
    </form>
    <div id="err" >
        <?php echo $error?>
    </div>
    <a href="register.php" id="confirm">Rejestracja</a>
</div>
</body>
</html>
