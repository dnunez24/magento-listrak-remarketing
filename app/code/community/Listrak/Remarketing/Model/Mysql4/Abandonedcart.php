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
 * Class Listrak_Remarketing_Model_Mysql4_Abandonedcart
 */
class Listrak_Remarketing_Model_Mysql4_Abandonedcart
    extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Intializes resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('listrak/session', 'id');
    }

    /**
     * Inflate abandoned cart
     *
     * @param Listrak_Remarketing_Model_Abandonedcart $object Abandoned cart
     *
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _afterLoad(Listrak_Remarketing_Model_Abandonedcart $object)
    {
        $object->setSession(
            Mage::getModel("listrak/session")->load($object->getId())
        );

        $this->loadCart($object);

        return parent::_afterLoad($object);
    }

    /**
     * Inflate cart with product information
     *
     * @param Listrak_Remarketing_Model_Abandonedcart $object Abandoned cart
     *
     * @return void
     */
    protected function loadCart(Listrak_Remarketing_Model_Abandonedcart $object)
    {
        $products = array();

        /* @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getModel('sales/quote')->load($object->getQuoteId());

        foreach ($quote->getAllVisibleItems() as $item) {
            $products[] = $this->_getCartProductEntity($item, $object->getStoreId());
        }

        $object->setProducts($products);
    }

    /**
     * Retrieve product information for quote item
     *
     * @param Mage_Sales_Model_Quote_Item $item    Quote item
     * @param int                         $storeId Magento store ID
     *
     * @return array
     */
    private function _getCartProductEntity(
        Mage_Sales_Model_Quote_Item $item, $storeId
    ) {
        /* @var Listrak_Remarketing_Helper_Product $productHelper */
        $productHelper = Mage::helper('remarketing/product');

        $info = $productHelper->getProductInformationFromQuoteItem(
            $item, array('product')
        );

        $product = $productHelper->getProductEntity(
            $info->getProduct(), $storeId, false
        );
        $product["qty"] = $item->getQty();
        $product["price"] = $item->getCalculationPrice();

        if ($info->getIsBundle()) {
            $product['bundle_items'] = array();
            foreach ($item->getChildren() as $child) {
                $product['bundle_items'][]
                    = $this->_getCartProductEntity($child, $storeId);
            }
        }

        return $product;
    }

}
