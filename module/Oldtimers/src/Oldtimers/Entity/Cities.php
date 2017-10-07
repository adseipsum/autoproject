<?php
namespace Oldtimers\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="cities")
 * @property int $id
 * @property string $name
 */
class Cities
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
    public $name;

    
    public function getId(){
        return $this->id;
    }
    
    public function getName() {
        return $this->name;
    }

    public function jsonSerialize(){
        return array(
            'id'      => $this->getId(),
            'name'     => $this->getName(),
        );
    }
    
}

