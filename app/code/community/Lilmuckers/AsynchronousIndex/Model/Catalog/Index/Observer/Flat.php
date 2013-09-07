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
 * The asyncronous index flat catalog observer
 *
 * @category Lilmuckers
 * @package  Lilmuckers_AsynchronousIndex
 * @author   Patrick McKinley <contact@patrick-mckinley.com>
 * @license  MIT http://choosealicense.com/licenses/mit/
 * @link     https://github.com/lilmuckers/magento-lilmuckers_asynchronousindex-enterprise
 */
class Lilmuckers_AsynchronousIndex_Model_Catalog_Index_Observer_Flat
    extends Enterprise_Catalog_Model_Index_Observer_Flat
{
    /**
     * Flat index worker identifiers
     */
    const TASK_CATEGORY_FLAT     = 'category_flat';
    const TASK_PRODUCT_FLAT      = 'product_flat';
    const TASK_PRODUCT_CHANGELOG = 'category_changelog';

    /**
     * The Asyncronous Index Default Helper
     *
     * @var Lilmuckers_AsynchronousIndex_Helper_Data
     */
    protected $_asyncHelper;
    
    /**
     * Initialize factory and config instances
     * 
     * @return void
     */
    public function __construct()
    {
        $this->_asyncHelper = Mage::helper('lilasyncindex');
    }

    /**
     * Process category save event
     *
     * @param Varien_Event_Observer $observer The event observer
     * 
     * @return void
     */
    public function processCategorySaveEvent(Varien_Event_Observer $observer)
    {
        //check that update-on-save event is enabled
        if ($this->_isLiveCategoryReindexEnabled()) {
            if ($this->_asyncHelper->isEnabled()) {
                $this->_queueCategoryFlatRefreshRowAction($observer->getEvent()->getCategory());
            } else {
                //call the original event
                parent::processCategorySaveEvent($observer);
            }
        }
    }
    
    /**
     * Process product save event
     *
     * @param Varien_Event_Observer $observer The event observer
     * 
     * @return void
     */
    public function processProductSaveEvent(Varien_Event_Observer $observer)
    {
        //check that update-on-save event is enabled
        if ($this->_isLiveProductReindexEnabled()) {
            if ($this->_asyncHelper->isEnabled()) {
                $this->_queueProductFlatRefreshRowAction($observer->getEvent()->getProduct());
            } else {
                //call the original event
                parent::processProductSaveEvent($observer);
            }
        }
    }
    
    /**
     * Process category move event
     *
     * @param Varien_Event_Observer $observer The event observer
     * 
     * @return void
     */
    public function processCategoryMoveEvent(Varien_Event_Observer $observer)
    {
        //check that update-on-save event is enabled
        if ($this->_isLiveProductReindexEnabled()) {
            if ($this->_asyncHelper->isEnabled()) {
                $this->_queueProductFlatRefreshChangelogAction();
            } else {
                //call the original event
                parent::processCategoryMoveEvent($observer);
            }
        }
    }
    
    /**
     * Queue category flat index update
     *
     * @param Mage_Catalog_Model_Category $category The category to index
     * 
     * @return void
     */
    protected function _queueCategoryFlatRefreshRowAction(Mage_Catalog_Model_Category $category)
    {
        //Queue the task
        $this->_asyncHelper->queueTask(
            self::TASK_CATEGORY_FLAT, 
            array(
                'category_id' => $category->getId() 
            )
        );
    }
    
    /**
     * Queue product flat index update
     *
     * @param Mage_Catalog_Model_Product $product The product to index
     * 
     * @return void
     */
    protected function _queueProductFlatRefreshRowAction(Mage_Catalog_Model_Product $product)
    {
        //Queue the task
        $this->_asyncHelper->queueTask(
            self::TASK_PRODUCT_FLAT, 
            array(
                'product_id' => $product->getId() 
            )
        );
    }
    
    /**
     * Queue product flat index update
     *
     * @return void
     */
    protected function _queueProductFlatRefreshChangelogAction()
    {
        //Queue the task
        $this->_asyncHelper->queueTask(
            self::TASK_PRODUCT_CHANGELOG
        );
    }
}
