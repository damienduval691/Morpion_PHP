<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'Joueur.php';
/** Liste des constantes 
 * Ces constantes servent à définir le mode de jeu et les couleurs
 */
define("cst_1v1",   1);             //Constante pour le mode de jeu : 1vs1 en 1 manche
define("cst_1v13g", 2);             //Constante pour le mode de jeu : 1vs1 en 3 manches
define("cst_1vBot", 3);             //Constante pour le mode de jeu : 1vs1 contre un bot
define("ROUGE",     "\033[1;31m");  //Constante pour la couleur ROUGE dans la console (utilisée pour le signe)
define("VERT",      "\033[1;32m");  //Constante pour la couleur VERT dans la console (utilisée pour le signe)
define("BLEU",      "\033[1;34m");  //Constante pour la couleur BLEU dans la console (utilisée pour le signe)
define("MAGENTA",   "\033[1;35m");  //Constante pour la couleur MEGANTA dans la console (utilisée pour le signe) pour le bot notamment
define("RAZ_COLOR", "\033[0m");     //Constante pour la remise à zéro (pour ne pas rendre en couleur le terminal en entier)
define("MIN",       -1000);         //Constante pour la gestion de l'IA
define("MAX",        1000);         //Constante pour la gestion de l'IA

displayMenu();

