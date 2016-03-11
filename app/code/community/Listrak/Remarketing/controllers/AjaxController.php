<?php
/**
 * Listrak Remarketing Magento Extension Ver. 1.1.9
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
 * Class Listrak_Remarketing_AjaxController
 */
class Listrak_Remarketing_AjaxController
    extends Mage_Core_Controller_Front_Action
{
    /**
     * Respond with javascript that contains the cart contents
     *
     * @return $this
     */
    public function cartAction()
    {
        /* @var Listrak_Remarketing_Block_Tracking_Sca $tracking_cart */
        $tracking_cart = $this->getLayout()->createBlock('remarketing/tracking_sca');
        $jsResponse = $tracking_cart->getCartJavascript();

        $this->_setJavascriptResponse($jsResponse);

        return $this;
    }

    /**
     * Respond with javascript that tracks the current session
     *
     * @return $this
     */
    public function trackAction()
    {
        /* @var Listrak_Remarketing_Block_Tracking_Sca $tracking_cart */
        $tracking_cart = $this->getLayout()->createBlock('remarketing/tracking_sca');
        $jsResponse = $tracking_cart->toHtml();

        $this->_setJavascriptResponse($jsResponse);

        return $this;
    }

    /**
     * Forms the response to execute in the browser.
     *
     * @param string $body The response body
     *
     * @return void
     */
    private function _setJavascriptResponse($body)
    {
        $response = $this->getResponse();

        $response->setHeader('Content-Type', 'application/javascript', true);
        $response->setBody($body);
    }
}
