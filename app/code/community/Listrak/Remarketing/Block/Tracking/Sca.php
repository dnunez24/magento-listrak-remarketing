<?php
/**
 * Listrak Remarketing Magento Extension Ver. 1.1.9
 *
 * PHP version 5
 *
 * @category  Listrak
 * @package   Listrak_Remarketing
 * @author    Listrak Magento Team <magento@listrak.com>
 * @copyright 2014 Listrak Inc
 * @license   http://s1.listrakbi.com/licenses/magento.txt License For Customer Use of Listrak Software
 * @link      http://www.listrak.com
 */

/**
 * Class Listrak_Remarketing_Block_Tracking_Sca
 */
class Listrak_Remarketing_Block_Tracking_Sca
    extends Listrak_Remarketing_Block_Require_Sca
{
    private $_initialized = false;

    /* @var Listrak_Remarketing_Model_Session $_convertSession */
    private $_convertSession = null;

    /**
     * Render block
     *
     * @return string
     */
    public function _toHtml()
    {
        try {
            if (!$this->canRender()) {
                return '';
            }

            if ($this->_hasAjaxScript()) {
                $method = "track";
                if ($this->isCartPage())
                    $method = "updateCart";

                $this->addLine(
                    "Listrak_Remarketing.{$method}();"
                );
            } else {
                $this->_addCustomerJS();
                if (!$this->_addCartJS()) {
                    $this->addLine("_ltk.SCA.Submit();");
                }
            }

            return parent::_toHtml();
        } catch(Exception $e) {
            $this->getLogger()->addException($e);

            return '';
        }
    }

    /**
     * Get the cart tracking code
     *
     * @return string
     */
    public function getCartJavascript()
    {
        $this->_ensureLoaded();

        $noSubmit = $this->_addCartJS(true);
        if (trim($this->getScript()) && !$noSubmit) {
            $this->addLine("_ltk.SCA.Submit();");
        }

        return $this->getScript(false);
    }

    private $_canRender = null;

    /**
     * Can render the block
     *
     * @return bool
     */
    public function canRender()
    {
        $this->_ensureLoaded();
        if ($this->_canRender == null) {
            $this->_canRender = parent::canRender()
                && !$this->isOrderConfirmationPage()
                && (
                    $this->_hasAjaxScript()
                    || $this->_hasCartJS()
                    || $this->_hasCustomerJS()
                );
        }

        return $this->_canRender;
    }

    /**
     * Render the AJAX line
     *
     * @return bool
     */
    private function _hasAjaxScript()
    {
        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');

        return $this->getFullPageRendering()
            && $helper->ajaxTracking();
    }

    /**
     * Has legacy session conversion code
     *
     * @return bool
     */
    private function _hasSessionToConvert()
    {
        return $this->_convertSession != null
            && $this->_convertSession->getId()
            && !$this->_convertSession->getConverted();
    }

    /**
     * Has cart tracking code
     *
     * @return bool
     */
    private function _hasCartJS()
    {
        return $this->_hasSessionToConvert()
            || Mage::getSingleton('checkout/session')->getListrakCartModified()
            || $this->isCartPage();
    }

    /**
     * Render cart tracking code
     *
     * @param bool $forceRender Flag to render even if nothing changed
     *
     * @return bool
     */
    private function _addCartJS($forceRender = false)
    {
        $noSubmit = false;

        if ($forceRender || $this->_hasCartJS()) {
            if ($this->_hasSessionToConvert()) {
                $this->addLine(
                    "_ltk.SCA.SetSessionID("
                    . $this->toJsString($this->_convertSession->getSessionId())
                    . ");"
                );

                $emails = $this->_convertSession->getEmails();

                /* @var Mage_Customer_Model_Session $custSession */
                $custSession = Mage::getSingleton('customer/session');
                if (count($emails) > 0 && !$custSession->isLoggedIn()) {
                    $this->addLine(
                        "_ltk.SCA.SetCustomer("
                        . $this->toJsString($emails[0]['email'])
                        . ", '', '');"
                    );
                }
            }

            $chkSession = Mage::getSingleton('checkout/session');

            $this->addLine("_ltk.SCA.Stage = 1;");

            if (Mage::getSingleton('checkout/cart')->getSummaryQty() > 0) {
                foreach ($this->_getCartItems() as $item) {
                    $this->addLine(
                        "_ltk.SCA.AddItemWithLinks("
                        . $this->toJsString($item->getSku()) . ", "
                        . $this->toJsString($item->getQty()) . ", "
                        . $this->toJsString($item->getPrice()) . ", "
                        . $this->toJsString($item->getName()) . ", "
                        . $this->toJsString($item->getImageUrl()) . ", "
                        . $this->toJsString($item->getProductUrl()) . ");"
                    );
                }

                $ltksid = $this->_getBasketId();
                $this->addLine("_ltk.SCA.Meta1 = {$this->toJsString($ltksid)};");
                $chkSession->setCartLtksid($ltksid);
            } else {
                $this->addLine("_ltk.SCA.ClearCart();");
                $noSubmit = true;
                // _ltk.SCA.Submit is called by _ltk.SCA.ClearCart
            }

            $chkSession->unsListrakCartModified();

            if ($this->_hasSessionToConvert()) {
                $this->_convertSession->setConverted(true);
                $this->_convertSession->save();
                $this->_convertSession->deleteCookie();
            }
        }

        return $noSubmit;
    }

    /**
     * Has customer tracking code
     *
     * @return bool
     */
    private function _hasCustomerJS()
    {
        /* @var Mage_Customer_Model_Session $custSession */
        $custSession = Mage::getSingleton('customer/session');

        return $custSession->isLoggedIn()
            && !$custSession->getListrakCustomerTracked();
    }

    /**
     * Render customer tracking code
     *
     * @return void
     */
    private function _addCustomerJS()
    {
        if ($this->_hasCustomerJS()) {
            /* @var Mage_Customer_Model_Session $custSession */
            $custSession = Mage::getSingleton('customer/session');

            $cust = $custSession->getCustomer();

            $this->addLine(
                "_ltk.SCA.SetCustomer("
                . $this->toJsString($cust->getEmail()) . ", "
                . $this->toJsString($cust->getFirstname()) . ", "
                . $this->toJsString($cust->getLastname()) . ");"
            );

            $custSession->setListrakCustomerTracked(true);
        }
    }

    /**
     * Retrieve cart items
     *
     * @return Mage_Sales_Model_Quote_Item[]
     */
    private function _getCartItems()
    {
        $result = array();

        /* @var Listrak_Remarketing_Helper_Product $productHelper */
        $productHelper = Mage::helper('remarketing/product');

        /* @var Mage_Checkout_Model_Cart $cart */
        $cart = Mage::getSingleton('checkout/cart');

        /* @var Mage_Sales_Model_Quote_Item $item */
        foreach ($cart->getQuote()->getAllVisibleItems() as $item) {
            $info = $productHelper
                ->getProductInformationFromQuoteItem(
                    $item, array('product_url', 'image_url')
                );

            $item->setSku($info->getSku());
            $item->setProductUrl($info->getProductUrl());
            $item->setImageUrl($info->getImageUrl());

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Builds the quote identifier string
     *
     * @return string
     */
    private function _getBasketId()
    {
        $storeId = Mage::app()->getStore()->getStoreId();
        $quoteId = Mage::getSingleton('checkout/session')->getQuoteId();

        $str = $storeId . ' ' . $quoteId;
        while (strlen($str) < 16) {
            // 5 for store ID, 1 for the space, and 10 for the quote ID
            $str .= ' ' . $quoteId;
        }
        $str = substr($str, 0, 16);

        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');
        return $helper->urlEncrypt($str);
    }

    /**
     * Loads session information only once
     *
     * @return void
     */
    private function _ensureLoaded()
    {
        if (!$this->_initialized) {
            /* @var Listrak_Remarketing_Helper_Data $helper */
            $helper = Mage::helper('remarketing');

            if ($helper->trackingTablesExist()) {
                /* @var Listrak_Remarketing_Model_Session $session */
                $session = Mage::getSingleton('listrak/session');
                $session->loadFromCookie();

                $this->_convertSession = $session;
            }

            if ($this->isOrderConfirmationPage()) {
                Mage::getSingleton('customer/session')
                    ->unsListrakCustomerTracked();
            }

            $this->_initialized = true;
        }
    }
}
