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
class Lilmuckers_AsynchronousIndex_Model_Worker_Flat extends Lilmuckers_Queue_Model_Worker_Abstract
{
    /**
     * Refresh the flat category index stuff
     * 
     * @param Lilmuckers_Queue_Model_Queue_Task $task The task handler
     * 
     * @return Lilmuckers_AsynchronousIndex_Model_Worker_Flat
     */
    public function updateCategoryFlatIndex(Lilmuckers_Queue_Model_Queue_Task $task)
    {
        try {
            //Get the mview client
            $_client = $task->getQueue()->getMviewClient()->init(
                Mage::helper('enterprise_index')->getIndexerConfigValue(
                    'catalog_category_flat', 
                    'index_table'
                )
            );
            
            //build the arguments
            $_arguments = array(
                'value' => $task->getCategoryId()
            );
            
            //run the flat indexer
            $_client->execute(
                'enterprise_catalog/index_action_category_flat_refresh_row', 
                $_arguments
            );
        } catch(Exception $e) {
            //if there's an error - retry the task
            $task->retry();
        }
        
        return $this;
    }
    
    /**
     * Refresh the flat product index stuff
     * 
     * @param Lilmuckers_Queue_Model_Queue_Task $task The task handler
     * 
     * @return Lilmuckers_AsynchronousIndex_Model_Worker_Flat
     */
    public function updateProductFlatIndex(Lilmuckers_Queue_Model_Queue_Task $task)
    {
        try {
            //Get the mview client
            $_client = $task->getQueue()->getMviewClient()->init(
                Mage::helper('enterprise_index')->getIndexerConfigValue(
                    'catalog_product_flat', 
                    'index_table'
                )
            );
            
            //build the arguments
            $_arguments = array(
                'value'      => $task->getProductId(),
            );
            
            //fun the flat indexer
            $_client->execute(
                'enterprise_catalog/index_action_product_flat_refresh_row', 
                $_arguments
            );
        } catch(Exception $e) {
            //if there's an error - retry the task
            $task->retry();
        }
        
        return $this;    
    }
    
    /**
     * Refresh the flat product index changelog stuff
     * 
     * @param Lilmuckers_Queue_Model_Queue_Task $task The task handler
     * 
     * @return Lilmuckers_AsynchronousIndex_Model_Worker_Flat
     */
    public function updateProductFlatChangelog(Lilmuckers_Queue_Model_Queue_Task $task)
    {
        try {
            //get the mview client
            $_client = $task->getQueue()->getMviewClient()->init(
                Mage::helper('enterprise_index')->getIndexerConfigValue(
                    'catalog_category_flat', 
                    'index_table'
                )
            );
            
            //run the changelog indexer
            $_client->execute('enterprise_catalog/index_action_category_flat_refresh_changelog');
        } catch(Exception $e) {
            //if there's an error - retry the task
            $task->retry();
        }
        
        return $this;
    }
}
