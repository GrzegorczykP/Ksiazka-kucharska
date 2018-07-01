<?php

class Profile {
    private $name;
    private $joinDate;
    private $accountType;
    private $recipes = array();

    private function getProfileName($connection) {
        if(!isset($_GET['nick'])) {
            if (isset($_SESSION['login']))$profileName = $_SESSION['login'];
            else {
                infoPage('Nie istnieje taki profil');
                exit;
            }
        }
        else {
            $sql = "SELECT * FROM users WHERE nick = ?";
            $prep = $connection->prepare($sql);
            $prep->bind_param('s', $_GET['nick']);
            $prep->execute();
            if ($prep->get_result()->num_rows == 1) $profileName = $_GET['nick'];
            else {
                infoPage('Nie istnieje taki profil');
                exit;
            }
            $prep->close();
        }
        return $profileName;
    }

    private function drawTopPartPage($sectorName) {
        echo '<div id="recipeHeader">';
        $path = file_exists('avatars/'.$this->name.'.jpg')?'avatars/'.$this->name.'.jpg':'avatars/default.jpg';
        echo '<img src="'.$path.'" />';
        echo '<div id="recipeInfo" >';
        echo '<div id="recipeName" >'.$this->name.'</div>';
        echo '<label>Data dołączenia: </label>'.$this->joinDate.'<br />';
        echo '<label>Ilość dodanych przepisów: </label>'.count($this->recipes).'<br />';
        echo '<label>Rodzaj konta: </label>'.$this->accountType.'<br />';
        echo '<div class="clear"></div> </div> <div class="clear"></div>';
        echo '</div>';
        echo '<div id="recipe" >';
        echo '<div id="ingredients" ><b>Akcje:</b>';
        // Przyciski akcji
        echo '<div class="button"><a href="profile.php?action=addAvatar">Zmień avatar</a></div> ';

        //
        echo '</div>'; //ingredients

        echo '<div id="instructions"><b>'.$sectorName.':</b><br /><br />';
    }

    private function drawBottomPartPage() {
        echo '</div><div class="clear"></div></div>'; //recipe
    }

    private function uploadImage() {
        $target_dir = "avatars/";
        $target_file = $target_dir . $this->name . '.jpg';
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["avatar"]["tmp_name"]);
            if($check !== false) {
                $uploadOk = 1;
            } else {
                return "Wybrany plik nie jest zdjęciem";
            }
        }

        if ($_FILES["avatar"]["size"] > 1048576) {
            return "Avatar nie może być większy niż 1 MB";
        }

        if($imageFileType != "jpg" ) {
            return "Avatar może być tylko w formacie JPG";
        }

        if (file_exists($target_file)) {
            unlink($target_file);
        }

        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            return "Pomyślnie zmieniono avatar";
        } else {
            return "Wystąpił błąd przy zmianie avatara";
        }

    }

    public function __construct() {
        require_once __DIR__ . '/../functions.php';
        $connection = connectDB();

        if ($connection->connect_errno) {
            infoPage('Nie udało się połączyć z bazą danych! Spróbuj ponownie później.');
        }
        else {
            $this->name = $this->getProfileName($connection);

            $sql = "SELECT *, cook_recipes.creation_date AS recipe_creation_date FROM users INNER JOIN cook_recipes ON cook_recipes.nick=users.nick WHERE users.nick = ? AND cook_recipes.checked = 1 ORDER BY cook_recipes.name";
            $prep = $connection->prepare($sql);
            $prep->bind_param('s',$this->name);
            $prep->execute();
            $result = $prep->get_result();
            if (($addedRecipes = $result->num_rows) < 1) {
                $sql = "SELECT * FROM users WHERE users.nick = ?";
                $prep = $connection->prepare($sql);
                $prep->bind_param('s',$this->name);
                $prep->execute();
                $result = $prep->get_result();
            }
            $row = $result->fetch_assoc();
            $prep->close();

            $this->joinDate = substr($row['creation_date'],0,10);

            switch ($row['account_type']) {
                case 'normal':
                    $this->accountType = 'standardowy';
                    break;
                case 'moderator':
                    $this->accountType = 'moderator';
                    break;
                default:
                    $this->accountType = '';
                    break;
            }

            if ($addedRecipes > 0) {
                do {
                    $this->recipes[] = array(
                        'recipe_ID' => $row['ID_recipe'],
                        'name' => $row['name'],
                        'creationDate' => substr($row['recipe_creation_date'],0,10),
                        'preparationTime' => $row['estimated_preparation_time']
                    );
                } while(($row = $result->fetch_assoc()) != NULL);
            }
        }
        $connection->close();
    }

    public function mainProfilePage() {
        $this->drawTopPartPage('Przepisy użytkownika '.$this->name.':');

        if (count($this->recipes)>0) {
            echo '<table id="recipes" cellpadding="5px" cellspacing="0px" border="1px">';
            echo '<th>Nazwa</th><th>Data dodania</th><th>Czas przygotowania</th>';
            for ($i = 0; $i < count($this->recipes); $i++) {
                $class = $i % 2 == 0 ? 'light' : 'normal';
                echo ' <tr class="' . $class . '"><td><a href="recipe.php?recipeId='.$this->recipes[$i]['recipe_ID'].'" >'.$this->recipes[$i]['name'].'</a></td>';
                echo '<td>'.$this->recipes[$i]['creationDate'].'</td>';
                echo '<td>'.$this->recipes[$i]['preparationTime'].'</td></tr></a>';
            }
            echo '</table>';
        } else echo 'Użytkownik nie dodał jescze żadnego przepisu';

        $this->drawBottomPartPage();
    }

    public function addAvatarPage() {
        $this->drawTopPartPage('Zmień avatar');
        if (isset($_FILES['avatar']['name'])) echo $this->uploadImage();
        else {
            echo <<<END
<div class="form">
    <form method="post" action="profile.php?action=addAvatar" enctype="multipart/form-data">
        <div>
            <label>Plik z avatarem:</label>
            <input type="file" name="avatar" "/>
        </div>
        <br /><input name="submit" type="submit" value="Zmień avatar"/>
    </form>
</div>
END;
        }

        $this->drawBottomPartPage();
    }

    public function getData() {
        return array(
            'nickname' => $this->name,
            'joinDate' => $this->joinDate,
            'accountType' => $this->accountType,
            'recipes' => $this->recipes
        );
    }
}