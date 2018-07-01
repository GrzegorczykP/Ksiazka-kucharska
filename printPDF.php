<?php
require_once('fpdf/fpdf.php');
require_once('functions.php');

class PDF extends FPDF {
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Strona '.$this->PageNo(),0,0,'C');
    }

    function FloatingImage($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='') {
        $x_pdf = $this->x;
        $y_pdf = $this->y;
        $this->Image($file, $x, $y, $w, $h, $type, $link);
        $this->x = $x_pdf + $w;
        $this->y = $y_pdf;

        return $y_pdf + $h;
    }
}

class GeneratePDF {
    private $pdf;

    private $recipeName;
    private $recipeAuthor;
    private $recipeAddDate;
    private $recipePrepTime;
    private $recipeCategory;
    private $recipesIngredients = array();
    private $recipesInstruction;

    private function getRecipeInfo($recipeID) {
        $connection = connectDB();
        $sql = "SELECT cook_recipes.nick,cook_recipes.name,cook_recipes.creation_date,cook_recipes.instructions,cook_recipes.estimated_preparation_time,categories.name AS 'cat_name' FROM cook_recipes INNER JOIN categories ON cook_recipes.ID_category=categories.ID_category WHERE cook_recipes.checked = 1 AND cook_recipes.ID_recipe = ?";
        $prep = $connection->prepare($sql);
        $prep->bind_param('i',$recipeID);
        $prep->execute();
        $result = $prep->get_result();
        $prep->close();
        $connection->close();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();

            $this->recipeName = iconv('utf-8','iso-8859-2',$row['name']);
            $this->recipeAuthor = iconv('utf-8','iso-8859-2',$row['nick']);
            $this->recipeAddDate = iconv('utf-8','iso-8859-2',substr($row['creation_date'],0,10));
            $this->recipePrepTime = iconv('utf-8','iso-8859-2',$row['estimated_preparation_time']);
            $this->recipeCategory = iconv('utf-8','iso-8859-2',$row['cat_name']);
            $this->recipesInstruction = iconv('utf-8','iso-8859-2',$row['instructions']);
            $this->recipesIngredients = $this->getIngredients($recipeID);
        } else {
            infoPage("Nie znaleziono przepisu");
            exit;
        }
    }

    private function getIngredients($recipeID) {
        $connection = connectDB();
        $connection->set_charset('iso-8859-2');
        $sql = "SELECT ingredients_quantity.quantity,ingredients_quantity.unit,ingredients.name,ingredients.show_name FROM ingredients_quantity INNER JOIN ingredients ON ingredients.ID_ingredient=ingredients_quantity.ID_ingredient WHERE ID_recipe = ?";
        $prep = $connection->prepare($sql);
        $prep->bind_param('i',$recipeID);
        $prep->execute();
        $result = $prep->get_result();
        $prep->close();
        $connection->close();

        $array = array();

        while ($row = $result->fetch_assoc()) {
            $array[] = array ( 'quantity' => iconv('utf-8','iso-8859-2',$row['quantity']),
                                'unit' => iconv('utf-8','iso-8859-2',$row['unit']),
                                'name' => iconv('utf-8','iso-8859-2',($row['show_name']!=''?$row['show_name']:str_replace('_',' ',$row['name']))) );
        }

        return $array;
    }

    private function getFont() {
        $this->pdf -> AddFont('Lato','', 'Lato-Regular.php');
        $this->pdf -> AddFont('Lato','B', 'Lato-Bold.php');
        $this->pdf -> AddFont('Lato','I', 'Lato-Italic.php');
        $this->pdf -> AddFont('Lato','BI', 'Lato-BoldItalic.php');
    }

    public function __construct($recipeID) {
        $this->getRecipeInfo($recipeID);

        $this->pdf = new PDF();
        $this->pdf -> SetTitle(iconv('iso-8859-2','utf-16',$this->recipeName.' (wersja do druku) - Ksi±¿ka kucharska'));

        $this->getFont();
    }

    public function drawPDF() {
        define('_BORDER',0);
        $this->pdf -> AddPage();

        //Nazwa
        $this->pdf -> SetFont('Lato','B',32);
        $this->pdf -> Cell(20);
        $this->pdf -> MultiCell(150,24,$this->recipeName,_BORDER,'C');

        //Zdjêcie
        $path = file_exists(iconv('iso-8859-2','utf-8','recipe_img/'.$this->recipeName.'.jpg'))?iconv('iso-8859-2','utf-8','recipe_img/'.$this->recipeName.'.jpg'):'recipe_img/default.png';
        $afterImg = $this->pdf -> FloatingImage($path,null,null,70,70) + 10;

        //Informacje
        $this->pdf -> SetFont('Lato','B',14);
        $this->pdf -> Cell(10);
        $this->pdf -> Cell(57,10,'Przepis utworzony przez: ',_BORDER);
        $this->pdf -> SetFont('Lato','',14);
        $this->pdf -> MultiCell(53,10,$this->recipeAuthor,_BORDER);

        $this->pdf -> SetFont('Lato','B',14);
        $this->pdf -> Cell(80);
        $this->pdf -> Cell(32,10,'Data dodania: ',_BORDER);
        $this->pdf -> SetFont('Lato','',14);
        $this->pdf -> MultiCell(78,10,$this->recipeAddDate,_BORDER);

        $this->pdf -> SetFont('Lato','B',14);
        $this->pdf -> Cell(80);
        $this->pdf -> Cell(47,10,'Czas przygotowania: ',_BORDER);
        $this->pdf -> SetFont('Lato','',14);
        $this->pdf -> MultiCell(63,10,$this->recipePrepTime,_BORDER);

        $this->pdf -> SetFont('Lato','B',14);
        $this->pdf -> Cell(80);
        $this->pdf -> Cell(24,10,'Kategoria: ',_BORDER);
        $this->pdf -> SetFont('Lato','',14);
        $this->pdf -> MultiCell(86,10,$this->recipeCategory,_BORDER);

        $this->pdf -> y = $afterImg;

        //Sk³adniki
        $this->pdf -> SetFont('Lato','B',14);
        $this->pdf -> SetFillColor(180);
        $this->pdf -> Cell(70,10,'Sk³adniki:',1,1,'C',true);
        $this->pdf -> SetFillColor(240);
        for ($i = 0;$i<count($this->recipesIngredients);$i++) {
            $str = '> '.$this->recipesIngredients[$i]['quantity'].' '.$this->recipesIngredients[$i]['unit'].' '.$this->recipesIngredients[$i]['name'];

            $this->pdf -> SetFont('Lato','',12);
            $this->pdf -> MultiCell(70,5,$str,'RLB','L',true);
        }

        $this->pdf -> y = $afterImg;

        //Instrukcja
        $this->pdf -> SetFont('Lato','B',14);
        $this->pdf -> Cell(80);
        $this->pdf -> SetFillColor(180);
        $this->pdf -> Cell(110,10,'Instrukcja:',1,1,'C',true);
        $this->pdf -> SetFont('Lato','',12);
        $this->pdf -> Cell(80);
        $this->pdf -> SetFillColor(240);
        $this->pdf -> MultiCell(110,6,$this->recipesInstruction,'RLB','L',true);

        //Otwarcie PDF
        $this->pdf -> Output();
    }
}

$pdf = new GeneratePDF($_GET['recipeId']);
$pdf->drawPDF();