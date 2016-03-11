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
 * Class Listrak_Remarketing_Model_Session
 */
class Listrak_Remarketing_Model_Session extends Mage_Core_Model_Abstract
{
    /**
     * Initializes the object
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('listrak/session');
    }

    /**
     * Loads the data associated with the session cookie
     *
     * @return $this
     */
    public function loadFromCookie()
    {
        /* @var Mage_Core_Model_Cookie $cookies */
        $cookies = Mage::getModel('core/cookie');

        $ltksid = $cookies->get('ltksid');
        if (!empty($ltksid) && strlen($ltksid) > 37) {
            $ltkpk = intval(substr($ltksid, 37), 10);
            if ($ltkpk != $this->getId()) {
                $this->load($ltkpk);
                if ($this->getSessionId() !== substr($ltksid, 0, 36)) {
                    $this->setData(array());
                }
            }
        }

        return $this;
    }

    /**
     * Initialize session for the current request
     *
     * @param bool $createOnlyIfHasItems Flag to ignore empty carts
     *
     * @return $this
     *
     * @throws Exception
     */
    public function init($createOnlyIfHasItems = false)
    {
        $this->loadFromCookie();

        /* @var Mage_Core_Model_Cookie $cookies */
        $cookies = Mage::getModel('core/cookie');

        $piid = $cookies->get('personalmerchant');

        /* @var Mage_Customer_Model_Session $custSession */
        $custSession = Mage::getSingleton("customer/session");

        /* @var Mage_Checkout_Helper_Cart $cartHelper */
        $cartHelper = Mage::helper('checkout/cart');

        $cartHasItems = $cartHelper->getItemsCount() > 0;

        if (!empty($piid)) {
            $this->setPiId($piid);
        }

        if (!$this->getId()) {
            if ($createOnlyIfHasItems && !$cartHasItems) {
                return null;
            }
            $this->setCreatedAt(gmdate('Y-m-d H:i:s'));
            $this->setIsNew(true);
            $this->setHadItems($cartHasItems);
        } else {
            $this->setHadItems($this->getHadItems() || $cartHasItems);
        }

        if ($custSession->isLoggedIn()) {
            $this->setCustomerId($custSession->getId());
        }

        $quoteId = $cartHelper->getQuote()->getId();

        if ($quoteId) {
            $this->setQuoteId($quoteId);
        }

        $this->setStoreId(Mage::app()->getStore()->getStoreId());
        $this->setUpdatedAt(gmdate('Y-m-d H:i:s'));

        if (strlen($this->getIps()) > 0) {
            if (strpos($this->getIps(), $_SERVER["REMOTE_ADDR"]) === false) {
                $this->setIps($this->getIps() . "," . $_SERVER["REMOTE_ADDR"]);
            }
        } else {
            $this->setIps($_SERVER["REMOTE_ADDR"]);
        }

        if ($this->getIsNew() === true) {
            /* @var Listrak_Remarketing_Helper_Data $helper */
            $helper = Mage::helper('remarketing');

            $saved = false;
            $tryCount = 0;
            while (!$saved && $tryCount < 2) {
                $tryCount++;

                try {
                    $this->setSessionId($helper->genUuid());
                    $this->save();
                    $saved = true;
                } catch(Exception $e) {
                    /* @var Listrak_Remarketing_Model_Log $logger */
                    $logger = Mage::getModel('listrak/log');

                    $logger->addException(
                        new Exception(
                            "{QuoteID: {$this->getQuoteId()}, "
                            . "SessionID: {$this->getSessionId()}} "
                            . "Exception when attempting to save session: "
                            . $e->getMessage()
                        )
                    );
                }
            }
            
            if (!$saved) {
                throw new Exception(
                    "{QuoteID: {$this->getQuoteId()}} Failed to save session. "
                    . "See previous exceptions."
                );
            }

            $cookies->set(
                'ltksid',
                "{$this->getSessionId()}-{$this->getId()}",
                true, null, null, null, false
            );
        } else {
            $this->save();
        }

        $coreSession = Mage::getSingleton('core/session');
        if ($coreSession->getIsListrakOrderMade()) {
            $this->deleteCookie();
            $coreSession->setIsListrakOrderMade(false);
        }

        return $this;
    }

    /**
     * Loaded all captured emails
     *
     * @return void
     */
    public function loadEmails()
    {
        /* @var Listrak_Remarketing_Model_Mysql4_Session $resource */
        $resource = $this->getResource();
        $resource->loadEmails($this);
    }

    /**
     * Delete session cookie
     *
     * @return void
     */
    public function deleteCookie()
    {
        /* @var Mage_Core_Model_Cookie $cookies */
        $cookies = Mage::getModel('core/cookie');
        $cookies->delete('ltksid');
    }

    /**
     * Delete the data associated with the loaded session
     *
     * @return void
     */
    public function delete()
    {
        /* @var Listrak_Remarketing_Model_Mysql4_Session $resource */
        $resource = $this->getResource();

        $resource->deleteEmails($this->getId());
        parent::delete();
    }
}
