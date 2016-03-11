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
 * Class Listrak_Remarketing_Block_Adminhtml_Productattributes_Init_Brands
 */
class Listrak_Remarketing_Block_Adminhtml_Productattributes_Init_Brands
    extends Mage_Adminhtml_Block_Template
{
    /**
     * Initialize the block
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(
            'listrak/remarketing/productattributes/form/initbrands.phtml'
        );
    }

    /**
     * Prepare layout
     *
     * @return Mage_Core_Block_Abstract
     */
    public function _prepareLayout()
    {
        $this->setChild(
            'form',
            $this->getLayout()->createBlock(
                'remarketing/adminhtml_productattributes_init_brands_form'
            )
        );
        return parent::_prepareLayout();
    }

    /**
     * Get the form HTML
     *
     * @return string
     */
    public function getFormHtml()
    {
        return $this->getChildHtml('form');
    }

    /**
     * Get form elements
     *
     * @return string
     */
    public function getFormElementsHtml()
    {
        return $this->getChildHtml('form-elements');
    }
}

