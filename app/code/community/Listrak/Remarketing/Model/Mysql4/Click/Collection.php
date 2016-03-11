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
 * Class Listrak_Remarketing_Model_Mysql4_Click_Collection
 */
class Listrak_Remarketing_Model_Mysql4_Click_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initializes collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('listrak/click');
    }

    /**
     * Filter by store
     *
     * @param array|int $storeId Magento store ID(s)
     *
     * @return $this
     */
    public function addStoreFilter($storeId)
    {
        $this->getSelect()
            ->join(
                array('s' => $this->getTable('listrak/session')),
                'main_table.session_id = s.id',
                array('store_id', 'session_id as session_uid', 'pi_id')
            )
            ->where('s.store_id IN (?)', $storeId);
        return $this;
    }
}