/** 
 * @function startGame(int)
 * Fonction qui permet de lancer le jeu
 * @param int $gameMode
 * Paramètre qui permet de savoir quel est le mode de jeu : cst_1v1 ou cst_1v13g ou cst_1vBot
 * @return : aucun
*/
function startGame($gameMode){
    $grid = initGrille();
    //Lancement du jeu selon le mode de jeu $gameMode
    switch($gameMode){
        case cst_1v1:
            gamePlayers(false, $grid);
            break;
        case cst_1v13g:
            gamePlayers(true, $grid);
            break;
        case cst_1vBot:
            gamePlayerBot($grid);
            break;
        default:
    }
    displayMenu();
}
/** 
 * @function gamePlayers(int, array)
 * Fonction qui permet de lancer le jeu à 2 joueurs : jeu avec 1 / 3 parties
 * @param int $multiGame
 * Paramètre qui permet de savoir quel est le mode de jeu : cst_1v1 ou cst_1v13g
 * @param array $grid
 * Paramètre qui fait passer la grille de jeu
 * @return : aucun
*/
function gamePlayers($multiGame, $grid){

    $round_max = $multiGame?3:1;
    $round = 1;
    $pass = true;
    //On demande à l'utilisateur s'il veut personnaliser les joueurs
    do{
        $choix = strtoupper(readline(PHP_EOL."Voulez-vous personnaliser les joueurs ? Y/N : "));
    }while(!($choix === 'Y' || $choix === 'N'));

    //On crée les objets joueurs
    list($player1, $player2) = setPlayers(false, $choix==='Y'?True:False);

    do{
        $grid = initGrille();
        $player = randFirst()==1?$player1:$player2;
        while($pass){ 
            $caseEmpty = true; //$caseEmpty = true si la case est vide (donc c'est possible) = false si la case est remplie
            //On affiche le nom du joueur, son signe ainsi que la couleur, qui doit jouer 
            echo PHP_EOL."C'est à ".$player->getName()." de jouer. (".$player->getColor().$player->getSign().RAZ_COLOR.")".PHP_EOL;
            //Boucle do while qui permet de rester dans la boucle, tant que le joueur a sélectionné une case déjà remplie
            do{
                //On affiche la grille
                displayGrille($grid);
                PHP_EOL.$choix = readline("Votre choix (entrez un chiffre situé dans le tableau) : ");
                //On vérifie si la case sélectionnée est vide ou non, sinon on affiche une erreur
                $caseEmpty = isCaseEmpty($grid, $choix);
                if(!$caseEmpty)
                    echo "La case est déjà prise / le choix entré est invalide.".PHP_EOL;
            }while(!$caseEmpty);

            //On remplie la grille puis on vérifie s'il y a une victoire, un match nul ou si c'est au prochain joueur de jouer
            $grid = fileGrid($player,$choix,$grid);
            if(verificationVictoire($grid,$player)){
                displayGrille($grid);
                echo PHP_EOL. $player->getColor().$player->getName().RAZ_COLOR. " a gagné la partie !".PHP_EOL;
                $player->incrementWin();
                $pass = false;
            }elseif (isGridFull($grid)) {
                displayGrille($grid);
                echo "Match nul ! ".PHP_EOL;
                $pass = false;
            }else{
                $player=switchPlayer($player, $player1, $player2);
            }
        }
        $pass = true;
        $round++;
    }while($round<=$round_max);
    //On regarde qui a gagné, et on affiche le vainqueur
    if($multiGame){
        if($player1->getnbWin() == $player1->getnbWin()){
            echo PHP_EOL."Match nul !".PHP_EOL;
        }elseif($player1->getnbWin() >= $player1->getnbWin()){
            PHP_EOL.$player1->getColor().$player1->getName().RAZ_COLOR." a gagné le jeu avec ".$player1->getnbWin()." parties gagnées !";
        }else{
            PHP_EOL.$player1->getColor().$player1->getName().RAZ_COLOR." a gagné le jeu avec ".$player1->getnbWin()." parties gagnées !"; 
        }
    }
    
}
/** 
 * @function gamePlayerBot(array)
 * Fonction qui permet de lancer le programme de jeu contre l'ordinateur
 * @param array $grid
 * Paramètre qui permet de récupérer la grille de jeu
 * @return : aucun
*/
function gamePlayerBot($grid){
/**
 * faire le choix : mode hard ou mode impossible
 */
    //Personalisation des joueurs
    do{
        $choix = strtoupper(readline(PHP_EOL."Voulez-vous personnaliser le joueur ? Y/N : "));
    }while(!($choix === 'Y' || $choix === 'N'));

    //Par défaut player1 et le joueur et player2 et le bot 
    list($player1, $player2) = setPlayers(true, $choix==='Y'?True:False);

    //Choix de l'IA à affonter (Analytique | Fontion mathématique)
    do{
        $choix = strtoupper(readline(PHP_EOL."Contre quel ordinateur voulez-vous jouer ? A/M(Intelligence Analytique/Fonction Mathématique d'Intelligence) : "));
    }while(!($choix === 'A' || $choix === 'M'));

    //Gestion choix de l'IA
    $gameFinished = false;
    switch ($choix) {
        case 'A':
            //Partie contre l'intelligence analytique
            $grid = initGrille();
            $player = randFirst()==1?$player1:$player2;
            do {
                //On affiche le nom du joueur, son signe ainsi que la couleur, qui doit jouer 
                echo PHP_EOL."C'est à ".$player->getName()." de jouer. (".$player->getColor().$player->getSign().RAZ_COLOR.")".PHP_EOL;
                //Gestion tour joueur et IA
                if($player->getId()==1) {
                    //Boucle do while qui permet de rester dans la boucle, tant que le joueur a sélectionné une case déjà remplie
                    do{
                        //On affiche la grille
                        displayGrille($grid);
                        PHP_EOL.$choix = readline("Votre choix (entrez un chiffre situé dans le tableau) : ");
                        //On vérifie si la case sélectionnée est vide ou non, sinon on affiche une erreur
                        $caseEmpty = isCaseEmpty($grid, $choix);
                        if(!$caseEmpty)
                            echo PHP_EOL."La case est déjà prise / le choix entré est invalide.".PHP_EOL;
                    }while(!$caseEmpty);
                    //On remplie la grille puis on vérifie s'il y a une victoire, un match nul ou si c'est au prochain joueur de jouer
                    $grid = fileGrid($player,$choix,$grid);
                } else {
                    //Tour de l'IA
                    displayGrille($grid);
                    $grid = intelligenceAnalyque($grid,$player1,$player2);
                }
                //On vérifie s'il y a une victoire, un match nul ou si c'est au prochain joueur de jouer
                if(verificationVictoire($grid,$player)){
                    displayGrille($grid);
                    echo PHP_EOL. $player->getColor().$player->getName().RAZ_COLOR. " a gagné la partie !".PHP_EOL;
                    $gameFinished = true;
                }
                if (isGridFull($grid)) {
                    displayGrille($grid);
                    echo "Match nul".PHP_EOL;
                    $gameFinished = true;
                }
                $player=switchPlayer($player, $player1, $player2);
            } while(!$gameFinished);
            break;
        case 'M':
            $grid = initGrille();
            $player = randFirst() == 1 ? $player1 : $player2;
            
            do {
                echo PHP_EOL."C'est à ".$player->getName()." de jouer. (".$player->getColor().$player->getSign().RAZ_COLOR.")".PHP_EOL;
        
                if ($player->getId() == 1) {
                    do {
                        displayGrille($grid);
                        $choix = readline("Votre choix (entrez un chiffre situé dans le tableau) : ");
                        $caseEmpty = isCaseEmpty($grid, $choix);
                        if (!$caseEmpty) {
                            echo PHP_EOL."La case est déjà prise / le choix entré est invalide.".PHP_EOL;
                        }
                    } while (!$caseEmpty);
        
                    $grid = fileGrid($player, $choix, $grid);
                } else {
                    displayGrille($grid); 
                    $grid = intelligenceMath($grid, $player1, $player2);
                }
        
                if(verificationVictoire($grid, $player)){
                    displayGrille($grid);
                    echo PHP_EOL. $player->getColor().$player->getName().RAZ_COLOR. " a gagné la partie !".PHP_EOL;
                    $gameFinished = true;
                } elseif (isGridFull($grid)) {
                    displayGrille($grid);
                    echo "Match nul".PHP_EOL;
                    $gameFinished = true;
                } else {
                    $player = switchPlayer($player, $player1, $player2);
                }
            } while (!$gameFinished);
            break;
        }
}
/** 
 * @function coordToNumber(int, int)
 * Fonction qui permet de récupérer un nombre (de 1 à 9) en fonction des coordonées entrées
 * @param int $x
 * Coordonnée x 
 * @param int $y
 * Coordonnée y
 * @return int : retour de la valeur en nombre des coordonnées
*/
function coordToNumber($x, $y) {
    return $x * 3 + $y + 1;
}

