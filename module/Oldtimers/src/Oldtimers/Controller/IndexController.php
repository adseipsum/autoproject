<?php
namespace Oldtimers\Controller;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Oldtimers\Mapper\CarMapper;
use Oldtimers\Entity\Car;
use Zend\Mvc\Controller\AbstractActionController;
/**
 * Basic action controller which will return json responses instead of
 * html views
 *
 * @author adseipsum
 *
 */
class IndexController extends AbstractActionController
{
    /*
     * If an id is passed to the route, then this will return an array containing only that entry
    * If no id, all entry will be listed
    */
    public function listAction()
    {
//         $id = $this->params()->fromRoute('id');
//         $result = [];
//         if($id != null)
//         {
//             $result = [$this->getCarMapper()->find($id)];
//         }
//         else
//         {
//             $result = $this->getCarMapper()->findAll();
//         }
//         return new JsonModel($result);
        return new ViewModel();
    }

    /*
     * Remove the entry represented by the id given
    */
    public function removeAction()
    {
        $id = $this->params()->fromRoute('id');
        $result = [];
        if($id != null)
        {
            $car = $this->getCarMapper()->find($id);
            if($car != null)
            {
                $this->getCarMapper()->remove($car);
                $result = [$car];
            }
        }
        return new JsonModel($car);
    }

    /*
     * Add a new entry given the POST "text" parameter
    * No validation happens for simplicity sake
    */
    public function addAction()
    {
        /* @var $car Car */
        $car = $this->getServiceLocator()->get('OldtimersCar');
        $car->text = $this->params()->fromPost('text');
        $this->getCarMapper()->save($car);
        return new JsonModel([$car]);
    }

    private $carMapper = null;
    /**
     * @return CarMapper
     */
    protected function getCarMapper()
    {
        if($this->carMapper == null)
            $this->carMapper = $this->getServiceLocator()->get('OldtimersCarMapper');
        return $this->carMapper;
    }
}