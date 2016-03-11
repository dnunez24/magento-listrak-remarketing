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
 * Class Listrak_Remarketing_Model_Mysql4_Session
 */
class Listrak_Remarketing_Model_Mysql4_Session
    extends Mage_Core_Model_Mysql4_Abstract
{
    /* @var Varien_Db_Adapter_Interface $_read */
    private $_read;

    /* @var Varien_Db_Adapter_Interface $_write */
    private $_write;

    /**
     * Initializes the resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('listrak/session', 'id');
        $this->_read = $this->_getReadAdapter();
        $this->_write = $this->_getWriteAdapter();
    }

    /**
     * Inflate session object
     *
     * @param Listrak_Remarketing_Model_Session $object Session
     *
     * @return Listrak_Remarketing_Model_Session
     */
    protected function _afterLoad(Listrak_Remarketing_Model_Session $object)
    {
        if ($object->getCustomerId()) {
            /* @var Mage_Customer_Model_Customer $customer */
            $customer = Mage::getModel("customer/customer")
                ->load($object->getCustomerId());

            if ($customer) {
                /* @var Listrak_Remarketing_Helper_Data $helper */
                $helper = Mage::helper('remarketing');

                $helper->setGroupNameAndGenderNameForCustomer($customer);
                $object->setCustomer($customer);
            }
        }

        if ($object->getId()) {
            $this->loadEmails($object);
            $this->loadClicks($object);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Load session by ID
     *
     * @param Listrak_Remarketing_Model_Session $session Session
     *
     * @return void
     */
    public function loadBySessionId(Listrak_Remarketing_Model_Session $session)
    {
        $select = $this->_read->select()
            ->from($this->getTable('listrak/session'), array("*"))
            ->where('session_id=?', $session->getSessionId());

        if ($result = $this->_read->fetchRow($select)) {
            $session->addData($result);
            $session->loadEmails();
        }
    }

    /**
     * Load session tied to a quote
     *
     * @param Listrak_Remarketing_Model_Session $session Session
     *
     * @return void
     */
    public function loadByQuoteId(Listrak_Remarketing_Model_Session $session)
    {
        $select = $this->_read->select()
            ->from($this->getTable('listrak/session'), array("*"))
            ->where('quote_id=?', $session->getQuoteId());

        if ($result = $this->_read->fetchRow($select)) {
            $session->addData($result);
            $session->loadEmails();
        }
    }

    /**
     * Save captured email address to the database
     *
     * @param Listrak_Remarketing_Model_Session $session        Session
     * @param string                            $email          Email address
     * @param int                               $emailcaptureId Capturing field
     *
     * @return void
     */
    public function insertEmail(
        Listrak_Remarketing_Model_Session $session,
        $email, $emailcaptureId
    ) {
        if ($session->getId()) {
            $data = array();
            $data['session_id'] = $session->getId();
            $data['email'] = $email;
            $data['emailcapture_id'] = $emailcaptureId;
            $data['created_at'] = gmdate('Y-m-d H:i:s');
            $this->_write->insert($this->getTable('listrak/session_email'), $data);
        }
    }

    /**
     * Load emails associate with a session
     *
     * @param Listrak_Remarketing_Model_Session $session Session
     *
     * @return void
     */
    public function loadEmails(Listrak_Remarketing_Model_Session $session)
    {
        $select = $this->_read->select()
            ->from(
                array('se' => $this->getTable('listrak/session_email')),
                array("*")
            )
            ->joinLeft(
                array('ec' => $this->getTable('listrak/emailcapture')),
                'se.emailcapture_id = ec.emailcapture_id',
                array('*')
            )
            ->where('session_id=?', $session->getId());

        $emails = $this->_read->fetchAll($select);
        $session->setEmails($emails);
    }

    /**
     * Load clicks into a session
     *
     * @param Listrak_Remarketing_Model_Session $session Session
     *
     * @return void
     */
    public function loadClicks(Listrak_Remarketing_Model_Session $session)
    {
        /* @var Listrak_Remarketing_Model_Mysql4_Click_Collection $clicks */
        $clicks = Mage::getModel("listrak/click")->getCollection();
        $clicks->addFieldToFilter('session_id', array('eq' => $session->getId()));

        $sessionClicks = array();
        foreach ($clicks as $click) {
            $sessionClicks[] = $click;
        }

        $session->setData('clicks', $sessionClicks);
    }

    /**
     * Delete all emails associated with a session
     *
     * @param string $sid session ID
     *
     * @return void
     */
    public function deleteEmails($sid)
    {
        $this->_write->delete(
            $this->getTable("listrak/session_email"),
            "session_id = $sid"
        );
    }
}
