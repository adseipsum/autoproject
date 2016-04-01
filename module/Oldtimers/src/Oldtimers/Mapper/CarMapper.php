<?php
namespace Oldtimers\Mapper;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Oldtimers\Entity\Car;
/**
 * Basic Mapping class for Doctrine. This can be abstracted
 * into a GenericMapper which will be covered in future blog posts.
 * 
 * All methods are pretty self explanatory
 * 
 * @author adseipsum
 *
 */
class CarMapper implements CarMapperInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $documentManager;
    
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $repository;
    
	public function __construct(ObjectManager $documentManager,
				    ObjectRepository $repository)
	{
		$this->documentManager = $documentManager;
		$this->repository = $repository;
	}
	
	public function findBy($criteria, $sort = null, $limit = null, $skip = null){
		return $this->repository->findBy($criteria, $sort, $limit, $skip);
	}
	
	public function find($id)
	{
	    return $this->repository->find($id);
	}
	
	public function findAll()
	{
	    return $this->repository->findAll();
	}
	
	public function save(Car $entity)
	{
	    $this->documentManager->persist($entity);
	    $this->documentManager->flush();
	}
	
	public function remove(Car $entity)
	{
	    $this->documentManager->remove($entity);
	    $this->documentManager->flush();
	}
	
	public function getRepository(){
		return $this->repository;
	}
	
	public function getCarList($search, $pageSize, $skip){
		$qb = $this->documentManager->createQueryBuilder('Oldtimers\Entity\Car');
		
		if(!empty($search['make'])){
			$qb->field('make')->equals($search['make']);
		}
		
		if(!empty($search['model'])){
			$qb->field('model')->equals($search['model']);
		}
		
		if(!empty($search['from'])){
			$qb->field('year')->gte((int)$search['from']);
		}
		
		if(!empty($search['to'])){
			$qb->field('year')->lte((int)$search['to']);
		}
		
		if(!empty($search['priceFrom'])){
			$qb->field('price')->gte((int)$search['priceFrom']);
		}
		
		if(!empty($search['priceTo'])){
			$qb->field('price')->lte((int)$search['priceTo']);
		}
		
		if(!empty($search['fuelType'])){
			$qb->field('fuelType')->equals($search['fuelType']);
		}
		
		if(!empty($search['city'])){
			$qb->field('owner.1')->equals($search['city']);
		}
		$qb
			->sort(array('date' => '-1', 'make' => '1'))
			->skip($skip)
			->limit($pageSize);
		
		$query = $qb->getQuery();

		return $query->execute();
	}
	
	public function getAllGrouped(){
		$qb = $this->documentManager->createQueryBuilder('Oldtimers\Entity\Car');
		$qb->group(array('make' => 1, 'model' => 1), array('count' => 0));
		$qb->reduce('function (obj, prev) { prev.count++; }');
		$query = $qb->getQuery();
	
		return $query->execute();
	}
}