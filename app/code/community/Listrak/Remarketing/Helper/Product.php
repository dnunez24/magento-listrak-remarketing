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
 * Class Listrak_Remarketing_Helper_Product
 */
class Listrak_Remarketing_Helper_Product
    extends Mage_Core_Helper_Abstract
{
    /* @var Mage_Catalog_Model_Product[] $_parentsById */
    private $_parentsById = array();

    /* @var string[] $_urlsById */
    private $_urlsById = array();

    /**
     * Attribute set options
     * @var array
     */
    private $_attributeSets = null;

    /**
     * Flag that enables fetching of all attributes' values
     * @var boolean
     */
    private $_retrieveAttributes = null;
    
    /**
     * Options to resolve attribute from selected ID to text
     * @var array
     */
    private $_attributeOptions = null;

    /* @var Mage_Catalog_Model_Category[] $_categories */
    private $_categories = array();

    /* @var bool $_useConfigurableParentImages */
    private $_useConfigurableParentImages = null;

    /**
     * Categories to skip because they have been disabled
     * or are set to be ignored
     * @var int[]
     */
    private $_skipCategories = null;

    /**
     * Inflate an API object from a product object
     *
     * @param Mage_Catalog_Model_Product $product       Product
     * @param int                        $storeId       Magento store ID
     * @param bool                       $includeExtras Retrieve all information
     *
     * @return array
     */
    public function getProductEntity(
        Mage_Catalog_Model_Product $product, $storeId, $includeExtras = true
    ) {
        $result = array();

        $result['entity_id'] = $product->getEntityId();
        $result['sku'] = $product->getSku();
        $result['name'] = $product->getName();
        $result['price'] = $product->getPrice();
        $result['special_price'] = $product->getSpecialPrice();
        $result['special_from_date'] = $product->getSpecialFromDate();
        $result['special_to_date'] = $product->getSpecialToDate();
        $result['cost'] = $product->getCost();
        $result['description'] = $product->getDescription();
        $result['short_description'] = $product->getShortDescription();
        $result['weight'] = $product->getWeight();
        if ($product->isVisibleInSiteVisibility()) {
            $result['url_path'] = $this->_getProductUrlWithCache($product);
        }

        $parentProduct = $this->_getParentProduct($product);
        if ($parentProduct != null) {
            $result['parent_id'] = $parentProduct->getEntityId();
            $result['parent_sku'] = $parentProduct->getSku();

            if (!$product->isVisibleInSiteVisibility()) {
                $result['name'] = $parentProduct->getName();

                if ($parentProduct->isVisibleInSiteVisibility()) {
                    $result['url_path']
                        = $this->_getProductUrlWithCache($parentProduct);
                }
            }

            if ($includeExtras && $this->_isConfigurableProduct($parentProduct)) {
                $result['purchasable']
                    = $this->_isPurchasable($product, $parentProduct);

                /* @var Mage_Catalog_Model_Product_Type_Configurable $typeInst */
                $typeInst = $parentProduct->getTypeInstance(true);
                $attributes = $typeInst
                    ->getUsedProductAttributes($parentProduct);

                /* @var Mage_Eav_Model_Entity_Attribute_Abstract $attribute */
                foreach ($attributes as $attribute) {
                    if (!array_key_exists('configurable_attributes', $result)) {
                        $result['configurable_attributes'] = array();
                    }

                    $result['configurable_attributes'][]
                        = array('attribute_name' => $attribute->getAttributeCode());
                }
            }
        }

        if (!isset($result['purchasable'])) {
            $result['purchasable'] = $this->_isPurchasable($product);
        }

        $images = $this->_getProductImages($product);
        if (isset($images['image'])) {
            $result['image'] = $images['image'];
        }
        if (isset($images['small_image'])) {
            $result['small_image'] = $images['small_image'];
        }
        if (isset($images['thumbnail'])) {
            $result['thumbnail'] = $images['thumbnail'];
        }

        if ($includeExtras) {
            $metas = $this->_getMetas($storeId, $product, $parentProduct);
            if ($metas != null) {
                if (isset($metas['meta3'])) {
                    $result['meta3'] = $metas['meta3'];
                }
                if (isset($metas['meta4'])) {
                    $result['meta4'] = $metas['meta4'];
                }
                if (isset($metas['meta5'])) {
                    $result['meta5'] = $metas['meta5'];
                }
            }

            // Brand and Category
            $brandCatProduct = $product;
            if ($parentProduct && !$product->isVisibleInSiteVisibility()) {
                $brandCatProduct = $parentProduct;
            }
            $setSettings = $this->_getProductAttributeSetSettings($brandCatProduct);

            if ($setSettings['brandAttribute'] != null) {
                $result['brand'] = $this->_getProductAttribute(
                    $brandCatProduct, $setSettings['brandAttribute']);
            }

            if ($setSettings['catFromMagento']) {
                $cats = $this->_getCategoryInformation($storeId, $brandCatProduct);
                if (isset($cats['category'])) {
                    $result['category'] = $cats['category'];
                }
                if (isset($cats['sub_category'])) {
                    $result['sub_category'] = $cats['sub_category'];
                }
            } else if ($setSettings['catFromAttributes']) {
                if ($setSettings['categoryAttribute'] != null) {
                    $result['category'] = $this->_getProductAttribute(
                        $brandCatProduct, $setSettings['categoryAttribute']);
                }
                if ($setSettings['subcategoryAttribute'] != null) {
                    $result['sub_category'] = $this->_getProductAttribute(
                        $brandCatProduct, $setSettings['subcategoryAttribute']);
                }
            }

            $result['attributes'] = $this->_getProductAttributes($product);

            // Inventory
            $result['in_stock'] = $product->isAvailable() ? "true" : "false";

            /* @var Mage_Cataloginventory_Model_Stock_Item $stockItem */
            $stockItem = $product->getStockItem();
            if ($stockItem) {
                $result['qty_on_hand'] = $stockItem->getStockQty();
            }

            // Related Products
            $result['links'] = $this->_getProductLinks($product);
        }

        $result['type'] = $product->getTypeId();

        return $result;
    }

    /**
     * Retrieve product information from a quote item object
     *
     * @param Mage_Sales_Model_Quote_Item $item           Quote item
     * @param array                       $additionalInfo Information to return
     *
     * @return Varien_Object
     */
    public function getProductInformationFromQuoteItem(
        Mage_Sales_Model_Quote_Item $item,
        $additionalInfo = array()
    ) {
        $children = $item->getChildren();
        return $this->_getProductInformationWork(
            $item, $additionalInfo, count($children) > 0, $children
        );
    }

    /**
     * Retrieve product information from an order item object
     *
     * @param Mage_Sales_Model_Order_Item $item           Order item
     * @param array                       $additionalInfo Information to return
     *
     * @return Varien_Object
     */
    public function getProductInformationFromOrderItem(
        Mage_Sales_Model_Order_Item $item, $additionalInfo = array()
    ) {
        return $this->_getProductInformationWork(
            $item, $additionalInfo,
            $item->getHasChildren(), $item->getChildrenItems()
        );
    }

    /**
     * Retrieve the relative product URL
     *
     * @param Mage_Catalog_Model_Product $product Product
     *
     * @return string
     */
    public function getProductUrl(Mage_Catalog_Model_Product $product)
    {
        /* @var Mage_Core_Model_Url $urlParser */
        $urlParser = Mage::getSingleton('core/url');

        $urlParser->parseUrl($product->getProductUrl());
        return substr($urlParser->getPath(), 1);
    }

    /**
     * Returns the image URL
     *
     * @param Mage_Catalog_Model_Product $product Product
     *
     * @return string
     */
    public function getProductImage(Mage_Catalog_Model_Product $product)
    {
        $images = $this->_getProductImages($product);

        if (isset($images['thumbnail'])) {
            return $images['thumbnail'];
        }
        if (isset($images['small_image'])) {
            return $images['small_image'];
        }
        if (isset($images['image'])) {
            return $images['image'];
        }

        return null;
    }

    public function setAttributeOptions($withAttributes, $options)
    {
        $this->_retrieveAttributes = $withAttributes;
        $this->_attributeOptions = $options;
    }

    /**
     * Retrieve product information from an object with basic information
     *
     * @param mixed $item        Object with data
     * @param array $getInfo     Additional information to retrieve
     * @param bool  $hasChildren Whether the product has children
     * @param array $children    Array of product children
     *
     * @return Varien_Object
     */
    private function _getProductInformationWork(
        $item, $getInfo, $hasChildren, $children
    ) {
        $getProduct = in_array('product', $getInfo);
        $getImage = in_array('image_url', $getInfo);
        $getLink = in_array('product_url', $getInfo);

        $result = new Varien_Object();

        $result->setProductId((int)$item->getProductId());
        $result->setIsConfigurable(false);
        $result->setIsBundle(false);
        $result->setSku($item->getSku());

        if ($this->_isConfigurableType($item->getProductType()) && $hasChildren) {
            $result->setIsConfigurable(true);

            $result->setParentId($result->getProductId());
            $result->setProductId((int)$children[0]->getProductId());
        }

        if ($this->_isBundleType($item->getProductType()) && $hasChildren) {
            $result->setIsBundle(true);

            $product = Mage::getModel('catalog/product')
                ->load($result->getProductId());
            $result->setSku($product->getSku());
            $result->setProduct($product);
        } else if ($getProduct || $getImage
            || ($getLink && !$result->getIsConfigurable())
        ) {
            $product = Mage::getModel('catalog/product')
                ->load($result->getProductId());

            $result->setProduct($product);
        }

        if ($getLink) {
            $linkProduct = $result->getProduct();
            if ($result->getIsConfigurable()) {
                $linkProduct = Mage::getModel('catalog/product')
                    ->load($result->getParentId());
            }

            $result->setProductUrl($this->getProductUrl($linkProduct));
        }

        if ($getImage) {
            $result->setImageUrl($this->getProductImage($result['product']));
        }

        return $result;
    }

    /**
     * Retrieve the product URL, with caching of the result for a request
     *
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return string
     */
    private function _getProductUrlWithCache(Mage_Catalog_Model_Product $product)
    {
        $productId = $product->getEntityId();

        if (!isset($this->_urlsById[$productId])) {
            $this->_urlsById[$productId] = $this->getProductUrl($product);
        }

        return $this->_urlsById[$productId];
    }

    /**
     * Retrieve an array of all available images for a product
     *
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return array
     */
    private function _getProductImages(Mage_Catalog_Model_Product $product)
    {
        $parent = $this->_getParentProduct($product);
        $parentIsConfigurable = $parent && $this->_isConfigurableProduct($parent);
        if ($this->_useConfigurableParentImages == null) {
            $confSetting = Mage::getStoreConfig(
                Mage_Checkout_Block_Cart_Item_Renderer_Configurable
                ::CONFIGURABLE_PRODUCT_IMAGE
            );
            $wanted = Mage_Checkout_Block_Cart_Item_Renderer_Configurable
                ::USE_PARENT_IMAGE;

            $this->_useConfigurableParentImages = $confSetting == $wanted;
        }

        $none = 'no_selection';

        $image = null;
        $smallImage = null;
        $thumbnail = null;

        if ($parentIsConfigurable && $this->_useConfigurableParentImages) {
            $image = $parent->getImage();
            $smallImage = $parent->getSmallImage();
            $thumbnail = $parent->getThumbnail();
        } else {
            $image = $product->getImage();
            if ($parent && (!$image || $image == $none)) {
                $image = $parent->getImage();
            }

            $smallImage = $product->getSmallImage();
            if ($parent && (!$smallImage || $smallImage == $none)) {
                $smallImage = $parent->getSmallImage();
            }

            $thumbnail = $product->getThumbnail();
            if ($parent && (!$thumbnail || $thumbnail == $none)) {
                $thumbnail = $parent->getThumbnail();
            }
        }

        $result = array();
        if ($image && $image != $none) {
            $result['image'] = $image;
        }
        if ($smallImage && $smallImage != $none) {
            $result['small_image'] = $smallImage;
        }
        if ($thumbnail && $thumbnail != $none) {
            $result['thumbnail'] = $thumbnail;
        }

        return $result;
    }

    /**
     * Get the parent of a configurable product
     *
     * @param Mage_Catalog_Model_Product $product Configurable product
     *
     * @return Mage_Catalog_Model_Product
     */
    private function _getParentProduct(Mage_Catalog_Model_Product $product)
    {
        if ($product->hasParentProduct())
            return $product->getParentProduct();

        /* @var Mage_Catalog_Model_Product_Type_Configurable $confProductModel */
        $confProductModel = Mage::getModel('catalog/product_type_configurable');

        $parentIds = $confProductModel
            ->getParentIdsByChild($product->getEntityId());

        if (is_array($parentIds) && count($parentIds) > 0) {
            $parentId = $parentIds[0];
            if ($parentId != null) {
                if (!array_key_exists($parentId, $this->_parentsById)) {
                    /* @var Mage_Catalog_Model_Product $parent */
                    $parent = Mage::getModel('catalog/product')
                        ->load($parentId);

                    $this->_parentsById[$parentId] = $parent;
                }
                return $this->_parentsById[$parentId];
            }
        }

        return null;
    }

    private function _getProductAttributes(Mage_Catalog_Model_Product $product)
    {
        if (!$this->_retrieveAttributes)
            return null;

        $result = array();

        $allAttributes = array_keys($product->getData());

        $hasParent = $product->hasParentProduct();
        if ($hasParent) {
            $parent = $product->getParentProduct();
            $allAttributes = array_unique(array_merge(
                $allAttributes,
                array_keys($parent->getData())));
        }

        $productAttributes = $this->_getAttributeValues($product, $allAttributes);

        if ($hasParent) {
            $parentAttributes = $this->_getAttributeValues($parent, $allAttributes);
        }

        foreach($allAttributes as $name) {
            $key = 'value';
            $value = $productAttributes[$name];
            if (is_array($value)) {
                $key = 'values';
            }

            $pkey = 'parent_value';
            $pvalue = null;
            if ($hasParent) {
                $pvalue = $parentAttributes[$name];
                if (is_array($pvalue)) {
                    $pkey = 'parent_values';
                }
            }

            if (($value !== null && $value !== "") || ($pvalue !== null && $pvalue !== "")) {
                $attr = array(
                    'attribute_name' => $name,
                    $key => $value,
                    $pkey => $pvalue
                );
                $result[] = $attr;
            }
        }

        return $result;
    }

    private function _getProductAttribute(Mage_Catalog_Model_Product $product, $attributeName)
    {
        if (!$this->_retrieveAttributes) {
            return $product
                ->getAttributeText($attributeName);
        }
        else {
            $value = $this->_getAttributeValue($product, $attributeName);
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            return $value;
        }
    }

    private function _getAttributeValues(Mage_Catalog_Model_Product $product, $attributeNames)
    {
        $result = array();
        foreach($attributeNames as $name) {
            $value = $this->_getAttributeValue($product, $name);
            $result[$name] = $value;
        }
        return $result;
    }

    private function _getAttributeValue(Mage_Catalog_Model_Product $product, $attributeName)
    {
        $value = $product->getData($attributeName);
        if (is_object($value))
            return null;

        if (array_key_exists($attributeName, $this->_attributeOptions)) {
            $options = $this->_attributeOptions[$attributeName];

            if (array_key_exists('options', $options)) {
                if ($options['multiple']) {
                    $selects = array();

                    $parts = explode(',', $value);
                    foreach($parts as $part) {
                        if (array_key_exists($part, $options['options'])) {
                            $selects[] = $options['options'][$part];
                        }
                    }

                    if (count($selects) > 0) {
                        $value = $selects;
                    }
                } else if (array_key_exists($value, $options['options'])) {
                    $value = $options['options'][$value];
                }
            }
        }

        if (is_array($value) && sizeof($value) > 0) {
            $arrValue = array();
            foreach($value as $key => $item) {
                if (is_numeric($key) && !is_object($item)) {
                    $arrValue[] = $item;
                }
            }
            if (sizeof($arrValue) == 0) {
                $arrValue = null;
            }
            $value = $arrValue;
        }

        return $value;
    }

    /**
     * Retrieve purchasable value to be returned by the API
     *
     * @param Mage_Catalog_Model_Product $product Current product
     * @param Mage_Catalog_Model_Product $parent  Parent product
     *
     * @return string
     */
    private function _isPurchasable(
        Mage_Catalog_Model_Product $product,
        Mage_Catalog_Model_Product $parent = null
    ) {
        if (!$this->_isEnabled($product)) {
            $result = false;
        } else if ($parent == null) {
            $result = $this->_isVisible($product);
        } else {
            $result = $this->_isEnabled($parent) && $this->_isVisible($parent);
        }

        return $result ? "true" : "false";
    }

    /**
     * Returns whether the product is enabled in the catalog
     *
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return bool
     */
    private function _isEnabled(Mage_Catalog_Model_Product $product)
    {
        return $product->getStatus()
            == Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
    }

    /**
     * Retrieve whether the product is purchasable according to the configuration
     *
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return bool
     */
    private function _isVisible(Mage_Catalog_Model_Product $product)
    {
        /* @var Listrak_Remarketing_Model_Product_Purchasable_Visibility $visModel */
        $visModel = Mage::getSingleton(
            'listrak/product_purchasable_visibility'
        );

        return $visModel->isProductPurchasable($product);
    }

    /**
     * Retrieve the attribute settings for a product
     *
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return array
     */
    private function _getProductAttributeSetSettings(
        Mage_Catalog_Model_Product $product
    ) {
        if ($this->_attributeSets == null) {
            $this->_attributeSets = array(0 => array(
                //default values
                'brandAttribute' => null,
                'catFromMagento' => true,
                'catFromAttributes' => false,
                'categoryAttribute' => null,
                'subcategoryAttribute' => null
            ));

            /* @var Listrak_Remarketing_Model_Mysql4_Product_Attribute_Set_Map_Collection $settings */
            $settings = Mage::getModel('listrak/product_attribute_set_map')
                ->getCollection();

            /* @var Listrak_Remarketing_Model_Product_Attribute_Set_Map $set */
            foreach ($settings as $set) {
                $this->_attributeSets[$set->getAttributeSetId()] = array(
                    'brandAttribute' =>
                        $set->getBrandAttributeCode(),
                    'catFromMagento' =>
                        $set->finalCategoriesSource() == 'default',
                    'catFromAttributes' =>
                        $set->finalCategoriesSource() == 'attributes',
                    'categoryAttribute' =>
                        $set->getCategoryAttributeCode(),
                    'subcategoryAttribute' =>
                        $set->getSubcategoryAttributeCode()
                );
            }
        }

        return array_key_exists($product->getAttributeSetId(), $this->_attributeSets)
            ? $this->_attributeSets[$product->getAttributeSetId()]
            : $this->_attributeSets[0];
    }

    /**
     * Retrieve the category and subcategory for a product
     *
     * @param int                        $storeId Magento store ID
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return array
     */
    private function _getCategoryInformation(
        $storeId, Mage_Catalog_Model_Product $product
    ) {
        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');

        $rootLevel = $helper->getCategoryRootIdForStore($storeId);
        $rootPath = array(1);
        if ($rootLevel) {
            $rootPath[] = $rootLevel;
        }

        $categoryLevel = $helper->getCategoryLevel();

        if ($this->_skipCategories == null) {
            $this->_skipCategories = array_unique(
                array_merge(
                    $helper->getInactiveCategories(),
                    $helper->getCategoriesToSkip()
                )
            );
        }

        /* @var Mage_Catalog_Model_Resource_Category_Collection $categories */
        $categories = $product->getCategoryCollection();
        $path = $this->_getFirstPathByPosition(
            $categories, $categoryLevel + 1, $rootPath
        );

        $result = array();
        if (isset($path[$categoryLevel - 1])) {
            $result['category']
                = $this->_getCategoryField($path[$categoryLevel - 1], 'name');
        }
        if (isset($path[$categoryLevel])) {
            $result['sub_category']
                = $this->_getCategoryField($path[$categoryLevel], 'name');
        }

        return $result;
    }

    /**
     * Retrieve the first active category
     *
     * @param mixed $categoryCollection All product categories
     * @param int   $maxLevel           Defines the depth of search
     * @param int[] $underPath          Partial, known good path
     *
     * @return array
     */
    private function _getFirstPathByPosition(
        $categoryCollection, $maxLevel, $underPath
    ) {
        if (sizeof($underPath) >= $maxLevel) {
            return $underPath;
        }

        $nextCategory = array();

        /* @var Mage_Catalog_Model_Category $category */
        foreach ($categoryCollection as $category) {
            $pathIds = $category->getPathIds();

            if (sizeof(array_intersect($pathIds, $this->_skipCategories)) > 0) {
                // the category tree contains a category
                // that we want skipped or is not active
                continue;
            }

            if (sizeof($pathIds) > sizeof($underPath)
                && !in_array($pathIds[sizeof($underPath)], $nextCategory)
            ) {
                $isUnderPath = true;
                for ($i = 0; $i < sizeof($underPath); $i++) {
                    if ($pathIds[$i] != $underPath[$i]) {
                        $isUnderPath = false;
                        break;
                    }
                }

                if ($isUnderPath) {
                    $nextCategory[] = $pathIds[sizeof($underPath)];
                }
            }
        }

        if (sizeof($nextCategory) == 0) {
            return $underPath;
        }

        $winnerPath = array();
        $winnerPathPosition = 0;
        foreach ($nextCategory as $categoryId) {
            $testPath = $underPath;
            $testPath[] = $categoryId;

            $testPathPosition = $this->_getCategoryField(
                $categoryId, 'position'
            );

            if (sizeof($winnerPath) == 0
                || $winnerPathPosition > $testPathPosition
            ) {
                $winnerPath = $testPath;
                $winnerPathPosition = $testPathPosition;
            }
        }

        return $this->_getFirstPathByPosition(
            $categoryCollection, $maxLevel, $winnerPath
        );
    }

    /**
     * Retrieve data from a category
     *
     * @param int    $categoryId Category ID
     * @param string $field      Category field/attribute by name
     *
     * @return mixed|null
     */
    private function _getCategoryField($categoryId, $field)
    {
        $category = $this->_getCategory($categoryId);
        if ($category != null) {
            return $category->getData($field);
        }

        return null;
    }

    /**
     * Retrieve a category by ID
     *
     * @param int $categoryId Category ID
     *
     * @return Mage_Catalog_Model_Category
     */
    private function _getCategory($categoryId)
    {
        if (array_key_exists($categoryId, $this->_categories)) {
            return $this->_categories[$categoryId];
        } else {
            $category = Mage::getModel('catalog/category');

            $category->load($categoryId);
            if ($category != null) {
                $this->_categories[$categoryId] = $category;
                return $category;
            }
        }

        return null;
    }

    /**
     * Get all linked products
     *
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return array
     */
    private function _getProductLinks(Mage_Catalog_Model_Product $product)
    {
        if (Mage::getStoreConfig(
            'remarketing/productcategories/product_links'
        ) != '1') {
            return null;
        }

        static $productTable = null;
        if ($productTable == null) {
            // this is done because a query shows up in MySQL
            // with 'SET GLOBAL SQL_MODE = ''; SET NAMES utf8;'
            // that is very costly in a loop

            /* @var Mage_Core_Model_Resource $resource */
            $resource = Mage::getModel('core/resource');

            $productTable = $resource->getTableName('catalog/product');
        }

        static $productAttrTable = null;
        if ($productAttrTable == null) {
            /* @var Mage_Catalog_Model_Product_Link $linkModel */
            $linkModel = Mage::getModel('catalog/product_link');

            $productAttrTable = $linkModel->getAttributeTypeTable('int');
        }

        $linkTypes = $this->_getLinkTypes();

        /* @var Mage_Catalog_Model_Resource_Product_Link_Collection $links */
        $links = Mage::getModel('catalog/product_link')
            ->getCollection();

        $select = $links->getSelect();

        $select->where('main_table.product_id = ?', $product->getId())
            ->where('main_table.product_id <> main_table.linked_product_id')
            ->where('main_table.link_type_id IN (?)', array_keys($linkTypes));

        $select->join(
            array('product' => $productTable),
            'main_table.linked_product_id = product.entity_id',
            'sku'
        );

        $positionJoinOn = array();
        foreach ($linkTypes as $linkTypeId => $linkType) {
            if ($linkType['positionAttributeId'] != null) {
                $adptr = $select->getAdapter();
                $joinStmt
                    = $adptr->quoteInto('main_table.link_type_id  = ?', $linkTypeId)
                    . ' AND '
                    . $adptr->quoteInto(
                        'attributes.product_link_attribute_id = ?',
                        $linkType['positionAttributeId']
                    );

                $positionJoinOn[] = $joinStmt;
            }
        }

        $joinCond
            = 'main_table.link_id = attributes.link_id AND (('
            . implode(') OR (', $positionJoinOn)
            . '))';
        $select->joinLeft(
            array('attributes' => $productAttrTable),
            $joinCond,
            array('position' => 'value')
        );

        $result = array();
        foreach ($links as $link) {
            $result[] = array(
                'link_type' => $linkTypes[$link->getLinkTypeId()]['name'],
                'sku' => $link->getSku(),
                'position' => $link->getPosition()
            );
        }

        return $result;
    }

    /**
     * Retrieve product link types
     *
     * @return array
     */
    private function _getLinkTypes()
    {
        static $_types = null;

        if ($_types == null) {
            $allLinks = array(
                Mage_Catalog_Model_Product_Link::LINK_TYPE_UPSELL =>
                    array('name' => 'up_sell', 'positionAttributeId' => null),
                Mage_Catalog_Model_Product_Link::LINK_TYPE_CROSSSELL =>
                    array('name' => 'cross_sell', 'positionAttributeId' => null),
                Mage_Catalog_Model_Product_Link::LINK_TYPE_RELATED =>
                    array('name' => 'related', 'positionAttributeId' => null),
                Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED =>
                    array('name' => 'grouped', 'positionAttributeId' => null)
            );

            foreach ($allLinks as $linkId => &$link) {
                /* @var Mage_Catalog_Model_Product_Link $linkModel */
                $linkModel = Mage::getModel('catalog/product_link');

                $linkAttributes = $linkModel->setLinkTypeId($linkId)
                    ->getAttributes();

                foreach ($linkAttributes as $attribute) {
                    if ($attribute['code'] == 'position'
                        && $attribute['type'] == 'int'
                    ) {
                        $link['positionAttributeId'] = $attribute['id'];
                        break;
                    }
                }
            }

            $_types = $allLinks;
        }

        return $_types;
    }

    /**
     * Return whether the product type is configurable
     *
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return bool
     */
    private function _isConfigurableProduct(Mage_Catalog_Model_Product $product)
    {
        return $this->_isConfigurableType($product->getTypeId());
    }

    /**
     * Return whether the product type passed in is configurable
     *
     * @param string $type Product type
     *
     * @return bool
     */
    private function _isConfigurableType($type)
    {
        return Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE == $type;
    }

    /**
     * Return whether the product type passed in is bundle
     *
     * @param string $type Product type
     *
     * @return bool
     */
    private function _isBundleType($type)
    {
        return Mage_Catalog_Model_Product_Type::TYPE_BUNDLE == $type;
    }

    /**
     * Retrieve the meta data for the current product from the meta provider
     *
     * @param int                        $storeId       Magento store ID
     * @param Mage_Catalog_Model_Product $product       Current Product
     * @param Mage_Catalog_Model_Product $parentProduct Parent Product
     *
     * @return array|null
     */
    private function _getMetas(
        $storeId,
        Mage_Catalog_Model_Product $product,
        Mage_Catalog_Model_Product $parentProduct = null
    ) {
        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');

        try {
            $provider = $helper->getMetaDataProvider();
            if ($provider) {
                return $provider->product($storeId, $product, $parentProduct);
            }
        }
        catch(Exception $e) {
            $helper->generateAndLogException(
                'Exception retrieving product meta data', $e
            );
        }

        return null;
    }
}
