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
     * @ODM\Field(type="integer")
     */
    public $garageId;
    
    /**
     * @ODM\Field(type="integer")
     */
    public $price;
    
    /**
     * @ODM\Field(type="string")
     */
    public $currency;
    
    /**
     * @ODM\Field(type="string")
     */
    public $brand;
    
    /**
     * @ODM\Field(type="string")
     */
    public $model;
    
    /**
     * @ODM\Field(type="integer")
     */
    public $year;
    
    /**
     * @ODM\Field(type="string")
     */
    public $transmission;
    
    /**
     * @ODM\Field(type="string")
     */
    public $driverSide;
    
    /**
     * @ODM\Field(type="string")
     */
    public $wheelDrive;
    
    /**
     * @ODM\Field(type="string")
     */
    public $fuel;
    
    /**
     * @ODM\Field(type="string")
     */
    public $engineType;
    
    /**
     * @ODM\Field(type="integer")
     */
    public $engineCapacity;
    
    /**
     * @ODM\Field(type="string")
     */
    public $color;
    
    /**
     * @ODM\Field(type="integer")
     */
    public $mileage;
    
    /**
     * @ODM\Field(type="array")
     */
    public $owner = array();
    
    /**
     * @ODM\Field(type="array")
     */
    public $features = array();
    
    /**
     * @ODM\Field(type="array")
     */
    public $photos = array();
    
    /**
     * @ODM\Field(type="string")
     */
    public $description;
 
}