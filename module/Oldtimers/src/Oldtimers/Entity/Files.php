<?php
namespace Oldtimers\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="files")
 * @property int $id
 * @property string $make
 * @property string $model
 */
class Files
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
    public $directory;
    
    /**
     * @ORM\Column(type="string")
     */
    public $name;

    public function getId(){
        return $this->id;
    }
    
    public function getDirectory() {
        return $this->directory;
    }
    
    public function getName() {
    	return $this->name;
    }
    
    public function jsonSerialize(){
        return array(
            'id'      		=> $this->getId(),
            'directory'     => $this->getDirectory(),
        	'name'     		=> $this->getName(),
        );
    }
    
}

