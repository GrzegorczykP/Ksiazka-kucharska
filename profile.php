<?php
session_start();
require_once('functions.php');
require_once('classes/profile.php');

if (isset($_GET['nick'])||isset($_SESSION['login'])) $profileName = isset($_GET['nick'])?$_GET['nick']:$_SESSION['login'];
else $profileName = '';

$profile = new Profile($profileName);
$profileData = $profile->getData();

?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
    <meta charset="UTF-8" />
    <?php echo '<title>'.$profileData['nickname'].' - Książka kucharska</title>'?>
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
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'addAvatar':
                $profile->addAvatarPage();
                break;
            default:
                $profile->mainProfilePage();
                break;
        }
    } else $profile->mainProfilePage();
    ?>
</div>
</body>
</html>