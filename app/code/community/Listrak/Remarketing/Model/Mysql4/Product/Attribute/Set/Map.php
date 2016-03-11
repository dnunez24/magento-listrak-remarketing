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
 * Class Listrak_Remarketing_Model_Mysql4_Product_Attribute_Set_Map
 */
class Listrak_Remarketing_Model_Mysql4_Product_Attribute_Set_Map
    extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initializes resource
     */
    protected function _construct()
    {
        $this->_init('listrak/product_attribute_set_map', 'map_id');
    }

}

