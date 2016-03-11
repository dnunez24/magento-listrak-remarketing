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
 * Class Listrak_Remarketing_Helper_Product_Attribute_Set_Map
 */
class Listrak_Remarketing_Helper_Product_Attribute_Set_Map
    extends Mage_Core_Helper_Abstract
{
    /**
     * Ensures that all attribute sets are the Listrak settings table
     *
     * @return void
     */
    public function ensureDataConsistency()
    {
        $newSets = $this->newAttributeSetsCollection();

        // add the new product attribute sets to our table
        foreach ($newSets as $set) {
            /* @var Listrak_Remarketing_Model_Product_Attribute_Set_Map $mapModel */
            $mapModel = Mage::getModel('listrak/product_attribute_set_map');

            $mapModel->setAttributeSetId($set->getAttributeSetId());
            $mapModel->save();
        }
    }

    /**
     * Retrieves a collection of all new attribute sets
     *
     * @return Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection
     */
    public function newAttributeSetsCollection()
    {
        /* @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');

        /* @var Mage_Catalog_Model_Resource_Product $productResource */
        $productResource = Mage::getModel('catalog/product')->getResource();

        /* @var Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection $sets */
        $sets = Mage::getResourceModel(
            'eav/entity_attribute_set_collection'
        );
        $sets
            ->setEntityTypeFilter($productResource->getTypeId());

        /* @var Listrak_Remarketing_Model_Mysql4_Product_Attribute_Set_Map $setResource */
        $setResource = Mage::getResourceModel('listrak/product_attribute_set_map');

        // the sets already in the table
        $model = new Varien_Db_Select(
            $setResource->getReadConnection()
        );
        $model
            ->from(
                array('current' =>
                    $resource->getTableName('listrak/product_attribute_set_map')
                ),
                array("*")
            )
            ->where('main_table.attribute_set_id = current.attribute_set_id');

        // new product attribute sets
        $sets->getSelect()
            ->where('NOT EXISTS (' . $model . ')');

        return $sets;
    }
}

