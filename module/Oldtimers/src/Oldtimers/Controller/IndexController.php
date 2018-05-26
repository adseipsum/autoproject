<?php

namespace Oldtimers\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;

/**
 * Basic action controller which will return json responses instead of
 * html views
 *
 * @author adseipsum
 */
class IndexController extends AbstractActionController
{

    private $carMapper = null;
    const PAGE_SIZE = 6;

    public function listAction()
    {
        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $make = $entityManager->createQuery('SELECT m.make FROM Oldtimers\Entity\Models m GROUP BY m.make')->getResult();
        $cities = $entityManager->createQuery('SELECT c.name FROM Oldtimers\Entity\Cities c')->getResult();

        $session = New Container('language');

        $view = new ViewModel(array(
        		'make' => $make,
                'cities' => $cities,
                'language' => $session->language,
        ));

        return $view;
    }

    public function getPageAction()
    {
    	$skip = $this->params()->fromQuery('skip');
    	
		$search = array();
    	if($this->params()->fromQuery('make')){
    		$search['make'] = $this->params()->fromQuery('make');
    	}
    	if($this->params()->fromQuery('model')){ 
    		$search['model'] = new \MongoRegex('/.*' . $this->params()->fromQuery('model') . '.*/i');
    	}
    	if($this->params()->fromQuery('from')){
    		$search['from'] = $this->params()->fromQuery('from');
    	}
    	if($this->params()->fromQuery('to')){
    		$search['to'] = $this->params()->fromQuery('to');
    	}
    	if($this->params()->fromQuery('priceFrom')){
    		$search['priceFrom'] = $this->params()->fromQuery('priceFrom');
    	}
    	if($this->params()->fromQuery('priceTo')){
    		$search['priceTo'] = $this->params()->fromQuery('priceTo');
    	}
    	if($this->params()->fromQuery('fuelType')){
    		$search['fuelType'] = $this->params()->fromQuery('fuelType');
    	}
    	if($this->params()->fromQuery('city')){
    		$search['city'] = $this->params()->fromQuery('city');
    	}

    	$result = $this->getCarMapper()->getCarList($search, self::PAGE_SIZE, $skip);

    	$count = $result->count();
    	$resultArray = $result->toArray();
    	//var_dump(array_keys($resultArray));
        $translate = $this->getServiceLocator()->get('ViewHelperManager')->get('translate');

        foreach($resultArray as $car){
            $car->transmission = $translate($car->transmission);
            $car->fuelType = $translate($car->fuelType);
            foreach($car->features as $key => $feature){
                $car->features[$key] = $translate($feature);
            }
        }
    	return new JsonModel(array('cars' => $resultArray, 'count' => $count));
    }

    public function carInfoJsonAction()
    {
        $car = $this->getCarMapper()->find($this->params()->fromQuery('id'));

        $translate = $this->getServiceLocator()->get('ViewHelperManager')->get('translate');

        $car->transmission = $translate($car->transmission);
        $car->fuelType = $translate($car->fuelType);
        foreach($car->features as $key => $feature){
            $car->features[$key] = $translate($feature);
        }

        $result = new JsonModel(array('carInfo' => $car));
        return $result;
    }

    public function advertisementAction()
    {
    	$advertisement = $this->getCarMapper()->find($this->getEvent()->getRouteMatch()->getParam('id'));

        $translate = $this->getServiceLocator()->get('ViewHelperManager')->get('translate');

        $advertisement->transmission = $translate($advertisement->transmission);
        $advertisement->fuelType = $translate($advertisement->fuelType);

        foreach($advertisement->features as $key => $feature){
            $advertisement->features[$key] = $translate($feature);
        }

        $session = New Container('language');

    	if(!$advertisement){ $this->redirect()->toUrl('/'); }

    	$result = new ViewModel(array(
    	    'advertisement' => $advertisement,
            'language' => $session->language
        ));
    	return $result;
    }

