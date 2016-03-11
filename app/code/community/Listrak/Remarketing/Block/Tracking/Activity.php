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
 * Class Listrak_Remarketing_Block_Tracking_Activity
 */
class Listrak_Remarketing_Block_Tracking_Activity
    extends Listrak_Remarketing_Block_Require_Activity
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

            $sku = $this->_getProductSku();
            if ($sku) {
                $this->addLine(
                    "_ltk.Activity.AddProductBrowse({$this->toJsString($sku)});"
                );
            } else {
                $this->addLine("_ltk.Activity.AddPageBrowse(location.href);");
            }

            $this->addLine("_ltk.Activity.Submit();");

            return parent::_toHtml();
        } catch(Exception $e) {
            $this->getLogger()->addException($e);

            return '';
        }
    }

    /**
     * Retrieve browsed sku, if any
     *
     * @return string|null
     */
    private function _getProductSku()
    {
        if ($this->isProductPage()) {
            $product = Mage::registry('current_product');
            if ($product) {
                return $product->getSku();
            }
        }

        return null;
    }
}
