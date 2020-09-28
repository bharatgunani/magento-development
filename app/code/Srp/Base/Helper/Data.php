<?php
namespace Srp\Base\Helper;
use Magento\Framework\App\Action\Action; 
class Data extends \Magento\Framework\App\Helper\AbstractHelper 
{
	protected $catalogHelper;
	protected $_productCollectionFactory;
	protected $_categoryFactory;
    protected $productRepository;
    protected $taxCalculation;
    protected $scopeConfig;
    protected $addressRepository;
    protected $_customerSession;
    protected $dataObjectHelper;
    protected $_filesystem ;
    protected $_imageFactory;
    protected $_storeManager;

	public function __construct(
	    \Magento\Framework\App\Helper\Context $context,
	    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    	\Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculation,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
	    \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Framework\Filesystem $filesystem,    
        \Magento\Store\Model\StoreManagerInterface $storeManager,     
        \Magento\Framework\Image\AdapterFactory $imageFactory,
		\Magento\Framework\App\Request\Http $request
	) {
	    parent::__construct($context);
        $this->_customerSession = $customerSession;
	    $this->catalogHelper=$catalogHelper;
	    $this->_categoryFactory = $categoryFactory;
    	$this->_productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
        $this->taxCalculation = $taxCalculation;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->addressRepository = $addressRepository;
        $this->_filesystem = $filesystem;               
        $this->_imageFactory = $imageFactory;  
        $this->_storeManager = $storeManager;
		$this->request = $request;
	}

    public function getCustomerSessionData(){
       
       /* if($this->_customerSession->isLoggedIn()) {
           return $this->_customerSession;
        }else{
            return false;
        }*/
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $customerSession = $objectManager->get('Magento\Customer\Model\Session');
    }

	public function getIncludingPrice($productId){
       
       	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->get('Magento\Catalog\Model\Product')->load($productId);
		
		return $this->catalogHelper->getTaxPrice($product, $product->getFinalPrice(), true);
        
    }

    public function getAddressObj($addressId){
       
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get('Magento\Customer\Model\AddressFactory')->create()->load($addressId);
    }

    public function getCartInfo(){
       
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get('\Magento\Checkout\Model\Cart');  
    }

    public function getOffpercentage($productId,$offerInclPrice)
    {	
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->get('Magento\Catalog\Model\Product')->load($productId);

    	$regularPrice=$product->getPrice();

    	$difference=$regularPrice - $offerInclPrice;

    	$percentage= ($difference/$regularPrice) * 100;

    	return number_format($percentage,2).'%';

    }

    public function getAddressById($customeraddressId)
    {
        $shippingAddress =$this->addressRepository->getById($customeraddressId);
        return $shippingAddress;

    }

    public function getPriceInclAndExclTax($productId)
    {
        $product = $this->productRepository->getById($productId);

        if ($taxAttribute = $product->getCustomAttribute('tax_class_id')) {
            // First get base price (=price excluding tax)
            $productRateId = $taxAttribute->getValue();
            $rate = $this->taxCalculation->getCalculatedRate($productRateId);

            if ((int) $this->scopeConfig->getValue(
                'tax/calculation/price_includes_tax', 
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE) === 1
            ) {
                // Product price in catalog is including tax.
                $priceExcludingTax = $product->getPrice() / (1 + ($rate / 100));
            } else {
                // Product price in catalog is excluding tax.
                $priceExcludingTax = $product->getPrice();
            }

            $priceIncludingTax = $priceExcludingTax + ($priceExcludingTax * ($rate / 100));

            return [
                'incl' => $priceIncludingTax,
                'excl' => $priceExcludingTax
            ];
        }

        return false;
    }

