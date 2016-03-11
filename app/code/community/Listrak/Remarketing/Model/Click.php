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
 * Class Listrak_Remarketing_Model_Click
 *
 * Processes requests for click data
 */
class Listrak_Remarketing_Model_Click
    extends Mage_Core_Model_Abstract
{
    /**
     * Initializes the object
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('listrak/click');
    }

    /**
     * Check the request for a click
     *
     * @return void
     */
    public function checkForClick()
    {
        $urlKeys = array_keys(Mage::app()->getRequest()->getParams());
        foreach ($urlKeys as $key) {
            if (stripos($key, 'trk_') !== false) {
                $this->_recordClick();
                break;
            }
        }
    }

    /**
     * Record a click
     *
     * @return void
     */
    private function _recordClick()
    {
        /* @var Listrak_Remarketing_Model_Session $session */
        $session = Mage::getSingleton('listrak/session');
        $session->init();

        $this->setTokenUid(Mage::helper('remarketing')->genUuid());
        $this->setClickDate(gmdate('Y-m-d H:i:s'));
        $this->setSessionId($session->getId());
        $this->setQuerystring(
            http_build_query(Mage::app()->getRequest()->getParams())
        );
        $this->save();

        /* @var Mage_Core_Model_Cookie $cookies */
        $cookies = Mage::getModel('core/cookie');
        $cookies->set('ltktrk', $this->getTokenUid(), true, null, null, null, true);
    }
}