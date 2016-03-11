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
 * Class Listrak_Remarketing_Block_Adminhtml_Productattributes_Edit_Tabs
 */
class Listrak_Remarketing_Block_Adminhtml_Productattributes_Edit_Tabs
    extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initializes the block
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('productattributes_map_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('remarketing')->__('Map Attributes'));
    }

    /**
     * Prepare layout
     *
     * @return Mage_Core_Block_Abstract
     */
    public function _prepareLayout()
    {
        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');

        $attributeCodes = $this
            ->_attributeSetAttributes(
                Mage::registry('productattribute_data')
                    ->getAttributeSetId()
            );

        $this->addTab(
            'productattributes_brand',
            array(
                'label' => $helper->__('Brand'),
                'content' => $this->getLayout()->createBlock(
                    'remarketing/adminhtml_productattributes_edit_tab_form_brand'
                )->setAttributeOptions($attributeCodes)->toHtml()
            )
        );

        $this->addTab(
            'productattributes_categories',
            array(
                'label' => $helper->__('Category and Subcategory'),
                'content' => $this->getLayout()->createBlock(
                    'remarketing/adminhtml_productattributes_edit_tab_form_categories'
                )->setAttributeOptions($attributeCodes)->toHtml()
            )
        );

        return parent::_prepareLayout();
    }

    /**
     * Retrieve all attribute for am attribute set
     *
     * @param int $setId Set to process
     *
     * @return array
     */
    private function _attributeSetAttributes($setId)
    {
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->setAttributeSetFilter($setId)
            ->addVisibleFilter();

        $attributes = array();
        foreach ($collection as $value) {
            $attributes[$value->getAttributeCode()]
                = $value->getFrontendLabel()
                . ' (' . $value->getAttributeCode() . ')';
        }

        asort($attributes);

        return $attributes;
    }
}

