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
 * The asyncronous index queue handler
 *
 * @category Lilmuckers
 * @package  Lilmuckers_AsynchronousIndex
 * @author   Patrick McKinley <contact@patrick-mckinley.com>
 * @license  MIT http://choosealicense.com/licenses/mit/
 * @link     https://github.com/lilmuckers/magento-lilmuckers_asynchronousindex-enterprise
 */
class Lilmuckers_AsynchronousIndex_Model_Queue
    extends Lilmuckers_Queue_Model_Queue_Abstract
{
    /**
     * Get the enterprise_mview/client model
     * 
     * @return Enterprise_Mview_Model_Client
     */
    public function getMviewClient()
    {
        return Mage::getModel('enterprise_mview/client');
    }
}
