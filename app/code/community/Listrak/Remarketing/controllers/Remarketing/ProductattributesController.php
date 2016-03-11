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
 * Class Listrak_Remarketing_Remarketing_ProductAttributesController
 */
class Listrak_Remarketing_Remarketing_ProductAttributesController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Handles marking the Listrak menu as active
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('remarketing');
        return $this;
    }

    /**
     * Requires ACL Permissions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        /* @var Mage_Admin_Model_Session $session */
        $session = Mage::getSingleton('admin/session');
        return $session->isAllowed('admin/remarketing/productattributes');
    }

    /**
     * Index action
     *
     * Show grid of selected attributes for each attribute set
     *
     * @return $this
     */
    public function indexAction()
    {
        $this->_initAction();

        try {
            /* @var Listrak_Remarketing_Helper_Product_Attribute_Set_Map $helper */
            $helper = Mage::helper('remarketing/product_attribute_set_map');

            // before we display the data, make sure all the product attribute
            // sets are found in our table - because we can't have null primary keys,
            // something that would happen otherwise
            $helper->ensureDataConsistency();

            /* @var Listrak_Remarketing_Model_Mysql4_Product_Attribute_Set_Map_Collection $sets */
            $sets = Mage::getModel('listrak/product_attribute_set_map')
                ->getCollection();
            $sets->addAttributeSetName()
                ->addAttributeNames()
                ->orderByAttributeSetName();

            Mage::register('productattribute_sets', $sets);

            $this->_addContent(
                $this->getLayout()
                    ->createBlock('remarketing/adminhtml_productattributes')
            );

            return $this->renderLayout();
        } catch (Exception $e) {
            /* @var Listrak_Remarketing_Model_Log $logger */
            $logger = Mage::getModel('listrak/log');
            $logger->addException($e);

            /* @var Mage_Adminhtml_Model_Session $adminSession */
            $adminSession = Mage::getSingleton('adminhtml/session');
            $adminSession->addError($e->getMessage());

            $this->_redirect(
                '*/*/index',
                array('id' => $this->getRequest()->getParam('id'))
            );

            return $this;
        }
    }

    /**
     * Edit action
     *
     * Assembles the edit page
     *
     * @return $this
     */
    public function editAction()
    {
        $this->_initAction();

        try {
            $mapId = $this->getRequest()->getParam('id');
            $model = Mage::getModel('listrak/product_attribute_set_map')
                ->getCollection()
                ->addAttributeSetName()
                ->addMapIdFilter($mapId)
                ->getFirstItem();

            Mage::register('productattribute_data', $model);

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this
                ->_addContent(
                    $this->getLayout()
                        ->createBlock('remarketing/adminhtml_productattributes_edit')
                )
                ->_addLeft(
                    $this->getLayout()
                        ->createBlock(
                            'remarketing/adminhtml_productattributes_edit_tabs'
                        )
                );

            return $this->renderLayout();
        } catch (Exception $e) {
            /* @var Listrak_Remarketing_Model_Log $logger */
            $logger = Mage::getModel('listrak/log');
            $logger->addException($e);

            /* @var Mage_Adminhtml_Model_Session $adminSession */
            $adminSession = Mage::getSingleton('adminhtml/session');
            $adminSession->addError(
                "An unexpected error occurred while attempting to display the form. Please try again."
            );

            $this->_redirect(
                '*/*/index',
                array('id' => $this->getRequest()->getParam('id'))
            );

            return $this;
        }
    }

    /**
     * Save action
     *
     * Save attribute set map selections
     *
     * @return $this
     */
    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            /* @var Mage_Adminhtml_Model_Session $adminSession */
            $adminSession = Mage::getSingleton('adminhtml/session');

            try {
                $mapId = $this->getRequest()->getParam('id');

                /* @var Listrak_Remarketing_Model_Product_Attribute_Set_Map $model */
                $model = Mage::getModel('listrak/product_attribute_set_map')
                    ->load($mapId);

                $model->setBrandAttributeCode(
                    $this->_nullIfEmpty($this->_getPost('brand_attribute'))
                );

                $model->setCategoriesSource(
                    $this->_getPost('categories_source')
                );

                $model->setUseConfigCategoriesSource(
                    $this->_getPost('use_config_categories_source') ? 1 : 0
                );

                $model->setCategoryAttributeCode(
                    $this->_nullIfEmpty(
                        $this->_getPost('categories_category_attribute')
                    )
                );

                $model->setSubcategoryAttributeCode(
                    $this->_nullIfEmpty(
                        $this->_getPost('categories_subcategory_attribute')
                    )
                );

                $model->save();

                $adminSession->addSuccess(
                    Mage::helper('adminhtml')->__('Item was successfully saved')
                );

                return $this->_redirect('*/*/');
            } catch (Exception $e) {
                /* @var Listrak_Remarketing_Model_Log $logger */
                $logger = Mage::getModel('listrak/log');
                $logger->addException($e);

                /* @var Mage_Adminhtml_Model_Session $adminSession */
                $adminSession = Mage::getSingleton('adminhtml/session');
                $adminSession->addError(
                    "An unexpected error occurred while attempting to save the settings. Please try again."
                );

                $this->_redirect(
                    '*/*/edit',
                    array('id' => $this->getRequest()->getParam('id'))
                );

                return $this;
            }
        }

        return $this->_redirect('*/*/');
    }

    /**
     * Bulk assign action
     *
     * Stores the brand attribute for all attribute sets that have it
     *
     * @return $this
     */
    public function bulkassignAction()
    {
        try {
            $attributeCode = $this->_nullIfEmpty(
                $this->_getPost('bulkassign_attribute')
            );

            if ($attributeCode) {
                /* @var Listrak_Remarketing_Model_Mysql4_Product_Attribute_Set_Map_Collection $sets */
                $sets = Mage::getModel('listrak/product_attribute_set_map')
                    ->getCollection();

                /* @var Listrak_Remarketing_Model_Product_Attribute_Set_Map $set */
                foreach ($sets as $set) {
                    if ($set->getBrandAttributeCode() == null) {
                        /* @var Mage_Catalog_Model_Resource_Product_Attribute_Collection $attrCollection */
                        $attrCollection = Mage::getResourceModel(
                            'catalog/product_attribute_collection'
                        );

                        $attrCount = $attrCollection
                            ->addVisibleFilter()
                            ->setAttributeSetFilter($set->getAttributeSetId())
                            ->setCodeFilter($attributeCode)
                            ->count();

                        if ($attrCount > 0) {
                            $set->setBrandAttributeCode($attributeCode);
                            $set->save();
                        }
                    }
                }
            }
        } catch (Exception $e) {
            /* @var Listrak_Remarketing_Model_Log $logger */
            $logger = Mage::getModel('listrak/log');
            $logger->addException($e);

            /* @var Mage_Adminhtml_Model_Session $adminSession */
            $adminSession = Mage::getSingleton('adminhtml/session');
            $adminSession->addError(
                "An unexpected error occurred while assigning the brand attribute."
            );
        }

        $this->_redirect('*/*/');
    }

    /**
     * Return null if the argument passed in is empty
     *
     * @param string $str String to nulify
     *
     * @return string
     */
    private function _nullIfEmpty($str)
    {
        if ($str == '') {
            return null;
        }

        return $str;
    }

    /**
     * Retrieve posted value
     *
     * @param string $key Key of value
     *
     * @return mixed
     */
    private function _getPost($key)
    {
        return $this->getRequest()->getPost($key, null);
    }
}

