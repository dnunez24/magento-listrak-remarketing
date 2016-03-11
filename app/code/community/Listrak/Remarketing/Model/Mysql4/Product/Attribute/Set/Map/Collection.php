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
 * Class Listrak_Remarketing_Model_Mysql4_Product_Attribute_Set_Map_Collection
 */
class Listrak_Remarketing_Model_Mysql4_Product_Attribute_Set_Map_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    // so we don't go getting it all the time
    private $_productTypeId;

    /**
     * Initializes collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('listrak/product_attribute_set_map');

        /* @var Mage_Catalog_Model_Resource_Product $productResource */
        $productResource = Mage::getModel('catalog/product')->getResource();
        $this->_productTypeId = $productResource->getTypeId();
    }

    /**
     * Filter by map
     *
     * @param array|int $ids Filter ID(s)
     *
     * @return $this
     */
    public function addMapIdFilter($ids)
    {
        $this->getSelect()->where('map_id IN (' . $ids . ')');

        return $this;
    }

    /**
     * Filter by an attribute set
     *
     * @param int $setId Attribute set ID
     *
     * @return $this
     */
    public function addAttributeSetFilter($setId)
    {
        $this->getSelect()->where('attribute_set_id = ' . $setId);

        return $this;
    }

    /**
     * Add attribute set name to collection
     *
     * @return $this
     */
    public function addAttributeSetName()
    {
        /* @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');

        // join in with the current settings to fetch attribute codes
        $this->getSelect()
            ->join(
                array(
                    'attribute_set' => $resource->getTableName('eav/attribute_set')
                ),
                'main_table.attribute_set_id = attribute_set.attribute_set_id',
                array('attribute_set_name')
            );

        $this->getSelect()
            ->where('attribute_set.entity_type_id = ?', $this->_productTypeId);

        return $this;
    }

    /**
     * Add attribute names to collection
     *
     * @return $this
     */
    public function addAttributeNames()
    {
        /* @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');

        $attributeTable = $resource->getTableName('eav/attribute');

        // add brand attribute name
        $this->getSelect()
            ->joinLeft(
                array('brand_attribute' => $attributeTable),
                'main_table.brand_attribute_code = brand_attribute.attribute_code',
                array('brand_attribute_name' => 'frontend_label')
            );

        // add category attribute name
        $this->getSelect()
            ->joinLeft(
                array('cat_attribute' => $attributeTable),
                'main_table.category_attribute_code = cat_attribute.attribute_code',
                array('category_attribute_name' => 'frontend_label')
            );

        // add subcategory attribute name
        $this->getSelect()
            ->joinLeft(
                array('subcat_attr' => $attributeTable),
                'main_table.subcategory_attribute_code = subcat_attr.attribute_code',
                array('subcategory_attribute_name' => 'frontend_label')
            );

        $brandFilter = 'brand_attribute.entity_type_id = ' . $this->_productTypeId
            . ' OR brand_attribute.entity_type_id IS NULL';
        $categoryFilter = 'cat_attribute.entity_type_id = ' . $this->_productTypeId
            . ' OR cat_attribute.entity_type_id IS NULL';
        $subcategoryFiler = 'subcat_attr.entity_type_id = '
            . $this->_productTypeId . ' OR subcat_attr.entity_type_id IS NULL';
        $this->getSelect()
            ->where($brandFilter)
            ->where($categoryFilter)
            ->where($subcategoryFiler);

        return $this;
    }

    /**
     * Order collection by attribute set name
     *
     * @return $this
     */
    public function orderByAttributeSetName()
    {
        $this->getSelect()->order('attribute_set_name ' . Varien_Db_Select::SQL_ASC);

        return $this;
    }
}

