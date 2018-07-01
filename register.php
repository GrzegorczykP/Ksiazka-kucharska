<?php
session_start();
require_once('functions.php');
require_once('classes/account.php');

$error = '';

if(isset($_SESSION['logged'])) {
    infoPage('Już jesteś zalogowany');
    exit();
}

if(isset($_POST['nick'])) {
    $account = new Account($_POST['nick'], $_POST['password'], $_POST['rep-password'], $_POST['e-mail']);
    $connection = connectDB();

    if ($connection->connect_errno) {
        $error = 'Nie udało się połączyć z bazą danych! Spróbuj ponownie później.';
    }
    else {
        if(strlen($err = $account->register($connection))>0) {
            $error = $err;
        }
        else {
            $_SESSION['logged'] = true;
            $_SESSION['login'] = $_POST['nick'];
            $_SESSION['accountType'] = 'normal';
            infoPage("Rejestracja pomyślna");
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
    <title>Rejestracja - Książka kucharska</title>
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
    <form method="post" action="register.php">
        <div>
            <label>Nazwa użytkownika:</label>
            <input type="text" name="nick" pattern="[A-Za-ząćęłńóśźżĄĆĘŁŃÓŚŹŻ0-9_ ]{3,24}" required title="Od 3 do 24 znaków. Może składać się z małych i dużych liter, cyfr oraz z '_'" value="<?php if(isset($_POST['nick'])) echo $_POST['nick'];?>"/>
            <text style="color: #ffed00">Od 3 do 24 znaków. Może składać się z małych i dużych liter (także polskich), cyfr oraz z '_'</text>
        </div>
        <div>
            <label>E-mail:</label>
            <input type="text" name="e-mail" required title="Podaj e-mail" value="<?php if(isset($_POST['e-mail'])) echo $_POST['e-mail'];?>"/>
        </div>
        <div>
            <label>Hasło:</label>
            <input type="password" name="password" pattern=".{6,32}" required title="Od 6 do 32 znaków. Może składać się z małych i dużych liter cyfr oraz ze znaków specjalnych"/>
            <text style="color: #ffed00">Od 6 do 32 znaków. Może składać się z małych i dużych liter cyfr oraz ze znaków specjalnych</text>
        </div>
        <div>
            <label>Powtórz hasło:</label>
            <input type="password" name="rep-password" pattern=".{6,32}" required title="Powtórz hasło"/>
        </div>
        <br /><input type="submit" value="Rejestruj"/>
    </form>
    <div id="err">
        <?php echo $error?>
    </div>
    <a href="login.php" id="confirm">Logowanie</a>
</div>
</body>
</html>