/** 
 * @function intelligenceAnalyque()
 * Fonction qui permet à l'intelligence analytique de jouer un tour
 * @param array $grid
 * Paramètre qui permet de récupérer la grille de jeu
 * @param Player $player
 * Récupère l'objet du joueur en cours
 * @return : la grille de jeu avec le coup effectuer par l'IA analytique
*/
function intelligenceAnalyque($grid,$player1,$player2){

    //Vérifier si l'IA peut gagner sur le tour en cours
    foreach(obtainEmptyCases($grid) as $case){
        $gridTest = fileGrid($player2,coordToNumber($case[0],$case[1]),$grid);
        if(verificationVictoire($gridTest,$player2)){
            $grid = fileGrid($player2, coordToNumber($case[0],$case[1]), $grid);
            return $grid;
        }
    }

    //Si l'IA ne peut pas gagner on vérifie si l'adversaire doit-être bloquer
    foreach(obtainEmptyCases($grid) as $case){
        $gridTest = fileGrid($player1,coordToNumber($case[0],$case[1]),$grid);
        if(verificationVictoire($gridTest,$player1)){
            $grid = fileGrid($player2, coordToNumber($case[0],$case[1]), $grid);
            return $grid;
        }
    }

    //Sinon l'IA prend le centre de la grille
    if(isCaseEmpty($grid,5)) {
        $grid = fileGrid($player2, 5, $grid);
        return $grid;
    }
    
    //Sinon prendre un coin de la grille
    $cases = [[0, 0], [0, 2], [2, 0], [2, 2]];
    foreach($cases as $case){
        if(isCaseEmpty($grid,$grid[$case[0]][$case[1]])){
            $grid = fileGrid($player2, coordToNumber($case[0],$case[1]), $grid);
            return $grid;
        }
    }

    //Enfin sinon jouer au hasard dans une des cases restantes
    $cases = obtainEmptyCases($grid);
    if (!empty($cases)) {
        $caseAleatoire = $cases[array_rand($cases)];
        $grid = fileGrid($player2, coordToNumber($caseAleatoire[0],$caseAleatoire[1]), $grid);
    }
    return $grid;
}

