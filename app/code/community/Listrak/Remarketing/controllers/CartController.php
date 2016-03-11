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
 * Class Listrak_Remarketing_CartController
 */
class Listrak_Remarketing_CartController
    extends Mage_Core_Controller_Front_Action
{
    private $_ltkSession = false;

    /**
     * Index action
     *
     * @return $this
     */
    public function indexAction()
    {
        return $this;
    }

    /**
     * Reload cart action
     *
     * Processes cart reload to give the customer the cart
     * contents that are found in the email the link was clicked
     * from, if the constraints allow
     *
     * @return Listrak_Remarketing_CartController
     */
    public function reloadAction()
    {
        try {
            /* @var Mage_Checkout_Model_Session $checkout */
            $checkout = Mage::getSingleton('checkout/session');

            /* @var Mage_Customer_Model_Session $cust */
            $cust = Mage::getSingleton('customer/session');

            /* @var Mage_Checkout_Helper_Cart $checkoutHelper */
            $checkoutHelper = Mage::helper('checkout/cart');
            $chkQuote = $checkoutHelper->getQuote();

            $ltksid = $this->_getLtksid();
            if (!$ltksid) {
                return $this->_redirectAfterReload();
            }
            
            if ($this->_isUid($ltksid)) {
                if ($chkQuote && $chkQuote->getId()) {
                    $this->_redirectAfterReload();
                }

                /* @var Listrak_Remarketing_Helper_Data $helper */
                $helper = Mage::helper('remarketing');

                if (!$helper->trackingTablesExist()) {
                    return $this->_redirectAfterReload();
                }

                /* @var Mage_Core_Model_Cookie $cookies */
                $cookies = Mage::getModel('core/cookie');
                $ltksidcookie = $cookies->get('ltksid');

                if (!empty($ltksidcookie) && $ltksidcookie == $ltksid) {
                    return $this->_redirectAfterReload();
                }

                $ltksession = $this->_getSession();
                if ($ltksession && $ltksession->getQuoteId()
                    && $cust && $cust->isLoggedIn()
                    && $cust->getId() === $ltksession->getCustomerId()
                ) {
                    return $this->_redirectAfterReload();
                }
            } else {
                $cartLtksid = $checkout->getCartLtksid();
                $mergedLtksid = $checkout->getMergedLtksid();

                /* @var Mage_Checkout_Model_Cart $cart */
                $cart = Mage::getSingleton('checkout/cart');

                if (($cartLtksid == $ltksid || $mergedLtksid == $ltksid)
                    && $cart->getSummaryQty() > 0
                ) {
                    return $this->_redirectAfterReload();
                }
            }

            $quote = $this->_getQuote();
            if ($quote && $quote->getId()
                && $quote->getId() != $checkout->getQuoteId()
                && $quote->getIsActive()
            ) {
                if (!$chkQuote) {
                    $chkQuote = Mage::getModel('sales/quote');
                }

                $chkQuote->merge($quote)
                    ->collectTotals()
                    ->save();
                $checkout->setQuoteId($chkQuote->getId());
                $checkout->setMergedLtksid($ltksid);
            }
        } catch (Exception $ex) {
            /* @var Listrak_Remarketing_Model_Log $logger */
            $logger = Mage::getModel("listrak/log");
            $logger->addException($ex);
        }

        return $this->_redirectAfterReload();
    }

    /**
     * Retrieves the session ID from the querystring
     *
     * @return string
     */
    private function _getLtksid()
    {
        return $this->getRequest()->getParam('ltksid');
    }

    /**
     * Returns whether session ID is in UUID format
     *
     * @param string $str Session ID
     *
     * @return bool
     */
    private function _isUid($str)
    {
        return preg_match(
            '/^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/i', $str
        ) == 1;
    }

    /**
     * Retrieve session model
     *
     * @return Listrak_Remarketing_Model_Session
     */
    private function _getSession()
    {
        if ($this->_ltkSession === false) {
            $sid = $this->_getLtksid();

            /* @var Listrak_Remarketing_Helper_Data $helper */
            $helper = Mage::helper('remarketing');
            if ($helper->trackingTablesExist() && $this->_isUid($sid)) {
                /* @var Listrak_Remarketing_Model_Session */
                $ltkSession = Mage::getModel("listrak/session");
                $ltkSession->setSessionId($sid);

                /* @var Listrak_Remarketing_Model_Mysql4_Session $ltkResource */
                $ltkResource = $ltkSession->getResource();
                $ltkResource->loadBySessionId($ltkSession);

                if ($ltkSession->hasQuoteId()) {
                    $this->_ltkSession = $ltkSession;
                }
            }

            if ($this->_ltkSession === false) {
                $this->_ltkSession = null;
            }
        }

        return $this->_ltkSession;
    }

    /**
     * Retrieve Magento quote associated with the request
     *
     * @return Mage_Sales_Model_Quote
     */
    private function _getQuote()
    {
        $session = $this->_getSession();
        if ($session) {
            $storeId = Mage::app()->getStore()->getStoreId();
            $quoteId = $session->getQuoteId();
        } else {
            /* @var Listrak_Remarketing_Helper_Data $helper */
            $helper = Mage::helper('remarketing');

            $sid = $this->_getLtksid();
            $qid = $helper->urlDecrypt($sid);

            $parts = explode(' ', $qid, 2);
            if (sizeof($parts) > 1) {
                $storeId = intval($parts[0]);
                $quoteId = intval($parts[1]);
            }
        }

        if (isset($storeId) && isset($quoteId)) {
            /* @var Mage_Sales_Model_Quote $quote */
            $quote = Mage::getModel('sales/quote');

            $quote
                ->setStoreId($storeId)
                ->load($quoteId);

            if ($quote->getEntityId()) {
                return $quote;
            }
        }

        return null;
    }

    /**
     * Redirects to checkout with all querystring parameters
     *
     * @return $this
     */
    private function _redirectAfterReload()
    {
        $query = $this->getRequest()->getParams();
        unset($query["redirectUrl"]);
        unset($query["ltksid"]);

        $url = $this->getRequest()->getParam('redirectUrl');
        if (!$url) {
            $url = 'checkout/cart/';
        }

        return $this->_redirect(
            $url,
            array(
                '_query' => $query,
                '_secure' => Mage::app()->getStore()->isCurrentlySecure()
            )
        );
    }
}
