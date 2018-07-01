<?php

class Ingredients {
    public $ingredient;
    public $quantity;
    public $unit;

    public function __construct($ingredient, $quantity, $unit) {
        $this->ingredient = $ingredient;
        $this->quantity = $quantity;
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
    private $picture;

    private $ingredients = array();

    public function __construct($name_c = NULL, $instruction_c = NULL, $category_c = NULL, $prepTime_c = NULL, $ingredient_c = NULL, $ingredientQuantity_c = NULL, $ingredientUnit_c = NULL, $picture = NULL) {
        $this->name = $name_c;
        $this->instruction = $instruction_c;
        $this->category = $category_c;
        $this->prepTime = $prepTime_c;

        for($i = 0; $i<count($ingredient_c); $i++) {
            $x = new Ingredients($ingredient_c[$i], $ingredientQuantity_c[$i], $ingredientUnit_c[$i]);
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
            $sql = "INSERT INTO cook_recipes (ID_recipe, nick, name, creation_date, instructions, estimated_preparation_time, ID_category, checked) VALUES (NULL, ?, ?, CURRENT_TIMESTAMP, ?, ?, ?, 0);";
            $prep = $connection->prepare($sql);
            $prep->bind_param('sssii',$_SESSION['login'],$this->name,$this->instruction,$this->prepTime,$this->category);
            $prep->execute();
            $recipeID = $prep->insert_id;
            $prep->close();

            $sql = "INSERT INTO ingredients_quantity (ID_indredient, ID_recipe, quantity, unit) VALUES (?, ?, ?, ?)";
            $prep = $connection->prepare($sql);

            foreach ($this->ingredients as $row) {
                $prep->bind_param('iids',$row->ingredient,$recipeID,$row->quantity,$row->unit);
                $prep->execute();
            }
            $prep->close();

            $this->uploadImage();

            return '';
        }
    }

    private function checkData() {
        if (strlen($this->name)>30) return 'Nazwa jest zbyt długa';
        if (strlen($this->name)<1) return 'Nie podano nazwy';
        if (strlen($this->instruction)<1) return 'Nie podano instrukcji';
        if (strlen($this->prepTime)<1) return 'Nie podano czasu przygotowaniaa';
        if ($this->picture['error']) return 'Wystąpił bląd przy przesyłaniu zdjęcia';
        if ($this->picture['size']>1024000) return 'Zdjącie nie może być większe niż 1 MB';

        return '';
    }

    private function uploadImage() {
        if($_FILES['picture']['name'])
        {
            $new_file_name = 'recipe_img/'.$this->name.'.jpg';
            move_uploaded_file($_FILES['picture']['tmp_name'], $new_file_name);
            echo $this->picture['error'];
        }
    }
}