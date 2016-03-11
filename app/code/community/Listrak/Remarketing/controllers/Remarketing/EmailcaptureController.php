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
 * Class Listrak_Remarketing_Remarketing_EmailCaptureController
 */
class Listrak_Remarketing_Remarketing_EmailCaptureController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Sets up visual context
     *
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('remarketing')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Fields Manager'),
                Mage::helper('adminhtml')->__('Field Manager')
            );
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
        return $session->isAllowed('admin/remarketing/emailcapture');
    }

    /**
     * Index action
     *
     * @return $this
     */
    public function indexAction()
    {
        try {
            $this->_initAction();

            $this->_addContent(
                $this->getLayout()->createBlock('remarketing/adminhtml_emailcapture')
            );

            return $this->renderLayout();
        } catch (Exception $e) {
            /* @var Listrak_Remarketing_Model_Log $logger */
            $logger = Mage::getSingleton('listrak/log');
            $logger->addException($e->getMessage());

            /* @var Mage_Adminhtml_Model_Session $adminSession */
            $adminSession = Mage::getSingleton('adminhtml/session');
            $adminSession->addError($e->getMessage());
            $adminSession->setEmailCaptureData($this->getRequest()->getPost());

            return $this->_redirect('adminhtml/dashboard');
        }
    }

    /**
     * Edit action
     *
     * @return $this
     */
    public function editAction()
    {
        try {
            $emailcaptureId = $this->getRequest()->getParam('id');

            /* @var Listrak_Remarketing_Model_Emailcapture $model */
            $model = Mage::getModel('listrak/emailcapture')
                ->load($emailcaptureId);

            if ($model->getId() || $emailcaptureId == 0) {
                Mage::register('emailcapture_data', $model);

                $this->loadLayout();
                $this->_setActiveMenu('emailcapture');

                $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Item Manager'),
                    Mage::helper('adminhtml')->__('Item Manager')
                );
                $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Item News'),
                    Mage::helper('adminhtml')->__('Item News')
                );

                $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

                $this
                    ->_addContent(
                        $this->getLayout()->createBlock(
                            'remarketing/adminhtml_emailcapture_edit'
                        )
                    )
                    ->_addLeft(
                        $this->getLayout()->createBlock(
                            'remarketing/adminhtml_emailcapture_edit_tabs'
                        )
                    );

                return $this->renderLayout();
            } else {
                /* @var Mage_Adminhtml_Model_Session $adminSession */
                $adminSession = Mage::getSingleton('adminhtml/session');
                $adminSession->addError(
                    Mage::helper('remarketing')->__('Item does not exist')
                );

                return $this->_redirect('*/*/');
            }
        } catch (Exception $e) {
            /* @var Listrak_Remarketing_Model_Log $logger */
            $logger = Mage::getSingleton('listrak/log');
            $logger->addException($e->getMessage());

            /* @var Mage_Adminhtml_Model_Session $adminSession */
            $adminSession = Mage::getSingleton('adminhtml/session');
            $adminSession->addError($e->getMessage());
            $adminSession->setEmailCaptureData($this->getRequest()->getPost());

            return $this->_redirect(
                '*/*/index',
                array('id' => $this->getRequest()->getParam('id'))
            );
        }
    }

    /**
     * New action, redirect to edit
     *
     * @return void
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Save action
     *
     * @return $this
     */
    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            /* @var Mage_Adminhtml_Model_Session $adminSession */
            $adminSession = Mage::getSingleton('adminhtml/session');

            try {
                $request = $this->getRequest();

                /* @var Listrak_Remarketing_Model_Emailcapture $model */
                $model = Mage::getModel('listrak/emailcapture');

                $model
                    ->setId($this->getRequest()->getParam('id'))
                    ->setPage($request->getPost('page'))
                    ->setFieldId($request->getPost('field_id'));
                $model->save();

                $adminSession->addSuccess(
                    Mage::helper('adminhtml')->__('Item was successfully saved')
                );
                $adminSession->unsEmailCaptureData();

                return $this->_redirect('*/*/');
            } catch (Exception $e) {
                /* @var Listrak_Remarketing_Model_Log $logger */
                $logger = Mage::getSingleton('listrak/log');
                $logger->addException($e->getMessage());

                $adminSession->addError($e->getMessage());
                $adminSession->setEmailCaptureData($this->getRequest()->getPost());

                return $this->_redirect(
                    '*/*/edit',
                    array('id' => $this->getRequest()->getParam('id'))
                );
            }
        }

        return $this->_redirect('*/*/');
    }

    /**
     * Delete action
     *
     * @return $this
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            /* @var Mage_Adminhtml_Model_Session $adminSession */
            $adminSession = Mage::getSingleton('adminhtml/session');

            try {
                /* @var Listrak_Remarketing_Model_Emailcapture $model */
                $model = Mage::getModel('listrak/emailcapture');

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();

                $adminSession->addSuccess(
                    Mage::helper('adminhtml')->__('Item was successfully deleted')
                );

                return $this->_redirect('*/*/');
            } catch (Exception $e) {
                /* @var Listrak_Remarketing_Model_Log $logger */
                $logger = Mage::getSingleton('listrak/log');
                $logger->addException($e->getMessage());

                $adminSession->addError($e->getMessage());

                return $this->_redirect(
                    '*/*/edit',
                    array('id' => $this->getRequest()->getParam('id'))
                );
            }
        }

        return $this->_redirect('*/*/');
    }

    /**
     * Grid action
     *
     * Product grid for AJAX request.
     * Sort and filter result for example.
     *
     * @return void
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock(
                    'importedit/adminhtml_emailcapture_grid'
                )
                ->toHtml()
        );
    }
}