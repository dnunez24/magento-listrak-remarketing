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
 * Class Listrak_Remarketing_Model_Review_Update_Api
 */
class Listrak_Remarketing_Model_Review_Update_Api
    extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve reviews
     *
     * @param int $storeId       Magento store ID
     * @param int $chunkSize     Page size
     * @param int $startReviewId Cursor
     *
     * @return array
     *
     * @throws Exception
     */
    public function reviewList($storeId, $chunkSize, $startReviewId)
    {
        Mage::app()->setCurrentStore($storeId);

        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');
        $helper->requireReviewsEnabled();

        try {
            $getStoreId = is_numeric($storeId) ? $storeId : 1;
            $getChunkSize = is_numeric($chunkSize) ? $chunkSize : 50;
            $fromReviewId = $startReviewId + 1;

            /* @var Listrak_Remarketing_Helper_Review_Update $updateHelper */
            $updateHelper = Mage::helper('remarketing/review_update');

            $collection = $updateHelper->getReviewListCollection($getStoreId);
            $collection
                ->getSelect()
                ->where("main_table.review_id >= ?", $fromReviewId)
                ->reset(Zend_Db_Select::ORDER)
                ->order('main_table.review_id ' . Varien_Db_Select::SQL_ASC)
                ->limit($getChunkSize);

            return $this->_reviewsFromCollection($getStoreId, $collection);
        } catch (Exception $e) {
            throw $helper->generateAndLogException(
                "Exception occurred in API call: " . $e->getMessage(), $e
            );
        }
    }

    /**
     * Retrieve updated reviews
     *
     * @param int $storeId       Magento store ID
     * @param int $chunkSize     Page size
     * @param int $startUpdateId Cursor
     *
     * @return array
     *
     * @throws Exception
     */
    public function reviewUpdateList($storeId, $chunkSize, $startUpdateId)
    {
        Mage::app()->setCurrentStore($storeId);

        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');
        $helper->requireReviewsEnabled();

        try {
            $getStoreId = is_numeric($storeId) ? $storeId : 1;
            $getChunkSize = is_numeric($chunkSize) ? $chunkSize : 50;
            $fromUpdateId = $startUpdateId + 1;

            /* @var Listrak_Remarketing_Helper_Review_Update $updateHelper */
            $updateHelper = Mage::helper('remarketing/review_update');

            $collection = $updateHelper->getReviewListCollection($getStoreId);
            $collection
                ->getSelect()
                ->where("updatetime.update_id >= ?", $fromUpdateId)
                ->reset(Zend_Db_Select::ORDER)
                ->order('updatetime.update_id ' . Varien_Db_Select::SQL_ASC)
                ->limit($getChunkSize);

            return $this->_reviewsFromCollection($getStoreId, $collection);
        } catch (Exception $e) {
            throw $helper->generateAndLogException(
                "Exception occurred in API call: " . $e->getMessage(), $e
            );
        }
    }

    /**
     * Retrieve review information
     *
     * @param int   $storeId          Magento store ID
     * @param mixed $reviewCollection Collection of reviews
     *
     * @return array
     */
    private function _reviewsFromCollection($storeId, $reviewCollection)
    {
        $statuses = $this->getStatuses();
        $ratings = $this->getRatings();

        $reviews = array();

        /* @var Mage_Review_Model_Review $review */
        foreach ($reviewCollection as $review) {
            $reviewId = $review->getReviewId();

            $reviewRatings = array();

            /* @var Mage_Rating_Model_Resource_Rating_Option_Vote_Collection $votes */
            $votes = Mage::getModel('rating/rating_option_vote')
                ->getResourceCollection();
            $votes->setReviewFilter($reviewId);

            /* @var Mage_Rating_Model_Rating_Option_Vote $vote */
            foreach ($votes as $vote) {
                $ratingId = $vote->getRatingId();

                array_push(
                    $reviewRatings,
                    array(
                        "rating_id" => $ratingId,
                        "rating_code" => $ratings[$ratingId],
                        "rating" => round($vote->getPercent() / 20, 4)
                    )
                );
            }

            /* @var Mage_Rating_Model_Rating $ratingModel */
            $ratingModel = Mage::getModel('rating/rating');
            $overallRatingObj = $ratingModel->getReviewSummary($reviewId, false);

            $overallRating = 0;
            foreach ($overallRatingObj as $ratingObj) {
                if ($ratingObj->getStoreId() == $storeId) {
                    if ($ratingObj->getCount()) {
                        $overallRating
                            = $ratingObj->getSum() / $ratingObj->getCount();
                    }

                    break;
                }
            }

            array_push(
                $reviews,
                array(
                    "update_id" => $review->getUpdateId(),
                    "review_id" => $reviewId,
                    "product_id" => $review->getEntityPkValue(),
                    "title" => $review->getTitle(),
                    "text" => $review->getDetail(),
                    "overall_rating" => round($overallRating / 20, 4),
                    "created_at" => $review->getCreatedAt(),
                    "updated_at" => $review->getUpdatedAt(),
                    "reviewer_name" => $review->getNickname(),
                    "email" => $review->getEmail(),
                    "status_id" => $review->getStatusId(),
                    "status_code" => $statuses[$review->getStatusId()],
                    "ratings" => $reviewRatings
                )
            );
        }

        return $reviews;
    }

    /**
     * Retrieve rating summaries
     *
     * @param int $storeId              Magento store ID
     * @param int $chunkSize            Page size
     * @param int $startRatingSummaryId Cursor
     *
     * @return array
     *
     * @throws Exception
     */
    public function ratingSummaryList($storeId, $chunkSize, $startRatingSummaryId)
    {
        Mage::app()->setCurrentStore($storeId);

        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');
        $helper->requireReviewsEnabled();

        try {
            $getStoreId = is_numeric($storeId) ? $storeId : 1;
            $getChunkSize = is_numeric($chunkSize) ? $chunkSize : 50;
            $fromRatingSummaryId = $startRatingSummaryId + 1;

            /* @var Listrak_Remarketing_Helper_Review_Update $updateHelper */
            $updateHelper = Mage::helper('remarketing/review_update');
            $collection = $updateHelper->getRatingSummaryListCollection($getStoreId);
            $collection->getSelect()
                ->where('review_entity_summary.entity_type = ?', 1)
                ->where(
                    "review_entity_summary.primary_id >= ?", $fromRatingSummaryId
                )
                ->order(
                    'review_entity_summary.primary_id ' . Varien_Db_Select::SQL_ASC
                )
                ->limit($getChunkSize);

            return $this->_ratingSummariesFromCollection($getStoreId, $collection);
        } catch (Exception $e) {
            throw $helper->generateAndLogException(
                "Exception occurred in API call: " . $e->getMessage(), $e
            );
        }
    }

    /**
     * Retrieve updated rating summaries
     *
     * @param int $storeId       Magento store ID
     * @param int $chunkSize     Page size
     * @param int $startUpdateId Cursor
     *
     * @return array
     *
     * @throws Exception
     */
    public function ratingSummaryUpdateList($storeId, $chunkSize, $startUpdateId)
    {
        Mage::app()->setCurrentStore($storeId);

        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');
        $helper->requireReviewsEnabled();

        try {
            $getStoreId = is_numeric($storeId) ? $storeId : 1;
            $getChunkSize = is_numeric($chunkSize) ? $chunkSize : 50;
            $fromUpdateId = $startUpdateId + 1;

            /* @var Listrak_Remarketing_Helper_Review_Update $updateHelper */
            $updateHelper = Mage::helper('remarketing/review_update');
            $collection = $updateHelper->getRatingSummaryListCollection($getStoreId);
            $collection->getSelect()
                ->where('review_entity_summary.entity_type = ?', 1)
                ->where("updatetime.update_id >= ?", $fromUpdateId)
                ->order('updatetime.update_id ' . Varien_Db_Select::SQL_ASC)
                ->limit($getChunkSize);

            return $this->_ratingSummariesFromCollection($getStoreId, $collection);
        } catch (Exception $e) {
            throw $helper->generateAndLogException(
                "Exception occurred in API call: " . $e->getMessage(), $e
            );
        }
    }

    /**
     * Retrieve rating summary information
     *
     * @param int   $storeId    Magento store ID
     * @param mixed $collection Collection of rating summaries
     *
     * @return array
     */
    private function _ratingSummariesFromCollection($storeId, $collection)
    {
        $ratingSummaries = array();

        /* @var Mage_Review_Model_Review_Summary $ratingSummary */
        foreach ($collection as $ratingSummary) {
            $productId = $ratingSummary->getEntityPkValue();

            /* @var Mage_Rating_Model_Resource_Rating_Collection $ratingCollection */
            $ratingCollection = Mage::getModel('rating/rating')
                ->getResourceCollection();
            $ratingCollection->setStoreFilter($storeId)
                ->load();
            $ratingCollection->addEntitySummaryToItem($productId, $storeId);

            $ratings = array();

            /* @var Mage_Rating_Model_Rating $rating */
            foreach ($ratingCollection as $rating) {
                array_push(
                    $ratings,
                    array(
                        "rating_id" => $rating->getRatingId(),
                        "rating_code" => $rating->getRatingCode(),
                        "rating" => round($rating->getSummary() / 20, 4)
                    )
                );
            }

            array_push(
                $ratingSummaries,
                array(
                    "update_id" => $ratingSummary->getUpdateId(),
                    "rating_summary_id" => $ratingSummary->getPrimaryId(),
                    "product_id" => $productId,
                    "updated_at" => $ratingSummary->getUpdatedAt(),
                    "total_reviews" => $ratingSummary->getReviewsCount(),
                    "rating" => round($ratingSummary->getRatingSummary() / 20, 4),
                    "ratings" => $ratings
                )
            );
        }

        return $ratingSummaries;

    }

    /**
     * Retrieve deleted reviews
     *
     * @param int $chunkSize     Page size
     * @param int $startDeleteId Cursor
     *
     * @return array
     *
     * @throws Exception
     */
    public function reviewDeleteList($chunkSize, $startDeleteId)
    {
        try {
            /* @var Mage_Core_Model_Resource $mageResource */
            $mageResource = Mage::getSingleton('core/resource');
            $dbRead = $mageResource->getConnection('core_read');

            $getChunkSize = is_numeric($chunkSize) ? $chunkSize : 50;
            $fromDeleteId = $startDeleteId + 1;

            /* @var Listrak_Remarketing_Model_Mysql4_Review_Update_Collection $collection */
            $collection = Mage::getModel('listrak/review_update')
                ->getCollection()
                ->productReviewsOnly()
                ->deletedRowsOnly();

            $allReviewIDs = $dbRead
                ->select()
                ->from(
                    array('review' => $mageResource->getTableName('review/review')),
                    'review.review_id'
                );

            $collection->getSelect()
                ->where(
                    'NOT EXISTS ('
                    . $allReviewIDs->where('main_table.review_id = review.review_id')
                    . ')'
                )
                ->where("update_id >= ?", $fromDeleteId)
                ->limit($getChunkSize);

            $deletedReviews = array();
            foreach ($collection as $deletedReview) {
                array_push(
                    $deletedReviews,
                    array(
                        "delete_id" => $deletedReview->getUpdateId(),
                        "review_id" => $deletedReview->getReviewId()
                    )
                );
            }

            return $deletedReviews;
        } catch (Exception $e) {
            /* @var Listrak_Remarketing_Helper_Data $helper */
            $helper = Mage::helper('remarketing');

            throw $helper->generateAndLogException(
                "Exception occurred in API call: " . $e->getMessage(), $e
            );
        }
    }

    /**
     * Purge old entries to the review update collection
     *
     * @param int $purgeBeforeDays Days to keep
     *
     * @return array
     *
     * @throws Exception
     */
    public function reviewUpdatePurge($purgeBeforeDays)
    {
        try {
            /* @var Mage_Core_Model_Resource $mageResource */
            $mageResource = Mage::getSingleton('core/resource');
            $dbWrite = $mageResource->getConnection('core_write');

            $doPurgeBeforeDays
                = is_numeric($purgeBeforeDays) ? $purgeBeforeDays : 30;
            $purgeBefore
                = $doPurgeBeforeDays > 0
                    ? gmdate('Y-m-d H:i:s', strtotime("-{$doPurgeBeforeDays} days"))
                    : gmdate('Y-m-d H:i:s');

            $rowsDeleted = $dbWrite->delete(
                $mageResource->getTableName('listrak/review_update'),
                array('activity_time < ?' => $purgeBefore)
            );

            return array(
                "count" => $rowsDeleted,
                "before" => $purgeBefore
            );
        } catch (Exception $e) {
            /* @var Listrak_Remarketing_Helper_Data $helper */
            $helper = Mage::helper('remarketing');

            throw $helper->generateAndLogException(
                "Exception occurred in API call: " . $e->getMessage(), $e
            );
        }
    }

    /**
     * Retrieve review statuses
     *
     * @return array
     */
    protected function getStatuses()
    {
        $statuses = array();

        /* @var Mage_Review_Model_Review $reviewModel */
        $reviewModel = Mage::getModel('review/review');

        /* @var Mage_Review_Model_Resource_Review_Status_Collection $collection */
        $collection = $reviewModel->getStatusCollection();

        /* @var Mage_Review_Model_Review_Status $status */
        foreach ($collection->getItems() as $status) {
            $statuses[$status->getStatusId()] = $status->getStatusCode();
        }

        return $statuses;
    }

    /**
     * Retrieve all ratings
     *
     * @return array
     */
    protected function getRatings()
    {
        $ratings = array();

        /* @var Mage_Rating_Model_Rating $ratingModel */
        $ratingModel = Mage::getModel('rating/rating');

        /* @var Mage_Rating_Model_Resource_Rating_Collection $collection */
        $collection = $ratingModel->getResourceCollection();

        /* @var Mage_Rating_Model_Rating $rating */
        foreach ($collection->getItems() as $rating) {
            $ratings[$rating->getRatingId()] = $rating->getRatingCode();
        }

        return $ratings;
    }
}

