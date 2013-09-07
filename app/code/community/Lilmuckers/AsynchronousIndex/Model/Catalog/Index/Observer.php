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
 * The asyncronous index main event observer
 *
 * @category Lilmuckers
 * @package  Lilmuckers_AsynchronousIndex
 * @author   Patrick McKinley <contact@patrick-mckinley.com>
 * @license  MIT http://choosealicense.com/licenses/mit/
 * @link     https://github.com/lilmuckers/magento-lilmuckers_asynchronousindex-enterprise
 */
class Lilmuckers_AsynchronousIndex_Model_Catalog_Index_Observer
    extends Enterprise_Catalog_Model_Index_Observer
{
    /**
     * The task name for the url workers
     */
    const TASK_CATALOG_URL = 'category_url';
    const TASK_PRODUCT_URL = 'product_url';
    
    /**
     * The Asyncronous Index Default Helper
     *
     * @var Lilmuckers_AsynchronousIndex_Helper_Data
     */
    protected $_asyncHelper;
    
    /**
     * Initialize factory and config instances
     *
     * @param array $args The input arguments for the observer
     * 
     * @return void
     */
    public function __construct(array $args = array())
    {
        $this->_asyncHelper = Mage::helper('lilasyncindex');
        parent::__construct($args);
    }
    
    /**
     * Refresh url rewrite for given category
     *
     * @param Varien_Event_Observer $observer The event observer handler
     * 
     * @return void
     */
    public function refreshCategoryUrlRewrite(Varien_Event_Observer $observer)
    {
        if (!$this->_isUpdateOnSaveCategoryUrlRewriteFlag()) {
            return;
        }
        
        //pull the category from the event
        $category = $observer->getEvent()->getCategory();
        
        // don't reindex on save if url key was changed or category was moved 
        // (for avoid 404 pages between cron runs)
        if ($category->getData('save_rewrites_history')) {
            return;
        }
        $affectedIds = $category->getAffectedCategoryIds();
        if (!is_object($category)
            || !$category->getId()
            || (!$category->dataHasChangedFor('url_key') && empty($affectedIds))
        ) {
            return;
        }

        //Check that we've been set to use asyncronous indexing
        if ($this->_asyncHelper->isEnabled()) {
            $this->_queueCategoryRefreshRowAction($category);
        } else {
            $this->_executeCategoryRefreshRowAction($category);
        }
    }
    
    /**
     * Refresh url rewrite for a given product
     *
     * @param Varien_Event_Observer $observer The event observer handler
     * 
     * @return void
     */
    public function refreshProductUrlRewrite(Varien_Event_Observer $observer)
    {
        if (!$this->_isUpdateOnSaveProductUrlRewriteFlag()) {
            return;
        }

        //pull the product from the event
        $product = $observer->getEvent()->getProduct();
        
        if (!is_object($product) || !$product->getId() || !$product->dataHasChangedFor('url_key')) {
            return;
        }

        //Check that we've been set to use asyncronous indexing
        if ($this->_asyncHelper->isEnabled()) {
            $this->_queueProductRefreshRowAction($product);
        } else {
            $this->_executeProductRefreshRowAction($product);
        }
    }
    
    /**
     * Delete url rewrite for a given category
     *
     * @param Varien_Event_Observer $observer The event observer handler
     * 
     * @return void
     */
    public function deleteCategoryUrlRewrite(Varien_Event_Observer $observer)
    {
        if (!$this->_isUpdateOnSaveCategoryUrlRewriteFlag()) {
            return;
        }

        //get the category from the event object
        $category = $observer->getEvent()->getCategory();
        if (!is_object($category) || !$category->getId()) {
            return;
        }

        //Check that we've been set to use asyncronous indexing
        if ($this->_asyncHelper->isEnabled()) {
            $this->_queueCategoryRefreshRowAction($category);
        } else {
            $this->_executeCategoryRefreshRowAction($category);
        }
    }
    
    /**
     * Delete url rewrite for given product
     *
     * @param Varien_Event_Observer $observer The event observer handler
     * 
     * @return void
     */
    public function deleteProductUrlRewrite(Varien_Event_Observer $observer)
    {
        if (!$this->_isUpdateOnSaveProductUrlRewriteFlag()) {
            return;
        }

        //get the product from the event handler
        $product = $observer->getEvent()->getProduct();
        if (!is_object($product) || !$product->getId()) {
            return;
        }

        //Check that we've been set to use asyncronous indexing
        if ($this->_asyncHelper->isEnabled()) {
            $this->_queueProductRefreshRowAction($product);
        } else {
            $this->_executeProductRefreshRowAction($product);
        }
    }
    
    /**
     * Queue category refresh action which refresh url_rewrite index
     *
     * @param Mage_Catalog_Model_Category $category The category to reindex for
     * 
     * @return void
     */
    protected function _queueCategoryRefreshRowAction(Mage_Catalog_Model_Category $category)
    {
        //Queue the task
        $this->_asyncHelper->queueTask(
            self::TASK_CATALOG_URL, 
            array(
                'category_id' => $category->getId() 
            )
        );
    }
    
    /**
     * Queue product refresh action which refresh url_rewrite index
     *
     * @param Mage_Catalog_Model_Product $product The product to reindex
     * 
     * @return void
     */
    protected function _queueProductRefreshRowAction(Mage_Catalog_Model_Product $product)
    {
        //queue the task
        $this->_asyncHelper->queueTask(
            self::TASK_PRODUCT_URL, 
            array(
                'product_id' => $product->getId() 
            )
        );
    }
}
