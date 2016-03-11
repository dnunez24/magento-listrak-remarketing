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
 * Class Listrak_Remarketing_Model_Abandonedcart_Api
 */
class Listrak_Remarketing_Model_Abandonedcart_Api
    extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve abandoned carts
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

        Mage::app()->setCurrentStore($storeId);
        $helper->requireCoreEnabled();

        $helper->requireSessionTrackingTable();

        try {
            if ($startDate === null || !strtotime($startDate)) {
                $this->_fault('incorrect_date');
            }

            if ($endDate === null || !strtotime($endDate)) {
                $this->_fault('incorrect_date');
            }

            $storeIdArray = explode(',', $storeId);

            /* @var Listrak_Remarketing_Model_Mysql4_Abandonedcart_Collection $collection */
            $collection = Mage::getModel('listrak/abandonedcart')->getCollection();
            $collection
                ->addStoreFilter($storeIdArray)
                ->addClearCartTrimFilter($startDate)
                ->addFieldToFilter(
                    'main_table.updated_at',
                    array('from' => $startDate, 'to' => $endDate)
                )
                ->addFieldToFilter('main_table.converted', '0')
                ->setPageSize($perPage)->setCurPage($page)
                ->setOrder('updated_at', 'ASC')
                ->distinct(true);

            $result = array();

            foreach ($collection as $item) {
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
     * Purge old abandoned cart entries
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

        $helper->requireSessionTrackingTable();

        try {
            if ($endDate === null || !strtotime($endDate)) {
                $this->_fault('incorrect_date');
            }

            $sessions = Mage::getModel("listrak/session")
                ->getCollection()
                ->addFieldToFilter('updated_at', array('lt' => $endDate));

            $count = 0;

            /* @var Listrak_Remarketing_Model_Session $session */
            foreach ($sessions as $session) {
                $session->delete();
                $count++;
            }

            if (!$helper->legacyTracking()
                && $helper->getTableRowCount('listrak/session') == 0
                && $helper->getTableRowCount('listrak/click') == 0
            ) {
                /* @var Mage_Core_Model_Resource $resource */
                $resource = Mage::getSingleton('core/resource');

                $resource->getConnection('core_write')
                    ->query(
                        "DROP TABLE {$resource->getTableName('listrak/session')}"
                        . ", {$resource->getTableName('listrak/session_email')}"
                        . ", {$resource->getTableName('listrak/click')}"
                    );

                $config = Mage::getConfig();
                $config->saveConfig(
                    'remarketing/config/tracking_tables_deleted', '1'
                );
                $config->reinit();
                Mage::app()->reinitStores();
            }

            return $count;
        } catch (Exception $e) {
            throw $helper->generateAndLogException(
                "Exception occurred in API call: " . $e->getMessage(), $e
            );
        }
    }
}
