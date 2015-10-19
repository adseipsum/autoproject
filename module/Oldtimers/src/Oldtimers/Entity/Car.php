<?php
namespace Oldtimers\Entity;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
/**
 * 
 * @ODM\Document(collection="cars")
 * @author adseipsum
 */
class Car
{
    /**
     * @ODM\Id
     */
    public $_id;
    
    /**
     * @ODM\Field(type="string")
     */
    public $text;
}