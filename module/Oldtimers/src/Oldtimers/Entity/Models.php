<?php
namespace Oldtimers\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="models")
 * @property int $id
 * @property string $make
 * @property string $model
 */
class Models
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(type="string")
     */
    public $make;

    /**
     * @ORM\Column(type="string")
     */
    public $model;
    
    public function getId(){
        return $this->id;
    }
    
    public function getMake() {
        return $this->make;
    }
    
    public function getModel() {
        return $this->model;
    }

    public function getYear() {
        return $this->year;
    }

    public function jsonSerialize(){
        return array(
            'id'      => $this->getId(),
            'make'     => $this->getMake(),
            'model'     => $this->getModel(),
            'year'     => $this->getYear(),
        );
    }
    
}

