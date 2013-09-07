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
 * The asyncronous flat catalog worker
 *
 * @category Lilmuckers
 * @package  Lilmuckers_AsynchronousIndex
 * @author   Patrick McKinley <contact@patrick-mckinley.com>
 * @license  MIT http://choosealicense.com/licenses/mit/
 * @link     https://github.com/lilmuckers/magento-lilmuckers_asynchronousindex-enterprise
 */
class Lilmuckers_AsynchronousIndex_Model_Worker_Price extends Lilmuckers_Queue_Model_Worker_Abstract
{
    /**
     * Refresh the flat category index stuff
     * 
     * @param Lilmuckers_Queue_Model_Queue_Task $task The task handler
     * 
     * @return Lilmuckers_AsynchronousIndex_Model_Worker_Flat
     */
    public function updateProductPrice(Lilmuckers_Queue_Model_Queue_Task $task)
    {
        try {
            $_client = $task->getQueue()->getMviewClient()->init(
                Mage::helper('enterprise_cataloginventory/index')
                    ->getIndexerConfigValue('catalog_product_price', 'index_table')
            );
            
            $_arguments = array(
                'value' => $task->getProductId(),
            );
            
            $_client->execute(
                'enterprise_catalog/index_action_product_price_refresh_row', 
                $_arguments
            );
        } catch(Exception $e) {
            //if there's an error - retry the task
            $task->retry();
        }
        
        return $this;
    }
    
    /**
     * Refresh the flat category index stuff
     * 
     * @param Lilmuckers_Queue_Model_Queue_Task $task The task handler
     * 
     * @return Lilmuckers_AsynchronousIndex_Model_Worker_Flat
     */
    public function updatePriceRules(Lilmuckers_Queue_Model_Queue_Task $task)
    {
        try {
            $_client = $task->getQueue()->getMviewClient()->init(
                Mage::helper('enterprise_cataloginventory/index')
                    ->getIndexerConfigValue('catalog_product_price', 'index_table')
            );
            $_client->execute('enterprise_catalog/index_action_product_price_refresh_changelog');
        } catch(Exception $e) {
            //if there's an error - retry the task
            $task->retry();
        }
        
        return $this;
    }
}
