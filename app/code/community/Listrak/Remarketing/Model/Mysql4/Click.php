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
 * Class Listrak_Remarketing_Model_Mysql4_Click
 */
class Listrak_Remarketing_Model_Mysql4_Click
    extends Mage_Core_Model_Mysql4_Abstract
{
    /* @var Varien_Db_Adapter_Interface $_read */
    private $_read;

    /**
     * Initializes resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('listrak/click', 'click_id');
        $this->_read = $this->_getReadAdapter();
    }

    /**
     * Load data associated with a quote
     *
     * @param int $quoteId Magento quote ID
     *
     * @return array
     */
    public function loadByQuoteId($quoteId)
    {
        $select = $this->_read->select()
            ->from($this->getTable('listrak/session'), array("*"))
            ->where('quote_id=?', $quoteId)
            ->join(
                array('c' => $this->getTable('listrak/click')),
                'id = c.session_id',
                array()
            );

        if ($result = $this->_read->fetchAll($select)) {
            return $result;
        }

        return array();
    }

    /**
     * Retrieve last click's data for a quote
     *
     * @param int $quoteId Magento quote ID
     *
     * @return array|null
     */
    public function loadLatestByQuoteId($quoteId)
    {
        $select = $this->_read->select()
            ->from(array('c' => $this->getTable('listrak/click')), array("*"))
            ->joinInner(
                array('s' => $this->getTable('listrak/session')),
                's.id = c.session_id',
                array()
            )
            ->where('s.quote_id = ?', $quoteId)
            ->order('click_id ' . Varien_Db_Select::SQL_DESC)
            ->limit(0, 1);

        if ($result = $this->_read->fetchRow($select)) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Retrieve last click's data for a session
     *
     * @param string $sid Session UID
     *
     * @return array|null
     */
    public function loadLatestBySessionId($sid)
    {
        $select = $this->_read->select()
            ->from(array('c' => $this->getTable('listrak/click')), array("*"))
            ->joinInner(
                array('s' => $this->getTable('listrak/session')),
                's.id = c.session_id',
                array()
            )
            ->where('s.session_id = ?', $sid)
            ->order('click_id ' . Varien_Db_Select::SQL_DESC)
            ->limit(0, 1);

        if ($result = $this->_read->fetchRow($select)) {
            return $result;
        } else {
            return null;
        }
    }
}
