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
 * Class Listrak_Remarketing_Block_Adminhtml_Notifications
 *
 * Displays notifications in the admin portal
 */
class Listrak_Remarketing_Block_Adminhtml_Notifications extends Mage_Core_Block_Text
{
    /**
     * Render block
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = "";

        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');
        if (!$helper->checkSetupStatus()) {
            $html .= "<div class='notification-global'>The Listrak module "
                . "requires a Listrak account. Please "
                . "<a href='http://www.listrak.com/partners/magento-extension.aspx'>"
                . "fill out our form</a> to get an account. If you already have a "
                . "Listrak account, please contact your account manager or "
                . "<a href='mailto:support@listrak.com'>support@listrak.com</a>."
                . "</div>";
        }

        /* @var Mage_Core_Helper_Url $urlHelper */
        $urlHelper = Mage::helper('core/url');

        $currentUrl = $urlHelper->getCurrentUrl();
        if (strpos($currentUrl, "/adminhtml_productattributes/") === false
            && $helper->displayAttributeSetNotification()
        ) {
            /* @var Mage_Adminhtml_Helper_Data $adminHelper */
            $adminHelper = Mage::helper('adminhtml');

            $url = $adminHelper
                ->getUrl('remarketing/adminhtml_productattributes/index');

            $html .= "<div class='notification-global'>Brand attribute has not been "
                . "defined for one or more attribute sets. Please <a href='{$url}'>"
                . "click here</a>, or go to Listrak > Product Attributes "
                . "to review your current settings.</div>";
        }

        return $html;
    }
}
