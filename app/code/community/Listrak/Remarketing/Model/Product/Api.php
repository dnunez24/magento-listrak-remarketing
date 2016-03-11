<?php
/**
 * Listrak Remarketing Magento Extension Ver. 1.1.9
 *
 * PHP version 5
 *
 * @category  Listrak
 * @package   Listrak_Remarketing
 * @author    Listrak Magento Team <magento@listrak.com>
 * @copyright 2014 Listrak Inc
 * @license   http://s1.listrakbi.com/licenses/magento.txt License For Customer Use of Listrak Software
 * @link      http://www.listrak.com
 */

/**
 * Class Listrak_Remarketing_Model_Product_Api
 */
class Listrak_Remarketing_Model_Product_Api
    extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Retrieve product data
     *
     * @param int     $storeId        Magento store ID
     * @param int     $perPage        Page size
     * @param int     $page           Cursor
     * @param boolean $withAttributes Retrieve attributes
     *
     * @return array
     *
     * @throws Exception
     */
    public function products($storeId = 1, $perPage = 50, $page = 1, $withAttributes = false)
    {
        Mage::app()->setCurrentStore($storeId);

        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');
        $helper->requireCoreEnabled();

        if (!is_numeric($storeId)
            || !is_numeric($perPage)
            || !is_numeric($page)
        ) {
            throw new Exception("Bad request parameters.");
        }

        try {
            /* @var Mage_Catalog_Model_Resource_Product_Collection $collection */
            $collection = $this->_productCollection($storeId, $perPage, $page);

            $results = array();

            /* @var Listrak_Remarketing_Helper_Product $productHelper */
            $productHelper = Mage::helper('remarketing/product');

            $attributeOptions = null;
            if ($withAttributes) {
                $attributeOptions = $collection->getAttributeOptions();
            }
            $productHelper->setAttributeOptions($withAttributes, $attributeOptions);

            foreach ($collection as $product) {
                $results[] = $productHelper->getProductEntity($product, $storeId);
            }

            return $results;
        } catch (Exception $e) {
            throw $helper->generateAndLogException(
                "Exception occurred in API call: " . $e->getMessage(), $e
            );
        }
    }

    /**
     * Retrieve updated products' data
     *
     * @param int      $storeId        Magento store ID
     * @param datetime $startDate      Date constraint, lower
     * @param datetime $endDate        Date constraint, upper
     * @param int      $perPage        Page size
     * @param int      $page           Cursor
     * @param boolean  $withAttributes Retrieve attributes
     *
     * @return array
     *
     * @throws Exception
     */
    public function updates(
        $storeId = 1, $startDate = null, $endDate = null,
        $perPage = 50, $page = 1, $withAttributes = false
    ) {
        Mage::app()->setCurrentStore($storeId);

        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');
        $helper->requireCoreEnabled();

        if (!is_numeric($storeId)
            || !strtotime($startDate)
            || !strtotime($endDate)
            || !is_numeric($perPage)
            || !is_numeric($page)
        ) {
            throw new Exception("Bad request parameters.");
        }

        try {
            /* @var Mage_Catalog_Model_Resource_Product_Collection $collection */
            $collection = $this->_productCollection($storeId, $perPage, $page, $startDate, $endDate);

            $results = array();

            /* @var Listrak_Remarketing_Helper_Product $productHelper */
            $productHelper = Mage::helper('remarketing/product');

            $attributeOptions = null;
            if ($withAttributes) {
                $attributeOptions = $collection->getAttributeOptions();
            }
            $productHelper->setAttributeOptions($withAttributes, $attributeOptions);

            foreach ($collection as $product) {
                $result = $productHelper->getProductEntity($product, $storeId);
                $result['updated_at'] = $product->getUpdatedAt();
                $results[] = $result;
            }

            return $results;
        } catch (Exception $e) {
            throw $helper->generateAndLogException(
                "Exception occurred in API call: " . $e->getMessage(), $e
            );
        }
    }

    /**
     * Get requested page of products
     *
     * @param int      $storeId   Magento store ID
     * @param int      $perPage   Page size
     * @param int      $page      Cursor
     * @param datetime $startDate Date constraint, lower
     * @param datetime $endDate   Date constraint, upper
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    private function _productCollection(
        $storeId = 1, $perPage = 50, $page = 1, $startDate = null, $endDate = null
    ) {
        /* @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('listrak/product')->getCollection();

        $collection->addStoreFilter($storeId)
            ->addAttributeToSelect('*');

        $collectionOrder = array();
        if ($startDate != null) {
            $collection->addModifiedFilter($startDate, $endDate);
            $collectionOrder[] = 'e.updated_at ASC';
        }

        $collectionOrder[] = 'e.entity_id ASC';
        $collection->getSelect()
            ->order($collectionOrder);

        $collection->setPageSize($perPage)
            ->setCurPage($page)
            ->load();

        /* @var Mage_Cataloginventory_Model_Stock $stockModel */
        $stockModel = Mage::getModel('cataloginventory/stock');
        $stockModel->addItemsToProducts($collection);

        return $collection;
    }

    /**
     * Retrieve purchasable products
     *
     * @param int $storeId Magento store ID
     * @param int $perPage Page size
     * @param int $page    Cursor
     *
     * @return array
     */
    public function purchasable($storeId = 1, $perPage = 50, $page = 1)
    {
        Mage::app()->setCurrentStore($storeId);

        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');
        $helper->requireCoreEnabled();

        /* @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getModel('listrak/product')->getCollection();
        $collection->addStoreFilter($storeId)
            ->addAttributeToSelect('visibility', 'left')
            ->addAttributeToFilter(
                'status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED
            );

        /* @var Listrak_Remarketing_Model_Product_Purchasable_Visibility $visModel */
        $visModel = Mage::getSingleton('listrak/product_purchasable_visibility');

        $purchasableFilter = $visModel->getVisibilityFilter();

        if ($purchasableFilter) {
            /* @var Mage_Core_Model_Resource $resource */
            $resource = Mage::getSingleton('core/resource');

            $dbRead = $resource->getConnection('core_read');

            $productToParentQuery = $dbRead->select()
                ->from(
                    Mage::getResourceSingleton('catalog/product_type_configurable')
                        ->getMainTable(),
                    array('product_id', 'parent_id')
                )
                ->group('product_id');

            /* @var Mage_Catalog_Model_Resource_Product_Collection $parents */
            $parents = Mage::getModel('catalog/product')->getCollection();
            $parents
                ->addStoreFilter($storeId)
                ->addAttributeToSelect('visibility', 'left')
                ->addAttributeToSelect('status', 'inner');

            return $this->_retrievePurchasablePage(
                $this->_purchasableQueryHelper(
                    $dbRead, $collection, $productToParentQuery, $parents,
                    $purchasableFilter
                ),
                $page, $perPage
            );
        } else {
            $query = $collection->getSelect();
            $query->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('e.entity_id', 'e.sku'));
            return $this->_retrievePurchasablePage($query, $page, $perPage);
        }
    }

    /**
     * Constructs the query returning purchasable products
     *
     * @param Varien_Db_Adapter_Interface $dbRead               Read connection
     * @param Varien_Data_Collection_Db   $productCollection    Products
     * @param Varien_Db_Select            $productToParentQuery Parent resolution
     * @param Varien_Data_Collection_Db   $parentCollection     Parents
     * @param array                       $purchasableFilter    Visibility filter
     *
     * @return Varien_Db_Select
     */
    private function _purchasableQueryHelper(
        $dbRead, $productCollection, $productToParentQuery, $parentCollection,
        $purchasableFilter
    ) {
        $productQuery = $productCollection->getSelect();

        $query = $dbRead->select();
        $query
            ->from(
                array('product' => new Zend_Db_Expr("({$productQuery})")),
                array('entity_id', 'sku')
            )
            ->joinLeft(
                array(
                    'product_to_parent' => new Zend_Db_Expr(
                        "({$productToParentQuery})"
                    )
                ),
                'product_to_parent.product_id = product.entity_id',
                array()
            )
            ->joinLeft(
                array(
                    'parent' => new Zend_Db_Expr(
                        "({$parentCollection->getSelect()})"
                    )
                ),
                $dbRead->quoteInto(
                    'parent.entity_id = product_to_parent.parent_id '
                    . 'AND parent.type_id = ?',
                    Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                ),
                array()
            )
            ->where(
                'COALESCE(parent.visibility, product.visibility) IN (?)',
                $purchasableFilter
            )
            ->where(
                'parent.status IS NULL OR parent.status = ?',
                Mage_Catalog_Model_Product_Status::STATUS_ENABLED
            );

        return $query;
    }

    /**
     * Retrieve purchasable products from the database
     *
     * @param Varien_Db_Select $query   Query
     * @param int              $page    Cursor
     * @param int              $perPage Page size
     *
     * @return array
     */
    private function _retrievePurchasablePage(
        Varien_Db_Select $query, $page, $perPage
    ) {
        $query->limit($perPage, $perPage * ($page - 1));
        return $query->query()->fetchAll();
    }
}
