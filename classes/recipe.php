<?php

class Ingredients {
    public $ingredient;
    public $quantity;
    public $unit;

    public function __construct($ingredient, $quantity, $unit) {
        require_once __DIR__ . '/../functions.php';
        $connection = connectDB();

        if ($connection->connect_errno) {
            infoPage('Nie udało się połączyć z bazą danych! Spróbuj ponownie później.');
        }
        else {
            $ingredient = str2url($ingredient);
            if (strlen($ingredient)<1) {
                infoPage('Podano nieprawidłową nazwę składnika');
                exit;
            } else {
                $sql = "SELECT * FROM ingredients WHERE ingredients.name LIKE ?";
                $prep = $connection->prepare($sql);
                $prep->bind_param('s', $ingredient);
                $prep->execute();
                $result = $prep->get_result();
                $affected_rows = $prep->affected_rows;
                $prep->close();
                unset ($prep);
                if ($affected_rows == 1) $this->ingredient = $result->fetch_assoc()['ID_ingredient'];
                else {
                    $sql = "INSERT INTO ingredients (ID_ingredient, name, show_name) VALUES (NULL, ?, NULL)";
                    $prep = $connection->prepare($sql);
                    $prep->bind_param('s', $ingredient);
                    $prep->execute();
                    $this->ingredient = $prep->insert_id;
                    $prep->close();
                }
            }
        }
        $connection->close();

        $this->quantity = $quantity==null?NULL:$quantity;
        $this->unit = $unit;


    }

    public function show() {
        echo $this->ingredient.";".$this->quantity." ".$this->unit."<br />";
    }
}

class Recipe {
    private $name;
    private $instruction;
    private $category;
    private $prepTime;

    private $ingredients = array();

    public function __construct($name_c = NULL, $instruction_c = NULL, $category_c = NULL, $prepTime_c = NULL, $ingredient_c = NULL, $ingredientQuantity_c = NULL, $ingredientUnit_c = NULL) {
        $this->name = $name_c;
        $this->instruction = $instruction_c;
        $this->category = $category_c;
        $this->prepTime = $prepTime_c;

        for($i = 0; $i<count($ingredient_c); $i++) {
            new Ingredients($ingredient_c[$i], $ingredientQuantity_c[$i], $ingredientUnit_c[$i]);
            $this->ingredients[] = new Ingredients($ingredient_c[$i], $ingredientQuantity_c[$i], $ingredientUnit_c[$i]);
        }
    }

    public function show () {
        foreach ($this->ingredients as $row) {
            $row->show();
        }
    }

    public function Name () {
        return $this->name;
    }

    public function addRecipe($connection)
    {
        if (strlen($err = $this->checkData()) > 0) {
            return $err;
        } else {
            if(strlen($err = $this->uploadImage())>0) {
                return $err;
            }
            $sql = "INSERT INTO cook_recipes (ID_recipe, nick, name, creation_date, instructions, estimated_preparation_time, ID_category, checked) VALUES (NULL, ?, ?, CURRENT_TIMESTAMP, ?, ?, ?, 0);";
            $prep = $connection->prepare($sql);
            $prep->bind_param('sssii',$_SESSION['login'],$this->name,$this->instruction,$this->prepTime,$this->category);
            $prep->execute();
            $recipeID = $prep->insert_id;
            $prep->close();

            $sql = "INSERT INTO ingredients_quantity (ID_ingredient, ID_recipe, quantity, unit) VALUES (?, ?, ?, ?)";
            $prep = $connection->prepare($sql);

            foreach ($this->ingredients as $row) {
                $prep->bind_param('iids',$row->ingredient,$recipeID,$row->quantity,$row->unit);
                $prep->execute();
            }
            $prep->close();

            return '';
        }
    }

    private function checkData() {
        if (strlen($this->name)>31) return 'Nazwa jest zbyt długa';
        if (strlen($this->name)<1) return 'Nie podano nazwy';
        if (strlen($this->instruction)<1) return 'Nie podano instrukcji';
        if (strlen($this->prepTime)<1) return 'Nie podano czasu przygotowaniaa';

        return '';
    }

    private function uploadImage() {
        if($_FILES['picture']['name'])
        {
            $target_dir = "recipe_img/";
            $target_file = $target_dir . $this->name . '.jpg';
            $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

            if(isset($_POST["submit"])) {
                $check = getimagesize($_FILES["picture"]["tmp_name"]);
                if($check !== false) {
                    $uploadOk = 1;
                } else {
                    return "Wybrany plik nie jest zdjęciem";
                }
            }

            if ($_FILES["picture"]["size"] > 1048576) {
                return "Zdjęcie nie może być większy niż 1 MB";
            }

            if($imageFileType != "jpg" ) {
                return "Zdjęcie może być tylko w formacie JPG";
            }

            if (file_exists($target_file)) {
                unlink($target_file);
            }

            if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_file)) {
                return "";
            } else {
                return "Wystąpił błąd przy wgrywaniu zdjęcia";
            }
        }
        else return '';
    }
}