<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'Joueur.php';
/** Liste des constantes 
 * Ces constantes servent à définir le mode de jeu et les couleurs
 */
define("cst_1v1", 1);
define("cst_1v13g", 2);
define("cst_1vBot", 3);
define("ROUGE", "\033[1;31m");
define("VERT", "\033[1;32m");
define("BLEU", "\033[1;34m");
define("MAGENTA", "\033[1;35m");
define("RAZ_COLOR", "\033[0m");

displayMenu();

function startGame($gameMode){
    $grille = initGrille();

    switch($gameMode){
        case cst_1v1:
            break;
        case cst_1v13g:
            break;
        case cst_1vBot:
            break;
        default:
    }

    echo PHP_EOL."Avant de commencer, veuillez entre quelques informations des jouers.".PHP_EOL;
    echo str_repeat("-", 40).PHP_EOL;
    echo "Joueur 1 : ".PHP_EOL;

    $name   = readline("Le nom du joueur 1 : ");
    echo PHP_EOL."Ensuite, choisissez le signe du joueur 1 : X ou O : ".PHP_EOL;
    
    do{
        $sign_choice = readline("Le signe du joueur 1 : ");
    }while($sign_choice !== 'X' && $sign_choice !== 'O');

    echo PHP_EOL."Pour finir, veuillez choisir la couleur : B = Bleu, R = Rouge, V = Vert : ".PHP_EOL;

    do{
        $color_choice = strtoupper(readline("La couleur du joueur 1 : "));
        switch($color_choice){
            case 'R':
                $color = ROUGE;
                break;
            case 'V' :
                $color = VERT;
                break; 
            case 'B' :
                $color = BLEU;
                break; 
        }
    }while($color_choice !== 'R' && $color_choice !== 'V' && $color_choice !== 'B');

    $player1 = new Player($name, $sign_choice, $color, 1);

    if($gameMode === cst_1vBot){
        $player2 = new Player("Ordinateur", ($sign_choice==='X')?"O" : 'X', MAGENTA, 2);
    } else{

    }
}

//Function d'affichage du choix du mode de jeu
function displayModeChoice(){
    $text = [
        ["LINE", ""],
        ["R - Règles", "LEFT"],
        ["J - Jeu unique (2 joueurs)", "LEFT"],
        ["C - Challenge de 3 parties (2 joueurs)", "LEFT"],
        ["O - Contre l'ordinateur", "LEFT"],
        ["Q - Quitter", "LEFT"],
        ["LINE", ""],
    ];

    displayAdaptativeMenu($text);
    
}
function displayMenu() {
    //Définition de chaque ligne présente dans le menu principal
    $mainMenu = [
        ["LINE",""],
        ["Jeu du Morpion","MIDDLE"],
        ["Realisé par :","LEFT"],
        ["- DUVAL Damien","LEFT"],
        ["- DACHEUX Corentin","LEFT"],
        ["- DESJARDIN Paul","LEFT"],
        ["LINE",""],
        ["R - Règles", "LEFT"],
        ["J - Jeu unique (2 joueurs)", "LEFT"],
        ["C - Challenge de 3 parties (2 joueurs)", "LEFT"],
        ["O - Contre l'ordinateur", "LEFT"],
        ["Q - Quitter", "LEFT"],
        ["LINE", ""],
    ];
    
    //Utilisation de la fonction d'affichage
    displayAdaptativeMenu($mainMenu);

    //Gestion du choix du joueur
    do{
        $choix = strtoupper(readline("Votre choix : "));
        //Faire le menu avec les choix
        switch ($choix){
            case 'R':
                displayRules();
                break;
            case 'J':
                startGame(cst_1v1);
                break;
            case 'C':
                startGame(cst_1v13g);
                break;
            case 'O':
                startGame(cst_1vBot);
                break;
            case 'Q':
                echo PHP_EOL."Fin du programme.";
                break;
            default:
                break;
        }
        displayModeChoice();
    } while($choix!=='Q');
}

//Fonction générique pour affichee un menu complet
//Besoin de pa
function displayAdaptativeMenu($textToDisplay) {
    //Déclaration des lignes de texte du menu
    $texts = $textToDisplay;
    
    //Recherche de la ligne la plus grande
    $maxLenght = 0;
    foreach($texts as $text) {
        if($maxLenght < iconv_strlen($text[0], 'UTF-8')) {
            $maxLenght = iconv_strlen($text[0], 'UTF-8');
        }
    }

    //Menu calculation
    $padding = 10;
    $totalLenght = $padding + $maxLenght;
    
    //Display Text
    foreach($texts as $text) {
        if($text[0] == "LINE") {
            displayMenuLine($totalLenght);
        } else {
            $lenghtWithoutText = $totalLenght - iconv_strlen($text[0], 'UTF-8');
            switch ($text[1]) {
                case "LEFT" :
                    $left = 5;
                    $right = $lenghtWithoutText-$left;
                    echo "|" . str_repeat(" ", $left) . $text[0] . str_repeat(" ", $right) . "|" . PHP_EOL;
                    break;
                case "MIDDLE" :
                    $left = floor($lenghtWithoutText/2);
                    $right = ceil($lenghtWithoutText/2);
                    echo "|" . str_repeat(" ", $left) . $text[0] . str_repeat(" ", $right) . "|" . PHP_EOL;
                    break;
                case "RIGHT" :
                    $right = 5;
                    $left = $lenghtWithoutText-$right;
                    echo "|" . str_repeat(" ", $left) . $text[0] . str_repeat(" ", $right) . "|" . PHP_EOL;
                    break;
            }
        }
    }
}

