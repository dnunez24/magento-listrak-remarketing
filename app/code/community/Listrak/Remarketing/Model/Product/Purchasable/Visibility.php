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
 * Class Listrak_Remarketing_Model_Product_Purchasable_Visibility
 *
 * Serves as the source for the config purchasability setting, and to
 * test products for purchasability
 */
class Listrak_Remarketing_Model_Product_Purchasable_Visibility
{
    /**
     * Options displayed in system config
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'off', 'label' => 'Do Not Filter'),
            array('value' => 'catalog', 'label' => 'Catalog'),
            array('value' => 'search', 'label' => 'Search'),
            array('value' => 'both', 'label' => 'Catalog and Search'),
            array('value' => 'site', 'label' => 'Site (Catalog or Search)')
        );
    }

    /**
     * Retrieve whether a product is purchasable or not
     *
     * @param Mage_Catalog_Model_Product $product Current product
     *
     * @return bool
     */
    public function isProductPurchasable(Mage_Catalog_Model_Product $product)
    {
        switch($this->_getSetting()) {
            case 'off':
                return true;

            case 'catalog':
                return $product->isVisibleInCatalog();

            case 'search':
                /* @var Mage_Catalog_Model_Product_Visibility $visibilityModel */
                $visibilityModel = Mage::getSingleton('catalog/product_visibility');
                return in_array(
                    $product->getVisibility(),
                    $visibilityModel->getVisibleInSearchIds()
                );

            case 'both':
                return Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
                    == $product->getVisibility();

            case 'site':
                return $product->isVisibleInSiteVisibility();

            default:
                return true;
        }
    }

    /**
     * Retrieve visibility setting to use in query
     *
     * @return array|int|null
     */
    public function getVisibilityFilter()
    {
        /* @var Mage_Catalog_Model_Product_Visibility $visibilityModel */
        $visibilityModel = Mage::getSingleton('catalog/product_visibility');

        switch($this->_getSetting()) {
            case 'off':
                return null;

            case 'catalog':
                return array('in' => $visibilityModel->getVisibleInCatalogIds());

            case 'search':
                return array('in' => $visibilityModel->getVisibleInSearchIds());
            case 'both':
                return Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH;

            case 'site':
                return array('in' => $visibilityModel->getVisibleInSiteIds());

            default:
                return null;
        }
    }

    /**
     * Visibility setting
     *
     * @return string
     */
    private function _getSetting()
    {
        return Mage::getStoreConfig(
            'remarketing/productcategories/purchasable_visibility'
        );
    }
}

