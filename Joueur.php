<?php
/**
* Classe qui définie l'objet joueur. Celui-ci dispose de différents attributs tels que : 
* @param string $name 
* Le nom du joueur souhaité
* @param string $sign
* Signe du joueur : X / O
* @param string $color
* code de la couleur pour le signe à afficher
*
**/

class Player{
    public $name;
    public $sign;
    public $color;
    public $id;

    public function __construct($name, $sign, $color, $id){
        $this->name     = $name;
        $this->sign     = $sign;
        $this->color    = $color;
        $this->id       = $id;
    }

    public function __destruct(){
    }
    
    public function getName(){
        return $this->name;
    }
    public function getSign(){
        return $this->sign;
    }
    public function getColor(){
        return $this->color;
    }
}

?>