/** 
 * @function obtainEmptyCases()
 * Fonction qui permet d'obtenir toutes les cases vide de la grille de jeu
 * @param array $grid
 * Paramètre qui permet de récupérer la grille de jeu
 * @return : un tableau avec la valeur de chaque case vide
*/
function obtainEmptyCases($grid) {
    $emptyCases = [];
    //Recherche de toutes les cases libres
    foreach ($grid as $i => $line) {
        foreach($line as $j => $case) {
            if(isCaseEmpty($grid,$case)){
                $emptyCases[] = [$i,$j]; //On récupère les coordonnées de la case vide
            }
        }
    }
    
    return $emptyCases;
}

/** 
 * @function miniMax(array, int, bool, Player, Player)
 * Fonction permet de récupérer la meilleure solution pour l'IA
 * @param array $grid
 * Paramètre qui permet de récupérer la grille de jeu
 * @param int $depth
 * Paramètre qui récupère "la profondeur" de la recherche
 * @param bool $isMaximizingPlayer
 * Paramètre qui permet de savoir si c'est l'IA ou le joueur, dans l'algorithme, qui doit maximiser ses chances
 * @param Player $player1
 * Paramètre qui permet de récupérer le joueur 1
 * @param Player $player2
 * Paramètre qui permet de récupérer le joueur 2, qui sera le bot
 * @return int : min eval ou min max - depth
*/
function miniMax($grid, $depth, $isMaximizingPlayer, $player1, $player2) {
    $sign_player1 = $player1->getColor().$player1->getSign().RAZ_COLOR;
    $sign_player2 = $player2->getColor().$player2->getSign().RAZ_COLOR;

    // Vérifie la victoire ou match nul
    if (verificationVictoire($grid, $player2)) {
        return 10; // IA gagne
    }
    if (verificationVictoire($grid, $player1)) {
        return -10; // Joueur humain gagne
    }
    if (isGridFull($grid)) {
        return 0; // Match nul
    }

    if ($isMaximizingPlayer) {
        // Maximisation pour l'IA
        $maxEval = MIN;
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                if ($grid[$i][$j] != $sign_player1 && $grid[$i][$j] != $sign_player2) {
                    $grid = fileGrid($player2, coordToNumber($i,$j), $grid);
                    $maxEval = max($maxEval,miniMax($grid, $depth + 1, !$isMaximizingPlayer, $player1, $player2));
                    $grid[$i][$j] = coordToNumber($i,$j); // Annuler le coup
                }
            }
        }
        return $maxEval - $depth;
    } else {
        // Minimisation pour le joueur humain
        $minEval = MAX;
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                if ($grid[$i][$j] != $sign_player1 && $grid[$i][$j] != $sign_player2) {
                    $grid = fileGrid($player1, coordToNumber($i,$j), $grid);
                    $minEval = min($minEval,miniMax($grid, $depth + 1, !$isMaximizingPlayer, $player1, $player2));
                    $grid[$i][$j] = coordToNumber($i,$j); // Annuler le coup
                }
                
            }
        }
        return $minEval + $depth;
    }
}

/** 
 * @function meilleurCoupMiniMax(array, Player, Player)
 * Fonction permet de récupérer la meilleure solution pour l'IA
 * @param array $grid
 * Paramètre qui permet de récupérer la grille de jeu
 * @param Player $player1
 * Paramètre qui récupère "la profondeur" de la recherche
 * @param Player $player2
 * Paramètre qui permet de savoir si c'est l'IA ou le joueur, dans l'algorithme, qui doit maximiser ses chances
 * @return int: retourne l'index, entre 1 et 9, qui correspond à la position du tableau, pour jouer le meilleur coup
*/
function meilleurCoupMiniMax($grid, $player1, $player2) {
    $sign_player1 = $player1->getColor().$player1->getSign().RAZ_COLOR;
    $sign_player2 = $player2->getColor().$player2->getSign().RAZ_COLOR;
    $meilleurScore = MIN;
    $meilleurCoup   = -1;

    // Recherche du meilleur coup pour l'IA
    for ($i = 0; $i < 3; $i++) {
        for ($j = 0; $j < 3; $j++) {
            if ($grid[$i][$j] != $sign_player1 && $grid[$i][$j] != $sign_player2) {
                $reset = $grid[$i][$j];
                
                $grid = fileGrid($player2, coordToNumber($i,$j), $grid);
                $score = miniMax($grid, 0, false, $player1, $player2);
                $grid[$i][$j] = $reset; // Annuler le coup
                if ($score > $meilleurScore) {
                    $meilleurCoup = $i * 3 + $j + 1;
                    $meilleurScore = $score;
                }
            }
        }
    }

    return $meilleurCoup;
}
/** 
 * @function intelligenceMath(array, Player, Player)
 * Fonction permet de récupérer la grille une fois que l'IA a joué
 * @param array $grid
 * Paramètre qui permet de récupérer la grille de jeu
 * @param Player $player1
 * Paramètre qui récupère "la profondeur" de la recherche
 * @param Player $player2
 * Paramètre qui permet de savoir si c'est l'IA ou le joueur, dans l'algorithme, qui doit maximiser ses chances
 * @return array: grille remplie par l'IA
*/
function intelligenceMath($grid, $player1, $player2) {
    // Trouve le meilleur coup pour l'IA
    $meilleurCoup = meilleurCoupMiniMax($grid, $player1, $player2);
    // Appliquer le coup
    $grid = fileGrid($player2, $meilleurCoup, $grid);
   // $grid[$i][$j] = 'O'; // Jouer le coup de l'IA

    return $grid;
}

