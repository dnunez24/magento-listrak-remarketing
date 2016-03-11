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
 * Class Listrak_Remarketing_Block_Adminhtml_EmailCapture_Edit
 */
class Listrak_Remarketing_Block_Adminhtml_EmailCapture_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Initializes the block
     */
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'remarketing';
        $this->_controller = 'adminhtml_emailcapture';

        $this->_updateButton(
            'save', 'label',
            Mage::helper('remarketing')->__('Save Field')
        );

        $this->_updateButton(
            'delete', 'label',
            Mage::helper('remarketing')->__('Delete Field')
        );
    }

    /**
     * Gets header text for block
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('emailcapture_data')
            && Mage::registry('emailcapture_data')->getId()
        ) {
            return Mage::helper('remarketing')->__("Edit Field");
        } else {
            return Mage::helper('remarketing')->__('Add Field');
        }
    }
}