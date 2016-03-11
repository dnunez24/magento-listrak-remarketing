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
 * Class Listrak_Remarketing_Model_Subscriber
 *
 * Overwrites newsletter subscriber object to ensure that
 * the customer receives only one signup/unsub email, and
 * not one from each source
 */
class Listrak_Remarketing_Model_Subscriber
    extends Mage_Newsletter_Model_Subscriber
{
    /**
     * Shim to test email singup configuration
     *
     * @return $this
     */
    public function sendConfirmationSuccessEmail()
    {
        if (Mage::getStoreConfig(
            'remarketing/subscription/signup_success_email'
        ) == '1') {
            return parent::sendConfirmationSuccessEmail();
        } else {
            return $this;
        }
    }

    /**
     * Shim to test email unsub configuration
     *
     * @return $this
     */
    public function sendUnsubscriptionEmail()
    {
        if (Mage::getStoreConfig(
            'remarketing/subscription/unsubscribe_email'
        ) == '1') {
            return parent::sendUnsubscriptionEmail();
        } else {
            return $this;
        }
    }
}