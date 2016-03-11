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
 * Class Listrak_Remarketing_Model_Apiextension
 *
 * Exists to make possible an API class that handles any
 * method whose return data doesn't fit in any other defined model
 */
class Listrak_Remarketing_Model_Apiextension
    extends Mage_Core_Model_Abstract
{
    /**
     * Initializes the object
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('listrak/apiextension');
    }
}