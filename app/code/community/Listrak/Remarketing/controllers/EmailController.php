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
 * Class Listrak_Remarketing_EmailController
 */
class Listrak_Remarketing_EmailController
    extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action
     *
     * Stores a captured email address in legacy mode
     *
     * @return void
     */
    public function indexAction()
    {
        try {
            $email = $this->getRequest()->getParam('email');
            if (Zend_Validate::is($email, 'EmailAddress')) {
                $emailcaptureId = $this->getRequest()->getParam('cid');

                /* @var Listrak_Remarketing_Model_Session $session */
                $session = Mage::getSingleton('listrak/session');
                $session->init();

                $emailcapture = Mage::getModel('listrak/emailcapture')
                    ->load($emailcaptureId);

                if ($emailcapture->getId()) {
                    /* @var Listrak_Remarketing_Model_Mysql4_Session $resource */
                    $resource = $session->getResource();
                    $resource->insertEmail($session, $email, $emailcaptureId);

                    $result = array('status' => true);
                } else {
                    $result = array('status' => false);
                }

                header('Content-type: application/json');
                echo json_encode($result);
            }
        } catch (Exception $e) {
            /* @var Listrak_Remarketing_Model_Log $logger */
            $logger = Mage::getModel("listrak/log");
            $logger->addException($e);
        }
    }

    /**
     * FSID action
     *
     * @deprecated This action is not used anywhere, and its use isn't clear
     *
     * @return void
     */
    public function fsidAction()
    {
        $email = $this->getRequest()->getParam('email');

        if (!Zend_Validate::is($email, 'EmailAddress')) {
            echo "invalid";
        } else {
            $emailcaptureId = $this->getRequest()->getParam('cid');

            /* @var Listrak_Remarketing_Model_Session $session */
            $session = Mage::getModel('listrak/session');
            $session->setSessionId($this->getRequest()->getParam('ltksid'));

            /* @var Listrak_Remarketing_Model_Mysql4_Session $sessionResource */
            $sessionResource = $session->getResource();
            $sessionResource->loadBySessionId($session);
            $sessionResource->insertEmail($session, $email, $emailcaptureId);

            echo json_encode(array('status' => true));
        }
    }
}
