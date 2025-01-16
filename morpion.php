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
    $grid = initGrille();

    switch($gameMode){
        case cst_1v1:
            gamePlayers(false, $grid);
            break;
        case cst_1v13g:
            gamePlayers(true, $grid);
            break;
        case cst_1vBot:
            break;
        default:
    }
    displayMenu();
}

function gamePlayers($multiGame, $grid){

    $round_max = $multiGame?3:1;
    $round = 1;

    list($player1, $player2) = setPlayers(false);

    echo PHP_EOL.$player1->getInfo()." ".$player2->getInfo();

    do{
        $grid = initGrille();
        $player = randFirst()==1?$player1:$player2;
        while(true){  
            echo PHP_EOL."C'est à ".$player->getName()." de jouer. (".$player->getColor().$player->getSign().RAZ_COLOR.")".PHP_EOL;
            $caseEmpty = true; //$caseEmpty = true si la case est vide (donc c'est possible) = false si la case est remplie
            do{
                displayGrille($grid);
                if(!$caseEmpty)
                    echo "La case est déjà prise.".PHP_EOL;
                PHP_EOL.$choix = readline("Votre choix : ");
                $caseEmpty = isCaseEmpty($grid, $choix);
            }while(!$caseEmpty);
            $grid = fileGrid($player,$choix,$grid);
            if(verificationVictoire($grid,$player)){
                displayGrille($grid);
                echo PHP_EOL. $player->getName(). " a gagné la partie !".PHP_EOL;
                $player->incrementWin();
                break;
            }elseif (isGridFull($grid)) {
                displayGrille($grid);
                echo "Match nul".PHP_EOL;
                break;
            }else{
                $player=switchPlayer($player, $player1, $player2);
            }
        }
        $round++;
    }while($round<=$round_max);

    $player = $player1->getWin()>$player2->getWin()?$player1:$player2;

    echo PHP_EOL.$player->getName()." a gagné le jeu avec ".$player->getWin()." partie(s) gagnée(s) !".PHP_EOL;
    
}

function gamePlayerBot($grid){
/**
 * faire le choix : mode hard ou mode impossible
 */
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
    $grid = [
        ["1", "2", "3"],
        ["4", "5", "6"],
        ["7", "8", "9"]
    ];  

    return $grid;
}   

