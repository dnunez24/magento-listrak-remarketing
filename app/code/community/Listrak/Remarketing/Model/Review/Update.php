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
 * Class Listrak_Remarketing_Model_Review_Update
 */
class Listrak_Remarketing_Model_Review_Update
    extends Mage_Core_Model_Abstract
{
    const ACTIVITY_TYPE_UPDATE = 1;
    const ACTIVITY_TYPE_DELETE = 2;

    /**
     * Initializes the object
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('listrak/review_update');
    }

    /**
     * Retrieve an update record for a review
     *
     * @param int $reviewId Review ID
     *
     * @return Listrak_Remarketing_Model_Review_Update
     */
    public function loadByReviewId($reviewId)
    {
        return $this->getCollection()
            ->addFilter('review_id', $reviewId)
            ->getFirstItem();
    }

    /**
     * Retrieve a collection of updated reviews
     *
     * @return Listrak_Remarketing_Model_Mysql4_Review_Update_Collection
     */
    public function getReviewUpdateCollection()
    {
        /* @var Listrak_Remarketing_Model_Mysql4_Review_Update_Collection $col */
        $col = $this->getCollection();
        return $col->getReviewUpdateTime();
    }

    /**
     * Retrieve a collection of updated rating summaries
     *
     * @return Listrak_Remarketing_Model_Mysql4_Review_Update_Collection
     */
    public function getRatingSummaryUpdateCollection()
    {
        /* @var Listrak_Remarketing_Model_Mysql4_Review_Update_Collection $col */
        $col = $this->getCollection();
        return $col->getRatingSummaryUpdateTime();
    }

    /**
     * Store a record of the review update
     *
     * @param int $reviewId      Review ID
     * @param int $entityId      Review type
     * @param int $entityPkValue Reviewed product ID
     *
     * @return void
     */
    public function markUpdated($reviewId, $entityId, $entityPkValue)
    {
        $this->mark(
            $reviewId, $entityId, $entityPkValue, self::ACTIVITY_TYPE_UPDATE
        );
    }

    /**
     * Store a record of review deletion
     *
     * @param int $reviewId      Review ID
     * @param int $entityId      Review type
     * @param int $entityPkValue Reviewed product ID
     *
     * @return void
     */
    public function markDeleted($reviewId, $entityId, $entityPkValue)
    {
        $this->mark(
            $reviewId, $entityId, $entityPkValue, self::ACTIVITY_TYPE_DELETE
        );
    }

    /**
     * Create a record to store review update or deletion
     *
     * @param int $reviewId      Review ID
     * @param int $entityId      Review type
     * @param int $entityPkValue Reviewed product ID
     * @param int $activityType  Updated or deleted
     *
     * @return void
     */
    protected function mark($reviewId, $entityId, $entityPkValue, $activityType)
    {
        $this->setReviewId($reviewId);
        $this->setEntityId($entityId);
        $this->setEntityPkValue($entityPkValue);
        $this->setActivityTime(gmdate('Y-m-d H:i:s'));
        $this->setActivity($activityType);
        $this->save();
    }
}

