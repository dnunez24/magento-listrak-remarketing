<?php
/**
 * Listrak Remarketing Magento Extension Ver. 1.0.0
 *
 * PHP version 5
 *
 * @category  Listrak
 * @package   Listrak_Remarketing
 * @author    Listrak Magento Team <magento@listrak.com>
 * @copyright 2011 Listrak Inc
 * @license   http://s1.listrakbi.com/licenses/magento.txt License For Customer Use of Listrak Software
 * @link      http://www.listrak.com
 */

/**
 * Class Listrak_Remarketing_Model_Log
 *
 * Serves as the logger of the extension
 */
class Listrak_Remarketing_Model_Log extends Mage_Core_Model_Abstract
{
    const LOG_TYPE_MESSAGE = 1;
    const LOG_TYPE_EXCEPTION = 2;

    /**
     * Initializes the object
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('listrak/log');
    }

    /**
     * Log a line
     *
     * @param string   $msg     Message
     * @param int|null $storeId Magento store ID, defaults to current
     *
     * @return void
     */
    public function addMessage($msg, $storeId = null)
    {
        if ($storeId == null) {
            $storeId = Mage::app()->getStore()->getStoreId();
        }

        $this->setMessage($msg);
        $this->setLogTypeId(self::LOG_TYPE_MESSAGE);
        $this->setStoreId($storeId);
        $this->save();
    }

    /**
     * Log an exception
     *
     * @param string   $msg     Exception message
     * @param null|int $storeId Magento store ID
     *
     * @return void
     */
    public function addException($msg, $storeId = null)
    {
        if ($storeId == null) {
            $storeId = Mage::app()->getStore()->getStoreId();
        }

        $this->setMessage($msg);
        $this->setLogTypeId(self::LOG_TYPE_EXCEPTION);
        $this->setStoreId($storeId);
        $this->save();
    }
}