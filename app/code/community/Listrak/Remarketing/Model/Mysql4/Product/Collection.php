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
 * Class Listrak_Remarketing_Model_Mysql4_Product_Collection
 */
class Listrak_Remarketing_Model_Mysql4_Product_Collection
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    private $_storeFilter = null;

    private $_withResolution = true;

    /**
     * Retrieve is flat enabled flag
     * Return alvays false if magento run admin
     *
     * @return bool
     */
    public function isEnabledFlat()
    {
        return false;
    }

    /**
     * Initialize resources
     *
     */
    protected function _construct()
    {
        $this->_init('listrak/product');
        parent::_construct();
    }

    /**
     * Add store availability filter. Include availability product
     * for store website
     *
     * @param mixed $store
     * @return Listrak_Remarketing_Model_Mysql4_Product_Collection
     */
    public function addStoreFilter($store = null)
    {
        $this->_storeFilter = $store;
        parent::addStoreFilter($store);

        return $this;
    }

    public function addModifiedFilter($startDate, $endDate)
    {
        $this->addAttributeToFilter(
            'updated_at',
            array('from' => $startDate, 'to' => $endDate)
        );

        return $this;
    }

    public function disabledAttributeResolutionAfterLoad()
    {
        $this->_withResolution = false;
        return $this;
    }

    /**
     * After load method
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _afterLoad()
    {
        if ($this->_withResolution) {
            $this->_loadParentProducts();
        }

        return $this;
    }

    protected function _loadParentProducts()
    {
        $parentsById = array();

        /* @var Mage_Catalog_Model_Product_Type_Configurable $confProductModel */
        $confProductModel = Mage::getModel('catalog/product_type_configurable');

        foreach($this->_items as $product) {
            $ids = $confProductModel
                ->getParentIdsByChild($product->getEntityId());

            if (is_array($ids) && count($ids) > 0) {
                $parentId = $ids[0];
                if ($parentId != null) {
                    $parentsById[$product->getEntityId()] = $parentId;
                }
            }
        }

        $parentIds = array_unique(array_values($parentsById));

        if (count($parentsById) > 0) {
            $parentCollection = Mage::getModel('listrak/product')
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $parentIds))
                ->disabledAttributeResolutionAfterLoad();

            if ($this->_storeFilter != null) {
                $parentCollection->addStoreFilter($this->_storeFilter);
            }

            foreach($parentCollection as $parent) {
                $pid = $parent->getEntityId();

                foreach($this->_items as $product) {
                    $id = $product->getEntityId();

                    if (array_key_exists($id, $parentsById) && $parentsById[$id] == $pid) {
                        $product->setParentProduct($parent);
                    }
                }
            }
        }
    }

    public function getAttributeOptions()
    {
        $result = array();

        $allSelectAttributes = Mage::getSingleton('eav/config')
            ->getEntityType($this->getEntity()->getType())
            ->getAttributeCollection()
            ->addFieldToFilter('frontend_input', array('in' => array('select', 'multiselect')))
            ->getItems();

        foreach ($allSelectAttributes as $attr) {
            $result[$attr->getAttributeCode()] = array(
                'multiple' => $attr->getFrontendInput() == 'multiselect',
                'id' => $attr->getId());
        }

        $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setPositionOrder('asc')
            ->addFieldToFilter(
                'attribute_id',
                array('in' => array_map(function($r) { return $r['id']; }, $result)))
            ->setStoreFilter(1);

        foreach($collection as $option) {
            foreach($result as $attr) {
                if ($attr['id'] == $option->getAttributeId()) {
                    $attr['options'][$option->getOptionId()] = $option->getValue();
                    break;
                }
            }
        }

        foreach ($allSelectAttributes as $attr) {
            $attrCode = $attr->getAttributeCode();
            if (!array_key_exists('options', $result[$attrCode])) {
                $result[$attrCode]['options'] = array();
                foreach($attr->getSource()->getAllOptions() as $option) {
                    $val = $option['value'];
                    if (!is_array($val) && $val != '') {
                        $result[$attrCode]['options'][$val]
                            = $option['label'];
                    }
                }
            }
        }

        return $result;
    }
}