    public function testAction()
    {
    	$i = 0;
    	$result = $this->getCarMapper()->findAll();
    	foreach($result as $record){
    		
    		$doc = $this->getCarMapper()->findBy(array(
    				'make' => $record->make, 
    				'model' => $record->model,
    				'year' => $record->year,
    				'mileage' => $record->mileage,
    				'price' => $record->price
    		));

    		if(count($doc) > 1){
    			$i++;
    			$car = $this->getCarMapper()->find($doc[1]->_id);
    				if($car->photos) foreach($car->photos as $photo){
    					unlink(PUBLIC_PATH . '/uploads/' . $car->garageId . '/' . $car->_id . '/' . $photo . '.jpg');
    				}
    			$this->getCarMapper()->remove($car);
    		}
    	}
    	echo $i;
    	die;
    	$entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$distinct = $this->getCarMapper()->getAllGrouped();
    	
    	foreach($distinct as $result){
    		$sql = "INSERT INTO models (make, model) VALUES ('{$result['make']}', '{$result['model']}')";
    		$stmt = $entityManager->getConnection()->prepare($sql);
    		$result = $stmt->execute();
    	}
    	
    	echo 'Done'; 
    	die;
    }

    public function removeJsonAction()
    {
    	
        $id = $this->params()->fromQuery('id');
        var_dump($id);
        $result = [];
        if($id != null){
            $car = $this->getCarMapper()->find($id);
            if($car != null){
                if($car->photos) foreach($car->photos as $photo){
                    unlink(PUBLIC_PATH . '/uploads/' . $car->garageId . '/' . $car->_id . '/' . $photo . '.jpg');
                }
                $this->getCarMapper()->remove($car);
                $result = [$car];
            }
        }
        return new JsonModel(array($car));
    }

    public function newAdvertisementAction()
    {
    	$entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
        if($this->params()->fromPost()){
            $car = $this->getServiceLocator()->get('OldtimersCar');
            $car->garageId = 0;
            $car->date = date('c');
            $car->make = $this->params()->fromPost('make');
            $car->model = $this->params()->fromPost('model');
            $car->modification = $this->params()->fromPost('modification');
            $car->year = $this->params()->fromPost('year');
            $car->transmission = $this->params()->fromPost('transmission');
            $car->driverSide = $this->params()->fromPost('driverSide');
            $car->wheelDrive = $this->params()->fromPost('wheelDrive');
            $car->fuelType = $this->params()->fromPost('fuelType');
            $car->engineCapacity = $this->params()->fromPost('engineCapacity');
            $car->enginePower = $this->params()->fromPost('enginePower');
            $car->color = $this->params()->fromPost('color');
            $car->mileage = $this->params()->fromPost('mileage');
            $car->description = $this->params()->fromPost('description');
            $car->features = $this->params()->fromPost('features');
            $car->price = $this->params()->fromPost('price');
            $car->currency = 'EUR';
            $car->owner = $this->params()->fromPost('owner');

            $photos = implode(',', $this->params()->fromPost('photos'));

            $files = $entityManager->createQuery("SELECT tf.directory, tf.name FROM Oldtimers\Entity\Files tf WHERE tf.id IN ( $photos )")->getResult();
            $filesPathes = array();
            foreach($files as $file){
            	$filesPathes[] = $file['directory'] . '/' . $file['name'];
            }
            //var_dump($tempFileId);
            $car->photos = $filesPathes;
            
            $this->getCarMapper()->save($car);
            $this->redirect()->toUrl('/');
        }

        $cities = $entityManager->createQuery('SELECT c.name FROM Oldtimers\Entity\Cities c')->getResult();
        $make = $entityManager->createQuery('SELECT m.make FROM Oldtimers\Entity\Models m GROUP BY m.make')->getResult();

        $session = New Container('language');

        $view = new ViewModel(array(
                'make' => $make,
        		'cities' => $cities,
        		'carDirectory' => uniqid("car-directory-"),
                'language' => $session->language
        ));
        
        return $view;
    }

