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
 * Class Listrak_Remarketing_Block_Adminhtml_EmailCapture_Edit_Tabs
 */
class Listrak_Remarketing_Block_Adminhtml_EmailCapture_Edit_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initializes the block
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('emailcapture_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('remarketing')->__('Field Information'));
    }

    /**
     * Before HTML output
     *
     * Adds necessary UI elements
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_section',
            array(
                'label' => Mage::helper('remarketing')->__('Field Information'),
                'title' => Mage::helper('remarketing')->__('Field Information'),
                'content' => $this->getLayout()->createBlock(
                    'remarketing/adminhtml_emailcapture_edit_tab_form'
                )->toHtml(),
            )
        );

        return parent::_beforeToHtml();
    }
}