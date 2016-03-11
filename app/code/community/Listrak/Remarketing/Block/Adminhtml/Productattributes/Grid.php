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
 * Class Listrak_Remarketing_Block_Adminhtml_ProductAttributes_Grid
 */
class Listrak_Remarketing_Block_Adminhtml_ProductAttributes_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initializes the block
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('productattributesGrid');
        $this->setDefaultSort('attribute_set_name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
    }

    /**
     * Prepare collection for output
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = Mage::registry('productattribute_sets');

        // prepare visible fields
        /* @var Listrak_Remarketing_Model_Product_Attribute_Set_Map $item */
        foreach ($collection as $item) {
            $item->setFrontendCategoriesSource($item->frontendCategoriesSource());
            $item->setFrontendBrandAttribute($item->frontendBrandAttribute());
            $item->setFrontendCategoryAttribute($item->frontendCategoryAttribute());
            $item->setFrontendSubcategoryAttribute(
                $item->frontendSubcategoryAttribute()
            );
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'attribute_set',
            array(
                'header' => Mage::helper('remarketing')->__('Attribute Set Name'),
                'align' => 'left',
                'index' => 'attribute_set_name',
                'filter' => false
            )
        );

        $this->addColumn(
            'brand_attribute',
            array(
                'header' => Mage::helper('remarketing')->__('Brand Attribute'),
                'align' => 'left',
                'index' => 'frontend_brand_attribute',
                'filter' => false
            )
        );

        $this->addColumn(
            'categories_source',
            array(
                'header' => Mage::helper('remarketing')->__('Category Source'),
                'align' => 'left',
                'index' => 'frontend_categories_source',
                'filter' => false
            )
        );

        $this->addColumn(
            'category_attribute',
            array(
                'header' => Mage::helper('remarketing')->__('Category Attribute'),
                'align' => 'left',
                'index' => 'frontend_category_attribute',
                'filter' => false
            )
        );

        $this->addColumn(
            'subcategory_attribute',
            array(
                'header' => Mage::helper('remarketing')->__('Subcategory Attribute'),
                'align' => 'left',
                'index' => 'frontend_subcategory_attribute',
                'filter' => false
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * Link to edit the information in a row
     *
     * @param mixed $row Grid row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}