/** 
 * @function displayModeChoice()
 * Fonction qui permet d'afficher le menu des choix
 * @return : aucun
*/
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

/** 
 * @function displayMenu()
 * Fonction qui permet d'afficher le menu de démarrage
 * @return : aucun
*/
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
                exit();
            default:
                break;
        }
        displayModeChoice();
    } while($choix!=='Q');

}

/** 
 * @function displayAdaptativeMenu(array)
 * Fonction qui permet d'afficher, de manière adaptative les différents menus
 * @param array $textToDisplay
 * Paramètre qui récupère le table des menus
 * @return : aucun
*/
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

/** 
 * @function displayRules()
 * Fonction qui permet d'afficher les règles du jeu
 * @return : aucun
*/
function displayRules() {
    $texts = [
        ["LINE",""],
        ["Le but","MIDDLE"],
        ["Le but du jeu est d’aligner avant son adversaire 3 symboles identiques","LEFT"],
        ["horizontalement, verticalement ou en diagonale. Chaque joueur a donc son propre","LEFT"],
        ["symbole, généralement une croix pour l’un et un rond pour l’autre.","LEFT"],
        ["LINE",""],
        ["Déroulement d'une partie","MIDDLE"],
        ["Vous avez le choix entre trois modes de jeu.","LEFT"],
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

/** 
 * @function displayMenuLine(int)
 * Fonction qui permet d'afficher une ligne dans la console de la longueur souhaitée
 * @param int $totalLenght
 * Paramètre qui récupère la longueur de la chaîne souhaitée
 * @return : aucun
*/
function displayMenuLine($totalLenght) {
    echo "+" . str_repeat("-",$totalLenght) . "+" . PHP_EOL;
}
/** 
 * @function randFirst()
 * Fonction qui retourne 1 ou 2 de manière aléatoire
 * @return 1 ou 2 selon le rand
*/
function randFirst(){
    //On retourne de manière aléatoire soit 1 ou 2
    return mt_rand(1,2);
}

/** 
 * @function initGrille()
 * Fonction qui initialise la grille de jeu
 * @return array $grid 
 * la grille initialisée
*/
function initGrille(){
    //Initialisaiton de la grille en 3x3
    $grid = [
        ["1", "2", "3"],
        ["4", "5", "6"],
        ["7", "8", "9"]
    ];  

    return $grid;
}   

/** 
 * @function displayGrille(array)
 * Fonction qui affiche la grille de jeu
 * @param array $grid
 * Paramètre qui récupère la grille de jeu
 * @return : aucun
*/
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
/** 
 * @function fileGrid(Player, int, array)
 * Fonction qui remplie la grille de jeu en fonction du joueur qui joue, le numéro choisit
 * @param Player $playeer
 * Récupère l'objet du joueur en cours
 * @param int $chosenNumber
 * Récupère le chiffre choisit en 1 et 9 dans la grille
 * @param array $grid
 * Récupère la grille en jeu
 * @return array $grid
 * La grille modifiée avec l'entrée du joueur
*/
function fileGrid($player, $chosenNumber,$grid){
    //On met en variable la couleur, le sign : ex : Player 1 --> avec rouge et sign = X
    //$sign_color sera : \033[1;31m X \033[0m
    $sign_color = $player->getColor(). $player->getSign(). RAZ_COLOR;

    //On sélectionne la bonne case qui correspond à la case choisie, et on y met la valeur
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
/** 
 * @function isGridFull(array)
 * Fonction qui indique si la grille est remplie
 * @param array $grid
 * Paramètre qui récupère la grille de jeu
 * @return bool : true ou false selon le résultat (true : remplie, false si non)
*/
function isGridFull($grid){
    //On regarde pour chacune des lignes, et chacune des cases, si elle contient X ou O. Cela permet de savoir si la grille est vide ou non
    foreach($grid as $ligne){
        foreach($ligne as $case){
            //Vérification si la case contient X ou O
            if(!(str_contains($case, "X") || str_contains($case, "O"))){
                return false;
            }
        }
    }
    return true;

}
/** 
 * @function isCaseEmpty(array, int)
 * Fonction qui indique si la case sélectionnée est remplie
 * @param array $grid
 * Paramètre qui récupère la grille de jeu
 * @param int $chosenNumber
 * Récupère l'indice de la case sélectionnée par le joueur
 * @return bool : true ou false selon le résultat (true : remplie, false si non)
*/
function isCaseEmpty($grid, $chosenNumber){
    /*  On vérifie, pour chaque case, si la case rentrée est = à l'index rentré (ex : la case 1 est = à 1, case 2 = 2, etc...)
        Pour chaque vérification, on retourne la valeur retour de la vérification : si la case 1 = à 1, alors true, sinon false
        Si La case 1 est différent de 1, alors ça veut dire qu'un joueur a déjà joué
    */
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
/** 
 * @function setPlayers(bool, bool)
 * Fonction qui retourne 2 objets Player suite à la personnalisation ou non
 * @param bool $bot_on
 * Récupère l'information si le jeu comporte un bot ou non : si oui, le joueur 2 sera un bot
 * @param bool $personnalisation
 * Récupère l'information si l'utilisateur veut personnaliser les joueurs : si oui, on demande des informations, sinon, on met des valeurs par défaut
 * @return Player [$player1, $player2]
 * Retourne une liste de 2 joueurs
*/
function setPlayers($bot_on, $personnalisation) {
    //On regarde si l'utilisateur veut personnaliser
    if($personnalisation){
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
    }else{
        //Paramètres par défaut
        $name1          = "Joueur 1";
        $sign_choice1   = "X";
        $color1         = BLEU;
    }
    
    //On regarde, pour le second joueur, si le bot est activé ou non
    if ($bot_on) {
        //On crée
        $name2          = "Ordinateur";
        $sign_choice2   = ($sign_choice1 === 'X') ? 'O' : 'X';
        $color2         = MAGENTA;
    } else {
        if($personnalisation){
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
        }else{
            //Paramètres par défaut
            $name2          = "Joueur 2";
            $sign_choice2   = "O";
            $color2         = ROUGE;
        }
    }
    //Création des objets players
    $player1 = new Player($name1, $sign_choice1, $color1, 1);
    $player2 = new Player($name2, $sign_choice2, $color2, 2);
    // Retourner les deux joueurs
    return [$player1, $player2];
}
/** 
 * @function switchPlayer(Player, Player, Player)
 * Fonction qui switch le joueur en cours
 * @param Player $player
 * Paramètre qui récupère le joueur qui joue actuellement
 * @param Player $player1
 * Paramètre qui récupère le joueur 1
 * @param Player $player2
 * Paramètre qui récupère le joueur 2
 * @return Player
 * Retourne l'objet Player qui doit être joué
*/
function switchPlayer($player, $player1, $player2){
    return ($player===$player1)?$player2:$player1;
}
/** 
 * @function verificationVictoire(array, Player)
 * Fonction qui vérifie si le joueur qui joue actuellement a gagné la partie
 * @param array $grid
 * Paramètre qui récupère la grille de jeu
 * @param Player $player
 * Paramètre qui récupère le joueur en cours
 * @return bool : true ou false selon le résultat (true : gagné, false si non)
*/
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

/** 
 * @function verificationVictoire(array, Player)
 * Fonction qui permet de détruire les objets au besoin
 * @return : aucun
*/
function destroyObject($player){
    unset($player);
}
?>