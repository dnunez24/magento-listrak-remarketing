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
 * Class Listrak_Remarketing_Block_Tracking_Click
 */
class Listrak_Remarketing_Block_Tracking_Click
    extends Listrak_Remarketing_Block_Require_Onescript
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

            $this->addLine("_ltk.Click.Submit();");

            return parent::_toHtml();
        }
        catch(Exception $e) {
            $this->getLogger()->addException($e);
            return '';
        }
    }
}
