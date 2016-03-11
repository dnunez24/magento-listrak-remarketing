<?php
/**
 * Listrak Remarketing Magento Extension Ver. 1.1.5
 *
 * PHP version 5
 *
 * @category  Listrak
 * @package   Listrak_Remarketing
 * @author    Listrak Magento Team <magento@listrak.com>
 * @copyright 2013 Listrak Inc
 * @license   http://s1.listrakbi.com/licenses/magento.txt License For Customer Use of Listrak Software
 * @link      http://www.listrak.com
 */

/**
 * Class Listrak_Remarketing_Helper_Review_Update
 *
 * Provides a few shortcuts to use in the API methods querying
 * review and rating summary updates
 */
class Listrak_Remarketing_Helper_Review_Update
    extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieves a collection with review updates
     *
     * @param int $storeId Magento store ID
     *
     * @return Mage_Review_Model_Resource_Review_Collection
     */
    public function getReviewListCollection($storeId)
    {
        /* @var Mage_Review_Model_Resource_Review_Collection $collection */
        $collection = Mage::getModel('review/review')
            ->getCollection()
            ->addStoreFilter($storeId);

        /* @var Listrak_Remarketing_Model_Review_Update $updateModel */
        $updateModel = Mage::getModel('listrak/review_update');

        /* @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');

        $collection
            ->getSelect()
            ->joinLeft(
                array(
                    'updatetime' =>
                        $updateModel
                            ->getReviewUpdateCollection()
                            ->getSelect()
                ),
                "main_table.review_id = updatetime.review_id",
                array("updatetime.update_id", "updatetime.updated_at")
            )
            ->joinLeft(
                array(
                    'employee' => $resource
                        ->getTableName('customer/entity')
                ),
                'detail.customer_id = employee.entity_id',
                array('employee.email')
            );

        return $collection;
    }

    /**
     * Retrieves a collection with rating summary updates
     *
     * @param int $storeId Magento store ID
     *
     * @return Mage_Review_Model_Resource_Review_Summary_Collection
     */
    public function getRatingSummaryListCollection($storeId)
    {
        /* @var Mage_Review_Model_Resource_Review_Summary_Collection $collection */
        $collection = Mage::getModel('review/review_summary')
            ->getCollection();

        /* @var Listrak_Remarketing_Model_Review_Update $updateModel */
        $updateModel = Mage::getModel('listrak/review_update');

        $updatetimeSelect = $updateModel
            ->getRatingSummaryUpdateCollection()
            ->getSelect();

        $joinOnClause
            = "review_entity_summary.primary_id = updatetime.rating_summary_id"
            . " AND review_entity_summary.store_id = updatetime.store_id";

        $collection->getSelect()
            ->joinLeft(
                array('updatetime' => $updatetimeSelect),
                $joinOnClause,
                array("updatetime.update_id", "updatetime.updated_at")
            )
            ->where('review_entity_summary.store_id = ?', $storeId);

        return $collection;
    }
}