//Fonction d'affichage de la grille
function displayGrille($grid) {
    echo "\n";
    for ($i = 0; $i < 3; $i++) {
        for ($j = 0; $j < 3; $j++) {
            // Affiche la case ou un espace vide
            echo " " . ($grid[$i][$j] !== "" ? $grid[$i][$j] : " ");
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

function fileGrid($player, $chosenNumber,$grid){

    $sign_color = $player->getColor(). $player->getSign(). RAZ_COLOR;

    switch($chosenNumber){
        case 1:
            $grid[0][0] = $sign_color;
            break;
        case 2:
            $grid[0][1] = $sign_color;
            break;
        case 3:
            $grid[0][2] = $sign_color;
            break;
        case 4:
            $grid[1][0] = $sign_color;
            break;
        case 5:
            $grid[1][1] = $sign_color;
            break;
        case 6:
            $grid[1][2] = $sign_color;
            break;
        case 7:
            $grid[2][0] = $sign_color;
            break;
        case 8:
            $grid[2][1] = $sign_color;
            break;
        case 9:
            $grid[2][2] = $sign_color;
            break;
    }

    return $grid;
}

function isGridFull($grid){

    foreach($grid as $ligne){
        foreach($ligne as $case){
            if(!(str_contains("X",$case) || str_contains("O", $case))){
                return false;
            }
        }
    }
    return true;

}

function isCaseEmpty($grid, $chosenNumber){

    switch($chosenNumber){
        case 1:
            return $grid[0][0] == 1;
        case 2:
            return $grid[0][1] == 2;
        case 3:
            return $grid[0][2] == 3;
        case 4:
            return $grid[1][0] == 4;
        case 5:
            return $grid[1][1] == 5;
        case 6:
            return $grid[1][2] == 6;
        case 7:
            return $grid[2][0] == 7;
        case 8:
            return $grid[2][1] == 8;
        case 9:
            return $grid[2][2] == 9;
        default:
            return false;
    }
}

function setPlayers($bot_on) {
    echo PHP_EOL . "Avant de commencer, veuillez entrer quelques informations des joueurs." . PHP_EOL;
    echo str_repeat("-", 40) . PHP_EOL;
    echo "Joueur 1 : " . PHP_EOL;

    // Saisie du nom du joueur 1
    $name1 = readline("Le nom du joueur 1 : ");

    // Choix du signe du joueur 1
    echo PHP_EOL . "Ensuite, choisissez le signe du joueur 1 : X ou O : " . PHP_EOL;
    do {
        $sign_choice1 = readline("Le signe du joueur 1 : ");
    } while ($sign_choice1 !== 'X' && $sign_choice1 !== 'O');

    // Couleurs disponibles (sans formatage)
    $available_colors = [
        'R' => 'Rouge',
        'V' => 'Vert',
        'B' => 'Bleu'
    ];

    // Choix de la couleur du joueur 1
    echo PHP_EOL . "Pour finir, veuillez choisir la couleur parmi les options disponibles : " . PHP_EOL;
    do {
        // Afficher les couleurs disponibles avec leur code d'échappement
        echo "Options : ";
        foreach ($available_colors as $key => $color_name) {
            $color_constant = constant(strtoupper($color_name)); // Associe la constante (ROUGE, VERT, BLEU)
            echo "$key = $color_constant$color_name\033[0m ";
        }
        echo PHP_EOL;

        $color_choice1 = strtoupper(readline("La couleur du joueur 1 : "));
    } while (!array_key_exists($color_choice1, $available_colors));

    // Associer la constante correspondant au choix
    $color1 = constant(strtoupper($available_colors[$color_choice1]));
    unset($available_colors[$color_choice1]); // Retirer la couleur choisie

    // Création du joueur 1
    $player1 = new Player($name1, $sign_choice1, $color1, 1);

    if ($bot_on) {
        // Si le bot est activé
        $player2 = new Player("Ordinateur", ($sign_choice1 === 'X') ? 'O' : 'X', MAGENTA, 2);
    } else {
        echo PHP_EOL . "Joueur 2 : " . PHP_EOL;

        // Saisie du nom du joueur 2
        $name2 = readline("Le nom du joueur 2 : ");

        // Choix du signe du joueur 2
        $sign_choice2 = ($sign_choice1 === 'X') ? 'O' : 'X';
        echo PHP_EOL . "Le signe du joueur 2 sera automatiquement : $sign_choice2" . PHP_EOL;

        // Choix de la couleur du joueur 2
        echo PHP_EOL . "Choisissez la couleur parmi les options disponibles : " . PHP_EOL;
        do {
            // Afficher les couleurs restantes avec leur code d'échappement
            echo "Options : ";
            foreach ($available_colors as $key => $color_name) {
                $color_constant = constant(strtoupper($color_name)); // Associe la constante (ROUGE, VERT, BLEU)
                echo "$key = $color_constant$color_name\033[0m ";
            }
            echo PHP_EOL;

            $color_choice2 = strtoupper(readline("La couleur du joueur 2 : "));
        } while (!array_key_exists($color_choice2, $available_colors));

        // Associer la constante correspondant au choix
        $color2 = constant(strtoupper($available_colors[$color_choice2]));

        // Création du joueur 2
        $player2 = new Player($name2, $sign_choice2, $color2, 2);
    }

    // Retourner les deux joueurs
    return [$player1, $player2];
}
function switchPlayer($player, $player1, $player2){
    return ($player===$player1)?$player2:$player1;
}
function verificationVictoire($grid, $player) {
    $symbole = $player->getColor(). $player->getSign(). RAZ_COLOR;
    // Vérifier les alignements horizontaux
    for ($i = 0; $i < 3; $i++) {
        if ($grid[$i][0] === $symbole && $grid[$i][1] === $symbole && $grid[$i][2] === $symbole) {
            return true; // Victoire horizontale
        }
    }

    // Vérifier les alignements verticaux
    for ($j = 0; $j < 3; $j++) {
        if ($grid[0][$j] === $symbole && $grid[1][$j] === $symbole && $grid[2][$j] === $symbole) {
            return true; // Victoire verticale
        }
    }

    // Vérifier la diagonale principale
    if ($grid[0][0] === $symbole && $grid[1][1] === $symbole && $grid[2][2] === $symbole) {
        return true; // Victoire diagonale principale
    }

    // Vérifier la diagonale secondaire
    if ($grid[0][2] === $symbole && $grid[1][1] === $symbole && $grid[2][0] === $symbole) {
        return true; // Victoire diagonale secondaire
    }

    // Pas de victoire
    return false;
}

function destroyObject($player){
    unset($player);
}

?>