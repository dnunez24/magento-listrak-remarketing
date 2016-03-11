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
 * Class Listrak_Remarketing_Model_Click_Api
 */
class Listrak_Remarketing_Model_Click_Api
    extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve clicks
     *
     * @param int      $storeId   Magento store ID
     * @param datetime $startDate Lower date constraint
     * @param datetime $endDate   Upper date constraint
     * @param int      $perPage   Page size
     * @param int      $page      Cursor
     *
     * @return array
     *
     * @throws Exception
     */
    public function items(
        $storeId = 1, $startDate = null, $endDate = null,
        $perPage = 50, $page = 1
    ) {
        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');

        // does not require a store to check - it's either there or not there
        $helper->requireClickTrackingTable();

        Mage::app()->setCurrentStore($storeId);

        $helper->requireCoreEnabled();

        if ($startDate === null || !strtotime($startDate)) {
            $this->_fault('incorrect_date');
        }

        if ($endDate === null || !strtotime($endDate)) {
            $this->_fault('incorrect_date');
        }

        try {
            /* @var Listrak_Remarketing_Model_Mysql4_Click_Collection $clicks */
            $clicks = Mage::getModel("listrak/click")->getCollection();

            $clicks
                ->addStoreFilter($storeId)
                ->addFieldToFilter(
                    'click_date', array('from' => $startDate, 'to' => $endDate)
                )
                ->setPageSize($perPage)->setCurPage($page);

            $result = array();

            foreach ($clicks as $item) {
                $result[] = $item;
            }

            return $result;
        } catch (Exception $e) {
            throw $helper->generateAndLogException(
                "Exception occurred in API call: " . $e->getMessage(), $e
            );
        }
    }

    /**
     * Purge clicks
     *
     * @param int      $storeId Magento store ID (deprecated)
     * @param datetime $endDate Upper date constraint
     *
     * @return int
     *
     * @throws Exception
     */
    public function purge($storeId = 1, $endDate = null)
    {
        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');

        $helper->requireClickTrackingTable();

        if ($endDate === null || !strtotime($endDate)) {
            $this->_fault('incorrect_date');
        }

        try {
            $clicks = Mage::getModel("listrak/click")
                ->getCollection()
                ->addFieldToFilter('click_date', array('lt' => $endDate));

            $count = 0;

            /* @var Listrak_Remarketing_Model_Click $click */
            foreach ($clicks as $click) {
                $click->delete();
                $count++;
            }

            return $count;
        } catch (Exception $e) {
            throw $helper->generateAndLogException(
                "Exception occurred in API call: " . $e->getMessage(), $e
            );
        }
    }
}
