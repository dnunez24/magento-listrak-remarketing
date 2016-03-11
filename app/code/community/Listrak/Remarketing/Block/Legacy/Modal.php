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
 * Class Listrak_Remarketing_Block_Legacy_Modal
 *
 * @deprecated OneScript now loaded separately, displaying the modal automatically
 */
class Listrak_Remarketing_Block_Legacy_Modal extends Mage_Core_Block_Text
{
    /**
     * Get page name
     *
     * @return mixed
     */
    public function getPageName()
    {
        return $this->_getData('page_name');
    }

    /**
     * Render block
     *
     * @return string
     */
    protected function _toHtml()
    {
        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');

        if (!$helper->coreEnabled()) {
            return "";
        }

        $merchantID = Mage::getStoreConfig('remarketing/modal/listrakMerchantID');
        if (!Mage::getStoreConfig('remarketing/modal/enabled')
            || strlen(Mage::getStoreConfig('remarketing/modal/scriptLocation')) < 1
            || strlen(trim($merchantID)) < 12
        ) {
            return "";
        }

        return '<script type="text/javascript">' .
            'var biJsHost = (("https:" == document.location.protocol) ? "https://" : "http://");' .
            'document.write(unescape("%3Cscript src=\'" + biJsHost + "' .
            Mage::getStoreConfig('remarketing/modal/scriptLocation') .
            '?m=' . $merchantID . '&v=1\' type=\'text/javascript\'%3E%3C/script%3E"));' .
            '</script>' .
            '<script type="text/javascript">' .
            'var _mlm = setInterval(function() { ' .
            'if(!window.jQuery) { return; }' .
            'clearInterval(_mlm);jQuery' .
            '(document).bind("ltkmodal.show", function() { ' .
            'if(typeof ecjsInit === "function") { ecjsInit(); } }); }, 100);' .
            '</script>';
    }
}