    /**
     * @return bool|JsonModel
     */
    public function saveFileAction(){
    	
    	$entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$carDirectory = $this->params()->fromPost('carDirectory') ? $this->params()->fromPost('carDirectory') : uniqid("car-directory-");
    	
    	if($this->getRequest()->getFiles()->toArray()){
    		$adapter = new \Zend\File\Transfer\Adapter\Http();
    		$adapter->setDestination(PUBLIC_PATH . '/uploads');
    		$uploadDir = PUBLIC_PATH . '/uploads/' . $carDirectory;

    		if (!file_exists($uploadDir)){
    			mkdir($uploadDir, 0777, 1);
    		}
    	
    		$info = $adapter->getFileInfo();
    		
    		if ($info) {
    			$fileName = uniqid("file-id-");
    			$filePath = $uploadDir . '/' . $fileName . '.jpg';

    			$adapter->addFilter('Rename', array('target' => $filePath, 'overwrite' => true));
    			$adapter->addValidator('Size', false, array('min' => '10kB', 'max' => '8MB'));
    			$adapter->addValidator('IsImage',  false, 'jpg, jpeg, png');
                $adapter->addValidator('ImageSize', false,  array('minWidth' => 320, 'minHeight' => 200, 'maxWidth' => 6000, 'maxHeight' => 4000));

    			
    			if(!$adapter->receive()){
    				return new JsonModel($adapter->getMessages());
    			}

    			$file = array('directory' => $carDirectory, 'name' => $fileName);
    			$entityManager->getConnection()->insert('files', $file);

    			$this->resizeImage($filePath);
    			
    			return new JsonModel(array('carDirectory' => $carDirectory, 'fileId' => $entityManager->getConnection()->lastInsertId()));
    		}
    	}
    	
    	return false;
    }

    /**
     * @return JsonModel
     */
    public function getModelByMakeIdJsonAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $models = $objectManager->getRepository('\Oldtimers\Entity\Models')->findBy(array('make' => $this->params()->fromQuery('make')), array('model' => 'ASC'));

        $result = new JsonModel(array(
                'models' => $models,
                'success' => true,
        ));

