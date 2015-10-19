<?php
namespace Oldtimers\Mapper;
use Oldtimers\Entity\Car;
/**
 * This can be abstracted into a GenericMapperInterface and any Entry
 * specific mappings can be added into here. This will be covered in 
 * future posts
 * 
 * @author adseipsum
 *
 */
interface CarMapperInterface
{
    public function find($id);
    public function findAll();
    public function save(Car $entry);
    public function remove(Car $entry);
}