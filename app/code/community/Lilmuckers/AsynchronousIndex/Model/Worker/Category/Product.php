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
 * The asyncronous category product index worker
 *
 * @category Lilmuckers
 * @package  Lilmuckers_AsynchronousIndex
 * @author   Patrick McKinley <contact@patrick-mckinley.com>
 * @license  MIT http://choosealicense.com/licenses/mit/
 * @link     https://github.com/lilmuckers/magento-lilmuckers_asynchronousindex-enterprise
 */
class Lilmuckers_AsynchronousIndex_Model_Worker_Category_Product
    extends Lilmuckers_Queue_Model_Worker_Abstract
{
    /**
     * Refresh the category product index stuff
     * 
     * @param Lilmuckers_Queue_Model_Queue_Task $task The task handler
     * 
     * @return Lilmuckers_AsynchronousIndex_Model_Worker_Flat
     */
    public function categoryProductsUpdate(Lilmuckers_Queue_Model_Queue_Task $task)
    {
        try {
            //Category/Product refresh
            $_client = $task->getQueue()->getMviewClient()
                ->init('catalog_category_product_cat');
            $_client->execute(
                'enterprise_catalog/index_action_catalog_category_product_category_refresh_row', 
                array(
                    'value' => $task->getCategoryIds()
                )
            );
        } catch(Exception $e) {
            //if there's an error - retry the task
            $task->retry();
        }
        
        return $this;
    }

    /**
     * Refresh the category product and pricing index stuff
     * 
     * @param Lilmuckers_Queue_Model_Queue_Task $task The task handler
     * 
     * @return Lilmuckers_AsynchronousIndex_Model_Worker_Flat
     */
    public function categoryProductPriceUpdate(Lilmuckers_Queue_Model_Queue_Task $task)
    {
        try {
            //process the price refresh
            $_client = $task->getQueue()->getMviewClient()->init(
                Mage::helper('enterprise_index')->getIndexerConfigValue(
                    'catalog_product_price', 
                    'index_table'
                )
            );
            $_client->execute('enterprise_catalog/index_action_product_price_refresh_changelog');
            
            //process the category product relation index
            $_client = $task->getQueue()->getMviewClient()
                ->init('catalog_category_product_index');
            $_client->execute(
                'enterprise_catalog/index_action_catalog_category_product_refresh_changelog'
            );
        } catch(Exception $e) {
            //if there's an error - retry the task
            $task->retry();
        }
        
        return $this;
    }

    /**
     * Refresh the category product index stuff
     * 
     * @param Lilmuckers_Queue_Model_Queue_Task $task The task handler
     * 
     * @return Lilmuckers_AsynchronousIndex_Model_Worker_Flat
     */
    public function categoryProductUpdate(Lilmuckers_Queue_Model_Queue_Task $task)
    {
        try {
            //Category/Product refresh
            $_client = $task->getQueue()->getMviewClient()
                ->init('catalog_category_product_index');
            $_client->execute(
                'enterprise_catalog/index_action_catalog_category_product_refresh_row', 
                array(
                    'value' => $task->getProductId(),
                )
            );
        } catch(Exception $e) {
            //if there's an error - retry the task
            $task->retry();
        }
        
        return $this;
    }
}