	public function getTaxLabelInclExcl($productId)
    {
        $product = $this->productRepository->getById($productId);
		$priceIncludingTaxLabel = '';
		
		$route      = $this->request->getRouteName();
        $controller = $this->request->getControllerName();
        $action     = $this->request->getActionName();

        $fullpath =  $route . '_' . $controller . '_' . $action;
		
		if($fullpath == 'catalog_product_view') {
			if ($taxAttribute = $product->getCustomAttribute('tax_class_id')) {
				if (
					( (int) $this->scopeConfig->getValue('tax/calculation/price_includes_tax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) === 1)
					&& 
					( (int) $this->scopeConfig->getValue('tax/display/type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) === 2 )
				) {
					$priceIncludingTaxLabel = 'Tax included';
				} else {
					//$priceIncludingTaxLabel = 'Tax not included';
				}
			}
		}
		
		return $priceIncludingTaxLabel;
    }
    
    public function chkCustomerLoggedin()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        if($customerSession->isLoggedIn()) {
           return true;
        }
        else
        {
            return false;
        }
    }

    public function getProductTypebyId($productId)
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$product = $objectManager->get('Magento\Catalog\Model\Product')->load($productId);
		
		if($product)		
		{
			return $product->getTypeId();
		}
		else
		{
			return false;
		}

    }

    public function chkConfigProductbyID($productId)
    {
    	if($this->getProductTypebyId($productId))
    	{

    		if($this->getProductTypebyId($productId) == "configurable")
    		{
    			return true;	
    		}
    		else
    		{
    			return false;
    		}
    		
    	}
    	else
    	{
    		return false;
    	}
    }

    public function getCurrentCategory()
    {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	    $category = $objectManager->get('Magento\Framework\Registry')->registry('current_category');//get current category
    
	    if(!empty($category))
	    {
	    	return $category->getId();	
	    }
	    else
	    {
	    	return false;
	    }
	    
    }

    public function loadCategory($id)
    {
        $category = $this->_categoryFactory->create()->load($id);
        return $category;
    }

    public function getCategoryProduct()
    {
    	if($this->getCurrentCategory())
    	{
    		$categoryId = $this->getCurrentCategory();
		    $category = $this->_categoryFactory->create()->load($categoryId);
		    $collection = $this->_productCollectionFactory->create();
		    $collection->addAttributeToSelect('*');
		    $collection->addCategoryFilter($category);
		    $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
		    $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
		    return $collection;
    	}
    	else
    	{
    		return false;
    	}
    }

    public function getProductQty($product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
        return $StockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
    }


    public function getCollectionByOption($product)
    {
        $obj = \Magento\Framework\App\ObjectManager::getInstance();

        $repository = $obj->create('Magento\Catalog\Model\ProductRepository');
        $product1 = $repository->getById($product->getId());

        $data = $product->getTypeInstance()->getConfigurableOptions($product1);

        $configProduct = $obj->create('Magento\Catalog\Model\Product')->load($product->getId());

        $childArray=array();

        $_children = $configProduct->getTypeInstance()->getUsedProducts($configProduct);

        foreach ($_children as $child){
            $childArray[]=$child->getSku();
        }
        
        $options = array();

        foreach($data as $attr){
          foreach($attr as $p){
            if(in_array($p['sku'],$childArray))
            {
                $options[$p['sku']][$p['attribute_code']] = $p['option_title'];    
            }
          }
        }

        $attributeValueArray = array();
        $optionByValues=array();

        foreach($options as  $sku => $valueArray) {
            
            foreach($valueArray as $key => $value)
            {
                if($key == "wwreservation")
                {
                    if(!in_array($value,$attributeValueArray))
                    {
                        $attributeValueArray[]=$value;
                        $optionByValues[$value]=array($sku);
                    }
                    else
                    {
                        array_push($optionByValues[$value],$sku);
                    }
                }

            }
            
        }

        return $optionByValues;
    }

    public function getProductBysku($productSku)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 

        $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
        $productObj = $productRepository->get($productSku);

        return $productObj;
    }

    public function hasCustomOptions($productSku)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 

        $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');

        $hasOptions = false;
        try {
            $productObj = $productRepository->get($productSku);
            $hasOptions = $productObj->hasOptions();
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
        return $hasOptions;
    }

    public function getCursesName($optionValue)
    {
        $timestamp = strtotime($optionValue);
                
        return __(date("l j F Y", $timestamp));
 
    }

    public function getCursesProductQty($productSku)
    {
        $product=$this->getProductBysku($productSku);
        return $this->getProductQty($product);

    }

    public function getcursesOptionvalue($productSku)
    {   
        $product=$this->getProductBysku($productSku);

        return $product->getResource()->getAttribute('betaling')->getFrontend()->getValue($product);

    }   

    public function getCursesPrice($productSku)
    {
         $product=$this->getProductBysku($productSku);
         return $product->getPrice();
    }

    public function resize($image, $width = null, $height = null)
    {
        $absolutePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/category/').$image;
        if (!file_exists($absolutePath)) return false;
        $imageResized = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('resized/'.$width.'/').$image;
        if (!file_exists($imageResized)) { // Only resize image if not already exists.
            //create image factory...
            $imageResize = $this->_imageFactory->create();         
            $imageResize->open($absolutePath);
            $imageResize->constrainOnly(TRUE);         
            $imageResize->keepTransparency(TRUE);         
            $imageResize->keepFrame(FALSE);         
            $imageResize->keepAspectRatio(TRUE);         
            $imageResize->resize($width,$height);  
            //destination folder                
            $destination = $imageResized ;    
            //save image      
            $imageResize->save($destination);         
        } 
        $resizedURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'resized/'.$width.'/'.$image;
            return $resizedURL;
    } 

    public function getFileExists($image)
    {
        $absolutePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('flag/').$image;
        if (file_exists($absolutePath)) 
        {
            return true;   
        }
        return false;
    }

    public function getMediaUrl(){
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function resizePlaceHolder($image, $width = null, $height = null)
    {
        $absolutePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/placeholder/').$image;
        if (!file_exists($absolutePath)) return false;
        $imageResized = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('resized/'.$width.'/').$image;
        if (!file_exists($imageResized)) { // Only resize image if not already exists.
            //create image factory...
            $imageResize = $this->_imageFactory->create();         
            $imageResize->open($absolutePath);
            $imageResize->constrainOnly(TRUE);         
            $imageResize->keepTransparency(TRUE);         
            $imageResize->keepFrame(FALSE);         
            $imageResize->keepAspectRatio(TRUE);         
            $imageResize->resize($width,$height);  
            //destination folder                
            $destination = $imageResized ;    
            //save image      
            $imageResize->save($destination);         
        } 
        $resizedURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'resized/'.$width.'/'.$image;
            return $resizedURL;
    } 

    public function getBaseUrl() {
        return $this->_storeManager->getStore()->getBaseUrl();
    }



}

