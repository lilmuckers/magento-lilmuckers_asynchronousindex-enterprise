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
class Lilmuckers_AsynchronousIndex_Model_Catalog_Index_Observer_Price
    extends Enterprise_Catalog_Model_Index_Observer_Price
{
    /**
     * Task identifiers
     */
    const TASK_PRODUCT_PRICE = 'product_price';
    const TASK_PRICE_RULES   = 'price_rules';
     
    /**
     * The Asyncronous Index Default Helper
     *
     * @var Lilmuckers_AsynchronousIndex_Helper_Data
     */
    protected $_asyncHelper;
    
    /**
     * Initialize factory and config instances
     *
     * @param array $args The setup for the observer
     * 
     * @return void
     */
    public function __construct(array $args = array())
    {
        $this->_asyncHelper = Mage::helper('lilasyncindex');
        parent::__construct($args);
    }

    /**
     * Process catalog inventory item save event
     * Process inventory save event instead catalog product save event due to 
     *  correlation with stock indexer
     *
     * @param Varien_Event_Observer $observer The event observer
     * 
     * @return void
     */
    public function processStockItemSaveEvent(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('enterprise_cataloginventory/index');
        if ($helper->isLivePriceAndStockReindexEnabled()) {
        
            //get the product ID
            $productId = $observer->getEvent()->getItem()->getProductId();
            
            if ($this->_asyncHelper->isEnabled()) {
                //if we're going async - then create the task
                $this->_asyncHelper->queueTask(
                    self::TASK_PRODUCT_PRICE, 
                    array(
                        'product_id' => $productId
                    )
                );
            } else {
                parent::processStockItemSaveEvent($observer);
            }
        }
    }
    
    /**
     * Process product save event (use for processing product delete)
     *
     * @param Varien_Event_Observer $observer The event observer
     * 
     * @return void
     */
    public function processProductSaveEvent(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('enterprise_cataloginventory/index');
        if ($helper->isLivePriceAndStockReindexEnabled()) {
        
            //get the product ID
            $productId = $observer->getEvent()->getProduct()->getId();
            
            if ($this->_asyncHelper->isEnabled()) {
                //if we're going async - then create the task
                $this->_asyncHelper->queueTask(
                    self::TASK_PRODUCT_PRICE, 
                    array(
                        'product_id' => $productId
                    )
                );
            } else {
                parent::processProductSaveEvent($observer);
            }
        }
    }
     
    /**
     * Refresh product prices after apply catalog price rules
     *
     * @param Varien_Event_Observer $observer The event observer
     * 
     * @return void
     */
    public function processCatalogPriceRulesApplyEvent(Varien_Event_Observer $observer)
    {
        //the index helper would be.... helpful
        $helper = Mage::helper('enterprise_cataloginventory/index');
        if ($helper->isLivePriceAndStockReindexEnabled()) {
            if ($this->_asyncHelper->isEnabled()) {
                //if we're going async - then create the task
                $this->_asyncHelper->queueTask(
                    self::TASK_PRICE_RULES
                );
            } else {
                parent::processCatalogPriceRulesApplyEvent($observer);
            }
        }
    }
}
