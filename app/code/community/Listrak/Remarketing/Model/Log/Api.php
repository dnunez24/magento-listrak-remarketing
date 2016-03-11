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
 * Class Listrak_Remarketing_Model_Log_Api
 */
class Listrak_Remarketing_Model_Log_Api
    extends Mage_Api_Model_Resource_Abstract
{

    /**
     * Retrieve logged messages and exceptions
     *
     * @param int      $storeId   Magento store ID
     * @param datetime $startDate Lower date constraint
     * @param datetime $endDate   Upper date constraint
     * @param int      $perPage   Page size
     * @param int      $page      Cursor
     * @param int      $logTypeId Filter by log type (deprecated)
     *
     * @return array
     *
     * @throws Exception
     */
    public function items(
        $storeId = 1, $startDate = null, $endDate = null,
        $perPage = 50, $page = 1, $logTypeId = 0
    ) {
        if ($startDate === null || !strtotime($startDate)) {
            $this->_fault('incorrect_date');
        }

        if ($endDate === null || !strtotime($endDate)) {
            $this->_fault('incorrect_date');
        }

        try {
            /* @var Listrak_Remarketing_Model_Mysql4_Log_Collection $logs */
            $logs = Mage::getModel("listrak/log")->getCollection();

            $logs
                ->addStoreFilter($storeId)
                ->addFieldToFilter(
                    'date_entered',
                    array('from' => $startDate, 'to' => $endDate)
                )
                ->setPageSize($perPage)->setCurPage($page);

            $result = array();

            foreach ($logs as $item) {
                $result[] = $item;
            }

            return $result;
        } catch (Exception $e) {
            /* @var Listrak_Remarketing_Helper_Data $helper */
            $helper = Mage::helper('remarketing');
            throw $helper->generateAndLogException(
                "Exception occurred in API call: " . $e->getMessage(), $e
            );
        }
    }

    /**
     * Purge all log lines
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
        if ($endDate === null || !strtotime($endDate)) {
            $this->_fault('incorrect_date');
        }

        try {
            $logs = Mage::getModel("listrak/log")
                ->getCollection()
                ->addFieldToFilter('date_entered', array('lt' => $endDate));

            $count = 0;

            /* @var Listrak_Remarketing_Model_Log $log */
            foreach ($logs as $log) {
                $log->delete();
                $count++;
            }

            return $count;
        } catch (Exception $e) {
            /* @var Listrak_Remarketing_Helper_Data $helper */
            $helper = Mage::helper('remarketing');
            throw $helper->generateAndLogException(
                "Exception occurred in API call: " . $e->getMessage(), $e
            );
        }
    }
}
