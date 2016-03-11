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
 * Class Listrak_Remarketing_Block_Conversion_Order
 */
class Listrak_Remarketing_Block_Conversion_Order
    extends Listrak_Remarketing_Block_Conversion_Abstract
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

            $this->addLine(
                "_ltk.Order.SetCustomer("
                . $this->toJsString($this->getEmailAddress()) . ", "
                . $this->toJsString($this->getFirstName()) . ", "
                . $this->toJsString($this->getLastName()) . ");"
            );

            $this->addLine(
                "_ltk.Order.OrderNumber = "
                .$this->toJsString($this->getOrderConfirmationNumber())
                . ";"
            );

            $order = $this->getOrder();

            $subtotal = $order->getSubtotal();
            $this->addLine("_ltk.Order.ItemTotal = {$this->toJsString($subtotal)};");

            $shipping = $order->getShippingAmount();
            $this->addLine(
                "_ltk.Order.HandlingTotal = {$this->toJsString($shipping)};"
            );

            $tax = $order->getTaxAmount();
            $this->addLine("_ltk.Order.TaxTotal = {$this->toJsString($tax)};");

            $total = $order->getGrandTotal();
            $this->addLine("_ltk.Order.OrderTotal = {$this->toJsString($total)};");

            foreach ($this->getOrderItems() as $item) {
                $this->addLine(
                    "_ltk.Order.AddItem("
                    . $this->toJsString($item->getSku()) . ", "
                    . $this->toJsString((int)$item->getQtyOrdered()) . ", "
                    . $this->toJsString($item->getPrice()) . ");"
                );
            }

            $this->addLine("_ltk.Order.Submit();");

            return parent::_toHtml();
        } catch(Exception $e) {
            $this->getLogger()->addException($e);
            return '';
        }
    }
}
