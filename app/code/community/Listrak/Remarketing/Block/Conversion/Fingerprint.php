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
 * Class Listrak_Remarketing_Block_Conversion_Fingerprint
 */
class Listrak_Remarketing_Block_Conversion_Fingerprint
    extends Listrak_Remarketing_Block_Require_Sca
{
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

            /* @var Listrak_Remarketing_Helper_Data $helper */
            $helper = Mage::helper('remarketing');

            return '<img src="'
                . $helper->getFingerprintImageUrl()
                . '" width="1" height="1" style="position: absolute" />';
        } catch(Exception $e) {
            $this->getLogger()->addException($e);
            return '';
        }
    }
}
