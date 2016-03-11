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
 * Class Listrak_Remarketing_Block_Adminhtml_Productattributes_Init_Brands_Form
 */
class Listrak_Remarketing_Block_Adminhtml_Productattributes_Init_Brands_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id' => 'bulk_form',
                'action' => $this->getUrl('*/*/bulkassign'),
                'method' => 'post'
            )
        );

        $attributeCodes = array();
        $attributeCodes[''] = '- No Brand Attribute -';
        foreach ($this->availableAttributes() as $key => $value) {
            $attributeCodes[$key] = $value;
        }

        $form->addField(
            'bulkassign_attribute',
            'select',
            array(
                'label' => Mage::helper('remarketing')->__('Attribute'),
                'name' => 'bulkassign_attribute',
                'values' => $attributeCodes
            )
        );

        $form->addField(
            'bulkassign_submit',
            'button',
            array(
                'class' => 'form-button',
                'value' => Mage::helper('remarketing')->__('Set')
            )
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Attributes that could be the brand attribute
     *
     * @return array
     */
    public function availableAttributes()
    {
        $everything = Mage::registry('productattribute_sets');
        $attributes = array();

        /* @var Listrak_Remarketing_Model_Product_Attribute_Set_Map $item */
        foreach ($everything as $item) {
            if ($item->getBrandAttributeCode() == null) {
                //get the attributes for this attribute set
                //and add them to the attributes array
                $collection = Mage::getResourceModel(
                    'catalog/product_attribute_collection'
                )
                    ->setAttributeSetFilter($item->getAttributeSetId())
                    ->addVisibleFilter();
                foreach ($collection as $attribute) {
                    if (!array_key_exists(
                        $attribute->getAttributeCode(), $attributes
                    )) {
                        $attributes[$attribute->getAttributeCode()]
                            = $attribute->getFrontendLabel()
                            . ' (' . $attribute->getAttributeCode() . ')';
                    }
                }
            }
        }

        asort($attributes);

        return $attributes;
    }
}

