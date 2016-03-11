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
 * Class Listrak_Remarketing_Helper_Data
 */
class Listrak_Remarketing_Helper_Data
    extends Mage_Core_Helper_Abstract
{
    private $_customerGroups;
    private $_categoryRootIdForStores = array();

    /**
     * Sets API attributes on a customer object
     *
     * @param Mage_Customer_Model_Customer $customer Customer to work with
     *
     * @return void
     */
    public function setGroupNameAndGenderNameForCustomer($customer)
    {
        if ($this->_customerGroups == null) {
            $this->_customerGroups = array();

            /* @var Mage_Customer_Model_Resource_Group $customerGroups */
            $customerGroups = Mage::getModel('customer/group')->getCollection();
            foreach ($customerGroups as $group) {
                $groupId = $group['customer_group_id'];
                $groupCode = $group['customer_group_code'];

                $this->_customerGroups[$groupId] = $groupCode;
            }
        }

        if (array_key_exists($customer->getGroupId(), $this->_customerGroups)) {
            $customer->setGroupName($this->_customerGroups[$customer->getGroupId()]);
        }

        $customer->setGenderName(
            Mage::getResourceSingleton('customer/customer')
                ->getAttribute('gender')
                ->getSource()
                ->getOptionText($customer->getGender())
        );
    }

    /**
     * Generate a random UUID
     *
     * @return string
     */
    public function genUuid()
    {
        // 32 bits for "time_low"
        // 16 bits for "time_mid"
        // 16 bits for "time_hi_and_version",
        //      four most significant bits holds version number 4
        // 16 bits, 8 bits for "clk_seq_hi_res", 8 bits for "clk_seq_low",
        //      two most significant bits holds zero and one for variant DCE1.1
        // 48 bits for "node"
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Creates a new exception based on another
     *
     * @param string    $exceptionText   Exception message
     * @param Exception $sourceException The exception to wrap
     *
     * @return Exception
     */
    public function generateAndLogException($exceptionText, $sourceException)
    {
        $exception = new Exception($exceptionText, 0, $sourceException);

        /* @var Listrak_Remarketing_Model_Log $log */
        $log = Mage::getModel("listrak/log");

        $log->addException($exception);
        return $exception;
    }

    /**
     * Check whether a Listrak account has been configured
     *
     * @return bool
     */
    public function checkSetupStatus()
    {
        return Mage::getStoreConfig('remarketing/config/account_created') == '1';
    }

    /**
     * Check whether to display a notification in admin portal
     *
     * @return bool
     */
    public function displayAttributeSetNotification()
    {
        /* @var Listrak_Remarketing_Helper_Product_Attribute_Set_Map $helper */
        $helper = Mage::helper('remarketing/product_attribute_set_map');
        return ($helper->newAttributeSetsCollection()->count() > 0);
    }

    /**
     * Check whether Listrak core functionality is enabled
     *
     * @return bool
     */
    public function coreEnabled()
    {
        return Mage::getStoreConfig('remarketing/modules/core') == '1';
    }

    /**
     * Halt execution if Listrak core functionality is disabled
     *
     * @throws Exception
     *
     * @return void
     */
    public function requireCoreEnabled()
    {
        if (!$this->coreEnabled()) {
            throw new Exception(
                'Listrak core functionality has been turned off in the System Configuration.'
            );
        }
    }

    /**
     * Check if the settings allow for OneScript usage
     *
     * @return bool
     */
    public function onescriptEnabled()
    {
        return $this->coreEnabled()
            && Mage::getStoreConfig('remarketing/modal/enabled') == '1'
            && strlen(
                trim(Mage::getStoreConfig('remarketing/modal/listrakMerchantID'))
            ) >= 12;
    }

    /**
     * Check whether OneScript SCA is enabled
     *
     * @return bool
     */
    public function scaEnabled()
    {
        return $this->onescriptTracking()
            && Mage::getStoreConfig('remarketing/modal/sca') == '1';
    }

    /**
     * Check whether OneScript activity tracking is enabled
     *
     * @return bool
     */
    public function activityEnabled()
    {
        return $this->onescriptTracking()
            && Mage::getStoreConfig('remarketing/modal/activity') == '1';
    }

    /**
     * Check whether the module is running in legacy mode
     *
     * @return bool
     */
    public function legacyTracking()
    {
        return $this->coreEnabled()
            && $this->trackingTablesExist()
            && !$this->onescriptReady();
    }

    /**
     * Check whether all tracking is done through OneScript
     *
     * @return bool
     */
    public function onescriptTracking()
    {
        return $this->onescriptEnabled()
            && $this->onescriptReady();
    }

    /**
     * Check whether cart changes are tracked through AJAX calls
     *
     * @return bool
     */
    public function ajaxTracking()
    {
        return Mage::getStoreConfig('remarketing/modal/ajax') == '1';
    }

    /**
     * Check whether review functionality has been enabled
     *
     * @return bool
     */
    public function reviewsEnabled()
    {
        return Mage::getStoreConfig('remarketing/modules/reviews') == '1';
    }

    /**
     * Halt execution if review functionality is disabled
     *
     * @throws Exception
     *
     * @return void
     */
    public function requireReviewsEnabled()
    {
        if (!$this->reviewsEnabled()) {
            throw new Exception(
                'Listrak reviews API has been turned off in the System Configuration.'
            );
        }
    }

    /**
     * Check whether the module is ready for OneScript tracking
     *
     * @return bool
     */
    public function onescriptReady()
    {
        return Mage::getStoreConfig('remarketing/config/onescript_ready') == '1';
    }

    /**
     * Check whether the session and click tracking tables are available
     *
     * @return bool
     */
    public function trackingTablesExist()
    {
        return Mage::getStoreConfig('remarketing/config/tracking_tables_deleted') != '1';
    }

    /**
     * Halt execution if session tracking tables have been deleted
     *
     * @throws Exception
     *
     * @return void
     */
    public function requireSessionTrackingTable()
    {
        if (!$this->trackingTablesExist()) {
            throw new Exception(
                'MissingSessionTrackingTable: The session tracking table has been deleted.'
            );
        }
    }

    /**
     * Halt execution if click tracking table is missing
     *
     * @throws Exception
     *
     * @return void
     */
    public function requireClickTrackingTable()
    {
        if (!$this->trackingTablesExist()) {
            throw new Exception(
                'MissingClickTrackingTable: The click tracking table has been deleted.'
            );
        }
    }

    /**
     * Get the selected category source
     *
     * @return string
     */
    public function categoriesSource()
    {
        return Mage::getStoreConfig(
            'remarketing/productcategories/categories_source'
        );
    }

    /**
     * Selected depth of the category tree, or how many categories to skip
     *
     * @return int
     */
    public function getCategoryLevel()
    {
        $setting = (int)Mage::getStoreConfig(
            'remarketing/productcategories/category_level'
        );

        if (!$setting) {
            $setting = 3;
        }

        return $setting;
    }

    /**
     * Retrieve the root category ID for a store
     *
     * @param int $storeId Magento store ID
     *
     * @return int
     */
    public function getCategoryRootIdForStore($storeId)
    {
        if (!array_key_exists($storeId, $this->_categoryRootIdForStores)) {
            /* @var Mage_Core_Model_Store $store */
            $store = Mage::getModel('core/store')
                ->load($storeId);

            /* @var Mage_Core_Model_Store_Group $storeGroup */
            $storeGroup = Mage::getModel('core/store_group')
                ->load($store->getGroupId());

            $this->_categoryRootIdForStores[$storeId] = $storeGroup
                ->getRootCategoryId();
        }

        return $this->_categoryRootIdForStores[$storeId];
    }

    /**
     * Get fingerprint tracking image URL
     *
     * @return string
     */
    public function getFingerprintImageUrl()
    {
        $endpoint = Mage::getStoreConfig('remarketing/endpoint/fingerprint');
        $tid = Mage::getStoreConfig('remarketing/modal/listrakMerchantID');

        if (!$endpoint) {
            $endpoint = 'fp.listrakbi.com/fp';
        } else {
            $endpoint = trim($endpoint, '/');
        }

        $protocol = Mage::app()->getStore()->isCurrentlySecure()
            ? "https:"
            : "http:";

        return "{$protocol}//{$endpoint}/{$tid}.jpg";
    }

    /**
     * Get the OneScript location
     *
     * @return string
     */
    public function onescriptSrc()
    {
        $endpoint = Mage::getStoreConfig('remarketing/endpoint/onescript');
        $tid = Mage::getStoreConfig('remarketing/modal/listrakMerchantID');

        if (!$endpoint) {
            $endpoint = 'cdn.listrakbi.com/scripts/script.js';
        } else {
            $endpoint = trim($endpoint);
        }

        $protocol = Mage::app()->getStore()->isCurrentlySecure()
            ? "https:"
            : "http:";

        return "{$protocol}//{$endpoint}?m={$tid}&v=1";
    }

    /**
     * Get all categories disabled by the user
     *
     * @return array
     */
    public function getInactiveCategories()
    {
        /* @var Mage_Catalog_Model_Resource_Category_Collection $collection */
        $collection = Mage::getModel('catalog/category')->getCollection();

        $collection
            ->addAttributeToFilter('is_active', 0);
        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('entity_id');

        $ids = array();
        foreach ($collection as $id) {
            $ids[] = intval($id['entity_id']);
        }

        return array_filter($ids);
    }

    /**
     * Get all categories to ignore in Listrak code
     *
     * @return array
     */
    public function getCategoriesToSkip()
    {
        $skip = Mage::getStoreConfig(
            'remarketing/productcategories/categories_skip'
        );

        $arr = array_unique(array_map('intval', array_filter(explode(",", $skip))));

        sort($arr);
        return $arr;
    }

    /**
     * Get the Listrak meta data provider, if any
     *
     * @return bool|Mage_Core_Helper_Abstract
     */
    public function getMetaDataProvider()
    {
        static $helper = null;

        if ($helper == null) {
            $helperRoute = Mage::getStoreConfig(
                'remarketing/advanced/meta_provider'
            );

            $helper = $helperRoute ? Mage::helper($helperRoute) : false;
        }

        return $helper;
    }

    /**
     * Encrypt data and make ready for URL consumption
     *
     * @param string $str The string to encrypt
     *
     * @return string
     */
    public function urlEncrypt($str)
    {
        /* @var Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');

        return rawurlencode(str_replace('/', '_', $coreHelper->encrypt($str)));
    }

    /**
     * Decrypt URL string
     *
     * @param string $str URL-encrypted string
     *
     * @return string
     */
    public function urlDecrypt($str)
    {
        /* @var Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');

        return $coreHelper->decrypt(str_replace('_', '/', rawurldecode($str)));
    }

    /**
     * Get the number of rows in the database
     *
     * Not all models will be usable by this method, but the ones
     * that the methods is called for are good
     *
     * @param string $modelEntity Magento router to desired model
     *
     * @return int
     */
    public function getTableRowCount($modelEntity)
    {
        /* @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');

        $readConnection = $resource->getConnection('core_read');

        $select = Mage::getModel($modelEntity)
            ->getCollection()
            ->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('COUNT(*)');

        return intval($readConnection->fetchOne($select));
    }

    /**
     * Initialize cart tracking cookie
     *
     * @return string
     */
    public function initCartCookie()
    {
        /* @var Mage_Core_Model_Cookie $cookie */
        $cookie = Mage::getModel('core/cookie');

        if (!$this->ajaxTracking()) {
            /* @var Mage_Checkout_Model_Session $session */
            $session = Mage::getSingleton('checkout/session');

            $value = md5('quote_' . $session->getQuoteId() . '_' . date("YmdHis"));
            $expiration = time() + 315532800; // 10 years, 2 of them leap years

            $cookie->set('mltkc', $value, $expiration, '/');
            return $value;
        } else {
            $cookie->delete('mltkc');
            return 'AJAX';
        }
    }
}
