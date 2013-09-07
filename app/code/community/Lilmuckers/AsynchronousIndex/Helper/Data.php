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
 * The asyncronous index default helper
 *
 * @category Lilmuckers
 * @package  Lilmuckers_AsynchronousIndex
 * @author   Patrick McKinley <contact@patrick-mckinley.com>
 * @license  MIT http://choosealicense.com/licenses/mit/
 * @link     https://github.com/lilmuckers/magento-lilmuckers_asynchronousindex-enterprise
 */
class Lilmuckers_AsynchronousIndex_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * The configuration path for enabling the asyncronous indexing
     */
    const ASYNC_ENABLE_CONFIG_PATH = 'index_management/index_options/asyncindex';
    
    /**
     * The queue to use for the reindexing
     */
    const ASYNC_QUEUE = 'asyncindex';
    
    /**
     * Return a flag for when we're using an asyncronous index
     * 
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::ASYNC_ENABLE_CONFIG_PATH);
    }
    
    /**
     * Get the asyncronous index queue handler
     * 
     * @return Lilmuckers_AsynchronousIndex_Model_Queue
     */
    public function getQueue()
    {
        return Mage::helper('lilqueue')->getQueue('asyncindex');
    }
    
    /**
     * Queue a task for working
     * 
     * @param string $task    The task identifier
     * @param array  $data    The data to send with the task
     * @param mixed  $storeId The store to work with
     * 
     * @return Lilmuckers_Queue_Model_Queue_Task
     */
    public function queueTask($task, $data = array(), $storeId = null)
    {
        //get the queue handler
        $_queue = $this->getQueue();
        
        //instantiate the task
        $_task = Mage::helper('lilqueue')->createTask(
            $task, 
            $data,
            $storeId
        );
        
        //send to the queue
        $_queue->addTask($_task);
        
        return $_task;
    }
}
