<?php
/**
* Classe qui définie l'objet joueur. Celui-ci dispose de différents attributs tels que : 
* @param string $name 
* Le nom du joueur souhaité
* @param string $sign
* Signe du joueur : X / O
* @param string $color
* code de la couleur pour le signe à afficher
* @param int $id
* Id du joueur afin de savoir qui est le joueur 1 et joueur 2 (au besoin)
* @param int $nbWin
* nombre de victoire pour réaliser un compte en fin de jeu
*
**/

class Player{
    //Initialisation des attributs
    private $name;
    private $sign;
    private $color;
    private $id;
    private $nbWin;

    //Constructeur de l'object Player
    public function __construct($name, $sign, $color, $id){
        $this->name     = $name;
        $this->sign     = $sign;
        $this->color    = $color;
        $this->id       = $id;
        $this->nbWin    = 0;
    }
    //Destruction
    public function __destruct(){
    }
    
    //Fonction pour récupérer les les attributs
    public function getName(){
        return $this->name;
    }
    public function getSign(){
        return $this->sign;
    }
    public function getColor(){
        return $this->color;
    }
    public function getnbWin(){
        return $this->nbWin;
    }
    public function getId(){
        return $this->id;
    }
    //Récupérer les infos globales (hormis les couleurs)
    public function getInfo(){
        return $this->name." ".$this->sign." ".$this->nbWin.$this->id;
    }
    //Incrémentation du nombre de victoires
    public function incrementWin(){
        $this->nbWin++;
    }
}

?>