        return $result;
    }

    /**
     * @param $file
     * @return bool
     */
	protected function resizeImage($file) {
	      
		$string             = null;
		$width              = 800;
		$height             = 600;
		$proportional       = false;
		$output             = 'file';
		$delete_original    = true;
		$use_linux_commands = false;
		$quality = 100;
		
	    if ( $height <= 0 && $width <= 0 ) return false;
		if ( $file === null && $string === null ) return false;
	
		# Setting defaults and meta
		$info                         = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
		$image                        = '';
		$final_width                  = 0;
		$final_height                 = 0;
		list($width_old, $height_old) = $info;
		$cropHeight = $cropWidth = 0;
		
		if ( $width_old <= $height_old ) return false;
		
		# Calculating proportionality
		if ($proportional) {
			if      ($width  == 0)  $factor = $height/$height_old;
			elseif  ($height == 0)  $factor = $width/$width_old;
			else                    $factor = min( $width / $width_old, $height / $height_old );
		
			$final_width  = round( $width_old * $factor );
			$final_height = round( $height_old * $factor );
		} else {
			$final_width = ( $width <= 0 ) ? $width_old : $width;
			$final_height = ( $height <= 0 ) ? $height_old : $height;
			$widthX = $width_old / $width;
			$heightX = $height_old / $height;
			 
			$x = min($widthX, $heightX);
			$cropWidth = ($width_old - $width * $x) / 2;
			$cropHeight = ($height_old - $height * $x) / 2;
		}
	
		# Loading image to memory according to type
		switch ( $info[2] ) {
			case IMAGETYPE_JPEG:  
				$file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);  
				break;
			case IMAGETYPE_GIF:   
				$file !== null ? $image = imagecreatefromgif($file)  : $image = imagecreatefromstring($string);  
				break;
			case IMAGETYPE_PNG:   
				$file !== null ? $image = imagecreatefrompng($file)  : $image = imagecreatefromstring($string);  
				break;
			default: 
				return false;
		}
	
		//$image = $this->addWatermark($image);
	
		# This is the resizing/resampling/transparency-preserving magic
		$image_resized = imagecreatetruecolor( $final_width, $final_height );
		if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
			$transparency = imagecolortransparent($image);
			$palletsize = imagecolorstotal($image);
		
			if ($transparency >= 0 && $transparency < $palletsize) {
				$transparent_color  = imagecolorsforindex($image, $transparency);
				$transparency       = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
				imagefill($image_resized, 0, 0, $transparency);
				imagecolortransparent($image_resized, $transparency);
			}
			elseif ($info[2] == IMAGETYPE_PNG) {
				imagealphablending($image_resized, false);
				$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
				imagefill($image_resized, 0, 0, $color);
				imagesavealpha($image_resized, true);
			}
		}
		imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeight, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);
	
	
		# Taking care of original, if needed
		if ( $delete_original ) {
		if ( $use_linux_commands ) exec('rm '.$file);
		else @unlink($file);
		}
	
		# Preparing a method of providing result
		switch ( strtolower($output) ) {
			case 'browser':
				$mime = image_type_to_mime_type($info[2]);
				header("Content-type: $mime");
				$output = NULL;
				break;
			case 'file':
				$output = $file;
				break;
			case 'return':
				return $image_resized;
				break;
			default:
				break;
		}
		
		//$image_resized = $this->addWatermark($image_resized, true);
	
		# Writing image according to type to the output destination and image quality
				switch ( $info[2] ) {
					case IMAGETYPE_GIF:   
						imagegif($image_resized, $output);    
						break;
					case IMAGETYPE_JPEG:  
						imagejpeg($image_resized, $output, $quality);   
						break;
					case IMAGETYPE_PNG:
						$quality = 9 - (int)((0.9*$quality)/10.0);
						imagepng($image_resized, $output, $quality);
						break;
					default: 
						return false;
		}
	
		return true;
	}
  
   /*
   * returns image
   */
	public function addWatermark($image, $bigWatermark = false){
		$stamp = imagecreatefrompng(PUBLIC_PATH . '/img/watermark.png'); //Input the location of your Watermark Here
		if($bigWatermark){
			$stamp = imagecreatefrompng(PUBLIC_PATH . '/img/watermark.png');
		}
		$sx = imagesx($stamp);
		$sy = imagesy($stamp);
	
		// Copy the stamp image onto our photo using the margin offsets and the photo
		// width to calculate positioning of the stamp.
		imagecopy($image, $stamp, imagesx($image) - $sx, imagesy($image) - $sy, 0, 0, imagesx($stamp), imagesy($stamp));
		
		return $image;
	}

    /**
     * @return bool
     */
    public function importAction(){

        include_once PUBLIC_PATH . '/simple_html_dom.php';
        
        $from = $this->getEvent()->getRouteMatch()->getParam('from');
        $to = $this->getEvent()->getRouteMatch()->getParam('to');

        for($advertisementId = $from; $advertisementId <= $to; $advertisementId++){
            
            $html = file_get_html('http://www.autodiler.me/auto_oglasi_auto/' . $advertisementId);
        
            //if sold
            foreach($html->find('img[alt=vozilo-prodato]') as $element){
                if($element->tag){
                    $html->clear();
                    continue 2;
                }
            }
        
            $array = array();
        
            foreach($html->find('div[class=date]') as $element){
                $array['popularity'] = (int) $element->children(0)->children(2)->innertext;
                $element->children(0)->outertext = '';
                $array['date'] = \DateTime::createFromFormat('d.m.Y.', str_replace('Datum: ', '', $element->innertext));
            }
        
            if(empty($array['date'])){
                $html->clear();
                continue;
            }
        
            if($array['date']->diff(new \DateTime())->d >= 30){
                $html->clear();
                continue;
            }
        
            foreach($html->find('div[class=lft_auto_desc]') as $element){
                var_dump($advertisementId);
                //var_dump($element->innertext);
        
                if(substr_count($element->innertext, 'Kategorija:') || substr_count($element->innertext, 'Tip bicikla:')){
                    $html->clear();
                    continue 2;
                }
        
                if(
                !substr_count($element->innertext, 'Kabriolet') &&
                !substr_count($element->innertext, 'Karavan') &&
                !substr_count($element->innertext, 'Kombi') &&
                !substr_count($element->innertext, 'Kupe') &&
                !substr_count($element->innertext, 'Limuzina') &&
                !substr_count($element->innertext, 'Mali gradski auto') &&
                !substr_count($element->innertext, 'Minivan (MPV)') &&
                !substr_count($element->innertext, 'Old timer') &&
                !substr_count($element->innertext, 'Pick-up') &&
                !substr_count($element->innertext, 'Terenac')
                ){
                    $html->clear();
                    continue 2;
                }
        
                $array['price'] = preg_replace("/[^0-9]/", "", $element->children(1)->children(0)->innertext);
                $array['year'] = preg_replace("/[^0-9]/", "", $element->children(4)->innertext);
                $array['mileage'] = preg_replace("/[^0-9]/", "", $element->children(7)->innertext);
                $array['engineCapacity'] = $element->children(10)->innertext;
                $array['kW'] = preg_replace("/[^0-9]/", "", $element->children(13)->innertext);
                $array['bodyType'] = $element->children(16)->innertext;
                $array['transmission'] = $element->children(19)->innertext;
                $array['fuelType'] = $element->children(25)->innertext;
                $array['wheelDrive'] = $element->children(28)->innertext;
                $array['driverSide'] = $element->children(40)->innertext;
                $array['description'] = $element->children(44)->children(2)->innertext;
        
            }
        
            foreach($html->find('ul[class=dodatna_oprema]') as $element)
            if($element) foreach($element->children() as $value)
                $array['features'][] = str_replace('- ', '', $value->innertext);
        
            foreach($html->find('div[class=heading2 leftMargin15px]') as $element)
                $markModel = explode(' - ', str_replace('Prodajem: ', '', $element->children(0)->innertext));
            $array['make'] = $markModel[0];
            $array['model'] = $markModel[1];

//            if($array['make'] != 'Mercedes Benz'){
//                $html->clear();
//                continue;
//            }
        
            foreach($html->find('div[class=rgt_auto_desc]') as $element)
                $array['owner']['name'] = $element->children(1)->innertext;
            $array['owner']['city'] = $element->children(4)->innertext;
            $array['owner']['phoneNumber'] = $element->children(7)->innertext;
            $array['owner']['email'] = $element->children(10)->children(0)->innertext;
        
        	if(empty($array['owner']['email']) && empty($array['owner']['phoneNumber'])){
                $html->clear();
                continue;
            }
        
            if($array){
                $car = $this->getServiceLocator()->get('OldtimersCar');
                $car->uniqueId = md5($array['make'] . $array['year'] . $array['mileage']);
                $car->date = $array['date']->format('d.m.Y');
                $car->popularity = $array['popularity'];
                $car->garageId = 0;
                $car->make = $array['make'];
                $car->model = $array['model'];
                $car->modification = '';
                $car->year = $array['year'];
                $car->transmission = $array['transmission'];
                $car->driverSide = $array['driverSide'];
                $car->wheelDrive = $array['wheelDrive'];
                $car->fuelType = $array['fuelType'];
                $car->engineType = '';
                $car->engineCapacity = $array['engineCapacity'];
                $car->color = '';
                $car->kW = $array['kW'];
                $car->mileage = $array['mileage'];
                $car->description = $array['description'];
                $car->features = !empty($array['features']) ? $array['features'] : array();
                $car->price = $array['price'];
                $car->currency = 'EUR';
                $car->owner = $array['owner'];
                $car->autodealerId = $advertisementId;
                $this->getCarMapper()->save($car);

                $carDirectory = uniqid("car-directory-");
        
                if($car->_id){
                    $uploadDir = PUBLIC_PATH . '/uploads/' . $carDirectory;

                    if (!file_exists($uploadDir)){
                        mkdir($uploadDir, 0777, 1);
                    }
        
                    for($i = 0; $i <= 12; $i++){
                        $filePath = $uploadDir . "/$i.jpg";
                        $file = @file_get_contents("http://www.autodiler.me/images/oglasi/$advertisementId/$i.jpg");
        
                        if($file){
                            file_put_contents($filePath, $file);
                            if($this->resizeImage($filePath)){
                            	$car->photos[] = $carDirectory . '/' . $i;
                            }else{
                            	unlink($filePath);
                            }
                        }
                    }
                    
                    if($car->photos >= 2){
                    	$this->getCarMapper()->save($car);
                    }else{
                    	$this->getCarMapper()->remove($car);
                    }
                }
            }
            sleep(5);
            $html->clear();
            unset($html);
        }
        return true;
    }

    /**
     * @return JsonModel
     */
    public function searchTagAction(){
    	$tag = $this->params()->fromQuery('tag');
    	return new JsonModel(array($tag));
    }

    /**
     * @return JsonModel
     */
    public function languageAction()
    {
        $session = new Container('language');
        $language = $this->params()->fromPost('language');
        $config = $this->serviceLocator->get('Config');
        if (isset($config['translator']['locale']['available'][$language])) {
            $session->language = $language;
            $this->serviceLocator->get('MvcTranslator')->setLocale($session->language);
        }

        $result = new JsonModel(array(
            'success' => true,
        ));

        return $result;
    }
    
    /**
     * @return CarMapper
     */
    protected function getCarMapper()
    {
        if($this->carMapper == null)
            $this->carMapper = $this->getServiceLocator()->get('OldtimersCarMapper');
        return $this->carMapper;
    }

    /**
     * @return ViewModel
     */
    public function formAction()
    {
        return new ViewModel();
    }
}

