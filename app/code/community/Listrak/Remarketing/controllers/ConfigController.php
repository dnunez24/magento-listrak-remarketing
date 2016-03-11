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
 * Class Listrak_Remarketing_ConfigController
 */
class Listrak_Remarketing_ConfigController
    extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action
     *
     * Returns the extension version, or enables OneScript tracking,
     * when asked to do these things specifically
     *
     * @return $this
     */
    public function indexAction()
    {
        if ($this->getRequest()->has('version')) {
            echo Mage::getConfig()->getNode('modules')->Listrak_Remarketing->version;
        } else if ($this->getRequest()->has('enableOnescriptTracking')) {
            echo $this->_enableOnescriptTracking();
        }

        return $this;
    }

    /**
     * Flags the extension as registered with Listrak
     *
     * @return $this
     */
    public function registerAction()
    {
        $reg = Mage::getStoreConfig('remarketing/config/account_created');

        if (!$reg) {
            Mage::getConfig()->saveConfig('remarketing/config/account_created', '1');
            Mage::getConfig()->reinit();
        }

        return $this;
    }

    /**
     * Returns whether the extension is registered with Listrak
     *
     * @return $this
     */
    public function checkAction()
    {
        echo Mage::getStoreConfig('remarketing/config/account_created');

        return $this;
    }

    /**
     * Ensure all the pieces are in place to enable OneScript tracking
     *
     * @return string
     */
    private function _enableOnescriptTracking()
    {
        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');

        if (!$helper->onescriptEnabled()) {
            return "failure: Onescript is disabled";
        }

        if ($helper->onescriptReady()) {
            return "success: already enabled";
        }

        if (!$this->getRequest()->has('skipValidation')) {
            $request = curl_init();
            curl_setopt($request, CURLOPT_TIMEOUT, 15);
            curl_setopt($request, CURLOPT_ENCODING, "");
            curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 0);

            curl_setopt($request, CURLOPT_URL, $helper->onescriptSrc());
            $script = curl_exec($request);
            $error = $script === false ? curl_error($request) : '';

            // $ch shouldn't be used below this next line
            curl_close($request);

            if ($script === false) {
                return "failure: Onescript did not load: {$error}";
            }

            if (strpos($script, "_ltk.SCA.Load(") === false) {
                return "failure: Onescript does not load the SCA session ID";
            }
        }

        Mage::getConfig()->saveConfig('remarketing/config/onescript_ready', '1');
        Mage::getConfig()->reinit();
        return "success";
    }
}