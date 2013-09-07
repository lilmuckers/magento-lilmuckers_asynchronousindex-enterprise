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
 * The asyncronous category index worker
 *
 * @category Lilmuckers
 * @package  Lilmuckers_AsynchronousIndex
 * @author   Patrick McKinley <contact@patrick-mckinley.com>
 * @license  MIT http://choosealicense.com/licenses/mit/
 * @link     https://github.com/lilmuckers/magento-lilmuckers_asynchronousindex-enterprise
 */
class Lilmuckers_AsynchronousIndex_Model_Worker extends Lilmuckers_Queue_Model_Worker_Abstract
{
    /**
     * Refresh the category URL rewrite stuff
     * 
     * @param Lilmuckers_Queue_Model_Queue_Task $task The task handler
     * 
     * @return Lilmuckers_AsynchronousIndex_Model_Worker
     */
    public function refreshCategoryUrlRewrite(Lilmuckers_Queue_Model_Queue_Task $task)
    {
        try {
            //get the MView client object
            $_client = $task->getQueue()->getMviewClient()->init('enterprise_url_rewrite_category');
            
            //execute the url reindexing
            $_client->execute(
                'enterprise_catalog/index_action_url_rewrite_category_refresh_row', 
                array(
                    'category_id' => $task->getCategoryId()
                )
            );
        } catch(Exception $e) {
            //if there's an error - retry the task
            $task->retry();
        }
        return $this;
    }

    /**
     * Refresh the category URL rewrite stuff
     * 
     * @param Lilmuckers_Queue_Model_Queue_Task $task The task handler
     * 
     * @return Lilmuckers_AsynchronousIndex_Model_Worker
     */
    public function refreshProductUrlRewrite(Lilmuckers_Queue_Model_Queue_Task $task)
    {
        try {
            //get the MView client object
            $_client = $task->getQueue()->getMviewClient()->init('enterprise_url_rewrite_product');
            
            //execute the url reindexing
            $_client->execute(
                'enterprise_catalog/index_action_url_rewrite_product_refresh_row', 
                array(
                    'product_id' => $task->getProductId()
                )
            );
        } catch(Exception $e) {
            //if there's an error - retry the task
            $task->retry();
        }
        return $this;
    }
}
