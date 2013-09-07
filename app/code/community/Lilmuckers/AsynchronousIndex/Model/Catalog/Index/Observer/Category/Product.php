<?php
/**
 * Magento Enterprise Asyncronous Indexing Module
 *
 * @category    Lilmuckers
 * @package     Lilmuckers_AsynchronousIndex
 * @copyright   Copyright (c) 2013 Patrick McKinley (http://www.patrick-mckinley.com)
 * @license     http://choosealicense.com/licenses/mit/
 */

/**
 * The asyncronous category product index observer
 *
 * @category Lilmuckers
 * @package  Lilmuckers_AsynchronousIndex
 * @author   Patrick McKinley <contact@patrick-mckinley.com>
 * @license  MIT http://choosealicense.com/licenses/mit/
 * @link     https://github.com/lilmuckers/magento-lilmuckers_asynchronousindex-enterprise
 */
class Lilmuckers_AsynchronousIndex_Model_Catalog_Index_Observer_Category_Product
    extends Enterprise_Catalog_Model_Index_Observer_Category_Product
{
    /**
     * The worker identifiers for the category products
     */
    const TASK_CATEGORY_PRODUCTS = 'category_products_category';
    const TASK_CATEGORY_PRICE    = 'category_product_price';
    const TASK_CATEGORY_PRODUCT  = 'category_product_category';
    
    /**
     * The Asyncronous Index Default Helper
     *
     * @var Lilmuckers_AsynchronousIndex_Helper_Data
     */
    protected $_asyncHelper;
    
    /**
     * Constructor with parameters
     * Array of arguments with keys
     *  - 'factory' Mage_Core_Model_Factory
     *
     * @param array $args Arguments to instantiate the object
     * 
     * @return void
     */
    public function __construct(array $args = array())
    {
        $this->_asyncHelper = Mage::helper('lilasyncindex');
        
        parent::__construct($args);
    }
    
    /**
     * Process category/product refresh upon category save event
     *
     * @param Varien_Event_Observer $observer The event observer
     * 
     * @return Lilmuckers_AsynchronousIndex_Model_Catalog_Index_Observer_Category_Product
     */
    public function processCategorySaveEvent(Varien_Event_Observer $observer)
    {
        //get the category from the event handler
        $category   = $observer->getEvent()->getCategory();
        $categoryId = $category->getId();
        
        //Ensure we're not acting on the ultimate root category
        if ( !$this->_isLiveCategoryProductReindexEnabled() 
            || $categoryId == Mage_Catalog_Model_Category::TREE_ROOT_ID
        ) {
            return $this;
        }
        
        //instantiate the path IDS array
        $parentPathIds = array();
        
        //get path ids from the parent category
        $parent = $observer->getEvent()->getParent();
        if ($parent instanceof Mage_Catalog_Model_Category) {
            $parentPathIds = $parent->getPathIds();
        }
        
        //generate the path IDs to run on
        $_pathIds = array_merge($parentPathIds, $category->getPathIds(), array($categoryId));
        
        if ($this->_asyncHelper->isEnabled()) {
            //Queue the task
            $this->_asyncHelper->queueTask(
                self::TASK_CATEGORY_PRODUCTS, 
                array(
                    'category_ids' => $_pathIds 
                )
            );
        } else {
            //Category/Product refresh
            $client = $this->_getClient('catalog_category_product_cat');
            $client->execute(
                'enterprise_catalog/index_action_catalog_category_product_category_refresh_row', 
                array(
                'value' => $_pathIds
                )
            );
        }
        
        return $this;
    }

    /**
     * Execute price and category product index operations.
     *
     * @param Varien_Event_Observer $observer the event observer
     * 
     * @return void
     */
    public function processUpdateWebsiteForProduct(Varien_Event_Observer $observer)
    {
        if ($this->_isLiveCategoryProductReindexEnabled()) {
            if ($this->_asyncHelper->isEnabled()) {
                //Queue the task
                $this->_asyncHelper->queueTask(
                    self::TASK_CATEGORY_PRICE
                );
            } else {
                parent::processUpdateWebsiteForProduct($observer);
            }
        }
    }
    
    /**
     * Process category/product refresh upon product save event
     *
     * @param Varien_Event_Observer $observer The event observer
     * 
     * @return Enterprise_Catalog_Model_Index_Observer_Category_Product
     */
    public function processProductSaveEvent(Varien_Event_Observer $observer)
    {
        if ($this->_isLiveCategoryProductReindexEnabled()) {
            if ($this->_asyncHelper->isEnabled()) {
                //Queue the task
                $this->_asyncHelper->queueTask(
                    self::TASK_CATEGORY_PRODUCT,
                    array(
                        'product_id' => $observer->getEvent()->getProduct()->getId()
                    )
                );
            } else {
                parent::processProductSaveEvent($observer);
            }
        }


        return $this;
    }

}
