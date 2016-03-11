<?php
/**
 * Listrak Remarketing Magento Extension Ver. 1.1.5
 *
 * PHP version 5
 *
 * @category  Listrak
 * @package   Listrak_Remarketing
 * @author    Listrak Magento Team <magento@listrak.com>
 * @copyright 2013 Listrak Inc
 * @license   http://s1.listrakbi.com/licenses/magento.txt License For Customer Use of Listrak Software
 * @link      http://www.listrak.com
 */

/**
 * Class Listrak_Remarketing_Block_Adminhtml_ProductAttributes_Edit
 */
class Listrak_Remarketing_Block_Adminhtml_ProductAttributes_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initialize the block
     */
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'remarketing';
        $this->_controller = 'adminhtml_productattributes';

        $this->_removeButton('delete');
    }

    /**
     * Get header text
     *
     * @return mixed
     */
    public function getHeaderText()
    {
        return Mage::registry('productattribute_data')->getAttributeSetName();
    }
}

