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
 * Class Listrak_Remarketing_Model_Mysql4_Abandonedcart_Collection
 */
class Listrak_Remarketing_Model_Mysql4_Abandonedcart_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initializes collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('listrak/abandonedcart');
    }

    /**
     * Initializes database select
     *
     * @return void
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()
            ->join(
                array('q' => $this->getTable('sales/quote')),
                'main_table.quote_id = q.entity_id',
                array('items_qty', 'grand_total')
            )
            ->where('main_table.had_items = 1 AND q.is_active = 1');
            // is_active is set to false when the order is submitted
    }

    /**
     * Filter by store
     *
     * @param array|int $storeIds Magento store ID(s)
     *
     * @return $this
     */
    public function addStoreFilter($storeIds)
    {
        $this->getSelect()->where('main_table.store_id IN (?)', $storeIds);
        return $this;
    }

    /**
     * Filter out carts that have been create and cleared in current time frame
     *
     * @param datetime $fromDate Lower date constraint
     *
     * @return $this
     */
    public function addClearCartTrimFilter($fromDate)
    {
        $this->getSelect()
            ->where("q.items_qty > 0 OR main_table.created_at <= '{$fromDate}'");
        return $this;
    }

    /**
     * Inflate items for reporting
     *
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    protected function _afterLoad()
    {
        /* @var Listrak_Remarketing_Model_Abandonedcart $item */
        foreach ($this->_items as $item) {
            $item->afterLoad();
        }

        return parent::_afterLoad();
    }
}
