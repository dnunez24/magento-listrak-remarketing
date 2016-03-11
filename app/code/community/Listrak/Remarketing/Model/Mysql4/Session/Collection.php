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
 * Class Listrak_Remarketing_Model_Mysql4_Session_Collection
 */
class Listrak_Remarketing_Model_Mysql4_Session_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initializes collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('listrak/session');
    }

    /**
     * Limit result to specific stores
     *
     * @param array|int $storeIds Magento store ID(s)
     *
     * @return Listrak_Remarketing_Model_Mysql4_Session_Collection
     */
    public function addStoreFilter($storeIds)
    {
        $this->getSelect()->where('main_table.store_id IN (?)', $storeIds);
        return $this;
    }

    /**
     * Inflate all items in collection
     *
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    protected function _afterLoad()
    {
        /* @var Listrak_Remarketing_Model_Session $i */
        foreach ($this->_items as $i) {
            $i->afterLoad();
        }

        return parent::_afterLoad();
    }
}
