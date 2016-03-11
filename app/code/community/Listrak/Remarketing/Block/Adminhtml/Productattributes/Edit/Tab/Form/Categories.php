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
 * Class Listrak_Remarketing_Block_Adminhtml_Productattributes_Edit_Tab_Form_Categories
 */
class Listrak_Remarketing_Block_Adminhtml_Productattributes_Edit_Tab_Form_Categories
    extends Mage_Adminhtml_Block_Widget
{
    /**
     * Initializes the block
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(
            'listrak/remarketing/productattributes/form/categories.phtml'
        );

        $this->setModel(Mage::registry('productattribute_data'));
    }

    /**
     * Retrieve category source
     *
     * @return mixed
     */
    public function getCategoriesSource()
    {
        if ($this->getUseConfigCategoriesSource()) {
            return $this->getConfigCategoriesSource();
        }

        return $this->getModel()->getCategoriesSource();
    }

    /**
     * Retrieve stored category source
     *
     * @return mixed
     */
    public function getConfigCategoriesSource()
    {
        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');
        return $helper->categoriesSource();
    }

    /**
     * Retrieve whether the config category is used
     *
     * @return mixed
     */
    public function getUseConfigCategoriesSource()
    {
        return $this->getModel()->getUseConfigCategoriesSource();
    }

    /**
     * Retrieve category attribute code
     *
     * @return mixed
     */
    public function getCategoryAttributeCode()
    {
        return $this->getModel()->getCategoryAttributeCode();
    }

    /**
     * Retrieve subcategory attribute code
     *
     * @return mixed
     */
    public function getSubcategoryAttributeCode()
    {
        return $this->getModel()->getSubcategoryAttributeCode();
    }
}

