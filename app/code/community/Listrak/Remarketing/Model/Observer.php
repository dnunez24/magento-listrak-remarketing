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
 * Class Listrak_Remarketing_Model_Observer
 */
class Listrak_Remarketing_Model_Observer
{
    /**
     * Process all requests
     *
     * @return $this
     */
    public function trackingInit()
    {
        try {
            /* @var Listrak_Remarketing_Helper_Data $helper */
            $helper = Mage::helper('remarketing');

            if ($helper->legacyTracking()) {
                /* @var Listrak_Remarketing_Model_Session $session */
                $session = Mage::getSingleton('listrak/session');
                $session->init(true);

                /* @var Listrak_Remarketing_Model_Click $click */
                $click = Mage::getModel('listrak/click');
                $click->checkForClick();
            }
        } catch (Exception $ex) {
            /* @var Listrak_Remarketing_Model_Log $logger */
            $logger = Mage::getModel('listrak/log');
            $logger->addException($ex);
        }

        return $this;
    }

    /**
     * Process a new order
     *
     * @return $this
     */
    public function orderPlaced()
    {
        try {
            /* @var Listrak_Remarketing_Helper_Data $helper */
            $helper = Mage::helper('remarketing');

            if ($helper->legacyTracking()) {
                Mage::getSingleton('core/session')
                    ->setIsListrakOrderMade(true);

                /* @var Listrak_Remarketing_Model_Session $session */
                $session = Mage::getSingleton('listrak/session');
                $session->init();
            }
        } catch (Exception $ex) {
            /* @var Listrak_Remarketing_Model_Log $logger */
            $logger = Mage::getModel('listrak/log');
            $logger->addException($ex);
        }

        return $this;
    }

    /**
     * Process a subscription sign-up
     *
     * @param mixed $observer Event data
     *
     * @return $this
     */
    public function subscriberSaved($observer)
    {
        try {
            /* @var Listrak_Remarketing_Helper_Data $helper */
            $helper = Mage::helper('remarketing');

            if ($helper->coreEnabled()) {
                $subscriber = $observer->getSubscriber();

                /* @var Listrak_Remarketing_Model_Subscriberupdate $updateModel */
                $updateModel = Mage::getModel("listrak/subscriberupdate");
                $updateModel->load($subscriber->getSubscriberId(), 'subscriber_id');

                if (!$updateModel->getData()) {
                    $updateModel->setSubscriberId($subscriber->getSubscriberId());
                }

                $updateModel->setUpdatedAt(gmdate('Y-m-d H:i:s'));
                $updateModel->save();
            }
        } catch (Exception $e) {
            /* @var Listrak_Remarketing_Model_Log $logger */
            $logger = Mage::getModel('listrak/log');
            $logger->addException($e);
        }

        return $this;
    }

    /**
     * Process a review update
     *
     * @param mixed $observer Event data
     *
     * @return $this
     */
    public function reviewUpdated($observer)
    {
        try {
            /* @var Listrak_Remarketing_Helper_Data $helper */
            $helper = Mage::helper('remarketing');

            if ($helper->reviewsEnabled()) {
                $review = $observer->getObject();

                /* @var Listrak_Remarketing_Model_Review_Update $updateModel */
                $updateModel = Mage::getModel('listrak/review_update');
                $updateModel->markUpdated(
                    $review->getReviewId(),
                    $review->getEntityId(),
                    $review->getEntityPkValue()
                );
            }
        } catch (Exception $e) {
            /* @var Listrak_Remarketing_Model_Log $logger */
            $logger = Mage::getModel('listrak/log');
            $logger->addException($e);
        }

        return $this;
    }

    /**
     * Process a review delete
     *
     * @param mixed $observer Event data
     *
     * @return $this
     */
    public function reviewDeleted($observer)
    {
        try {
            /* @var Listrak_Remarketing_Helper_Data $helper */
            $helper = Mage::helper('remarketing');

            if ($helper->reviewsEnabled()) {
                $review = $observer->getObject();

                /* @var Listrak_Remarketing_Model_Review_Update $updateModel */
                $updateModel = Mage::getModel('listrak/review_update');
                $updateModel->markDeleted(
                    $review->getReviewId(),
                    $review->getEntityId(),
                    $review->getEntityPkValue()
                );
            }
        } catch (Exception $e) {
            /* @var Listrak_Remarketing_Model_Log $logger */
            $logger = Mage::getModel('listrak/log');
            $logger->addException($e);
        }

        return $this;
    }

    /**
     * Process a shopping cart update
     *
     * @return $this
     */
    public function cartModified()
    {
        try {
            /* @var Listrak_Remarketing_Helper_Data $helper */
            $helper = Mage::helper('remarketing');

            if ($helper->scaEnabled()) {
                Mage::getSingleton('checkout/session')
                    ->setListrakCartModified(true);

                $helper->initCartCookie();
            }
        } catch(Exception $e) {
            /* @var Listrak_Remarketing_Model_Log $logger */
            $logger = Mage::getModel('listrak/log');
            $logger->addException($e);
        }

        return $this;
    }

    /**
     * Reset data associated with Listrak's customer tracking
     *
     * @return $this
     */
    public function resetCustomerTracking()
    {
        Mage::getSingleton('customer/session')
            ->unsListrakCustomerTracked();

        return $this;
    }
}
