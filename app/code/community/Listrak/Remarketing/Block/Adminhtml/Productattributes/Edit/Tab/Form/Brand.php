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
 * Class Listrak_Remarketing_Block_Adminhtml_Productattributes_Edit_Tab_Form_Brand
 */
class Listrak_Remarketing_Block_Adminhtml_Productattributes_Edit_Tab_Form_Brand
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'productattribute_form',
            array('legend' => Mage::helper('remarketing')->__('Field information'))
        );

        $attributeCodes = array();
        $attributeCodes[''] = '- No Brand Attribute -';
        foreach ($this->getAttributeOptions() as $key => $value) {
            $attributeCodes[$key] = $value;
        }

        $fieldset->addField(
            'brand_attribute',
            'select',
            array(
                'label' => Mage::helper('remarketing')->__('Brand Attribute'),
                'name' => 'brand_attribute',
                'values' => $attributeCodes,
                'value' => Mage::registry('productattribute_data')
                    ->getBrandAttributeCode()
            )
        );

        return parent::_prepareForm();
    }
}