//Function d'affichage des règles du jeu
function displayRules() {
    $texts = [
        ["LINE",""],
        ["Le but","MIDDLE"],
        ["Le but du jeu est d’aligner avant son adversaire 3 symboles identiques","LEFT"],
        ["horizontalement, verticalement ou en diagonale. Chaque joueur a donc son propre","LEFT"],
        ["symbole, généralement une croix pour l’un et un rond pour l’autre.","LEFT"],
        ["LINE",""],
        ["Déroulement d'une partie","MIDDLE"],
        ["Vous avez le choix entre trois mode de jeu.","LEFT"],
        ["Le mode unique consiste en une simple partie en une manche avec deux joueurs.","LEFT"],
        ["Le mode « challenge » se joue en trois manches à deux joueurs. Le joueur qui remporte le plus de manches gagne la partie.","LEFT"],
        ["Le mode contre l'ordinateur se présente sous la forme d'une seule partie au cours de laquelle vous devez le battre.","LEFT"],
        ["LINE",""],
        ["Fin de partie","MIDDLE"],
        ["Le premier joueur à aligner 3 symboles identiques gagne la partie et marque 1 point.","LEFT"],
        ["Si la grille est complétée sans vainqueur, la partie est finie et il y a égalité.","LEFT"],
        ["LINE",""]
    ];

    displayAdaptativeMenu($texts);
}

//Affichage d'une ligne simple avec le format +-....-+
//Le nombre de '-' dépend de la variable $totalLenght passer
function displayMenuLine($totalLenght) {
    echo "+" . str_repeat("-",$totalLenght) . "+" . PHP_EOL;
}

function randFirst(){
    return rand(1,2);
}

//Fonction d'initialisation de la grille de jeu
function initGrille(){
    //Initialisaiton de la grille en 3x3
    $grille = [
        ["1", "2", "3"],
        ["4", "5", "6"],
        ["7", "8", "9"]
    ];  

    return $grille;
}   

//Fonction d'affichage de la grille
function displayGrille($grille) {
    echo "\n";
    //print_r($grille);
    for ($i = 0; $i < 3; $i++) {
        for ($j = 0; $j < 3; $j++) {
            // Affiche la case ou un espace vide
            echo " " . ($grille[$i][$j] !== "" ? $grille[$i][$j] : " ");
            if ($j < 2) {
                echo " |"; // Barre verticale entre les colonnes
            }
        }
        echo "\n";
        if ($i < 2) {
            echo "---+---+---\n"; // Ligne horizontale entre les rangées
        }
    }
    echo "\n";
}

function fileGrid($player, $chosenNumber,$grille){

    $sign_color = $player->color. $player->sign. RAZ_COLOR;

    switch($chosenNumber){
        case 1:
            $grille[0][0] = $sign_color;
            break;
        case 2:
            $grille[0][1] = $sign_color;
            break;
        case 3:
            $grille[0][2] = $sign_color;
            break;
        case 4:
            $grille[1][0] = $sign_color;
            break;
        case 5:
            $grille[1][1] = $sign_color;
            break;
        case 6:
            $grille[1][2] = $sign_color;
            break;
        case 7:
            $grille[2][0] = $sign_color;
            break;
        case 8:
            $grille[2][1] = $sign_color;
            break;
        case 9:
            $grille[2][2] = $sign_color;
            break;
    }
}

function isGridFull($grille){

    foreach($grille as $ligne){
        foreach($ligne as $case){
            if(!(str_contains("X",$case) || str_contains("O", $case))){
                return false;
            }
        }
    }
    return true;

}

function isCaseEmpty($grille, $chosenNumber){

    $grille = [
        ["1", "2", "3"],
        ["4", "5", "6"],
        ["7", "8", "9"]
    ];  

    switch($chosenNumber){
        case 1:
            return $grille[0][0] == 1;
            break;
        case 2:
            return $grille[0][1] == 2;
            break;
        case 3:
            return $grille[0][2] == 3;
            break;
        case 4:
            return $grille[1][0] == 4;
            break;
        case 5:
            return $grille[1][1] == 5;
            break;
        case 6:
            return $grille[1][2] == 6;
            break;
        case 7:
            return $grille[2][0] == 7;
            break;
        case 8:
            return $grille[2][1] == 8;
            break;
        case 9:
            return $grille[2][2] == 9;
            break;
        default:
            return false;
    }
}

function verificationVictoire($grille, $symbole, $player) {
    $symbole = $player->color. $player->sign. RAZ_COLOR;
    // Vérifier les alignements horizontaux
    for ($i = 0; $i < 3; $i++) {
        if ($grille[$i][0] === $symbole && $grille[$i][1] === $symbole && $grille[$i][2] === $symbole) {
            return true; // Victoire horizontale
        }
    }

    // Vérifier les alignements verticaux
    for ($j = 0; $j < 3; $j++) {
        if ($grille[0][$j] === $symbole && $grille[1][$j] === $symbole && $grille[2][$j] === $symbole) {
            return true; // Victoire verticale
        }
    }

    // Vérifier la diagonale principale
    if ($grille[0][0] === $symbole && $grille[1][1] === $symbole && $grille[2][2] === $symbole) {
        return true; // Victoire diagonale principale
    }

    // Vérifier la diagonale secondaire
    if ($grille[0][2] === $symbole && $grille[1][1] === $symbole && $grille[2][0] === $symbole) {
        return true; // Victoire diagonale secondaire
    }

    // Pas de victoire
    return false;
}

function destroyObjects($player1, $player2){
    unset($player1);
    unset($player2);
}

?>