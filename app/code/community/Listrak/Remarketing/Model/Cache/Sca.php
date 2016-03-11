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
 * Class Listrak_Remarketing_Model_Cache_Sca
 *
 * Serves as the caching model for the SCA tracking block
 */
class Listrak_Remarketing_Model_Cache_Sca
    extends Enterprise_PageCache_Model_Container_Abstract
{
    private $_cartCookieValue = null;
    private $_saveBlock = false;

    /**
     * Retrieve the block cache ID
     *
     * @return string
     */
    protected function _getCacheId()
    {
        if (!$this->_cartCookieValue) {
            $this->_cartCookieValue = $this->_getCookieValue('mltkc');
        }

        if (!$this->_cartCookieValue) {
            $this->_cartCookieValue = 'AJAX';
        }

        return md5('REMARKETING_SCA_' . $this->_cartCookieValue);
    }

    /**
     * Render SCA tracking block
     *
     * @return string
     */
    protected function _renderBlock()
    {
        /* @var Listrak_Remarketing_Helper_Data $helper */
        $helper = Mage::helper('remarketing');

        $block = $this->_placeholder->getAttribute('block');

        /* @var Listrak_Remarketing_Block_Tracking_Sca $block */
        $block = new $block;
        $block->setFullPageRendering(true);

        if ($block->canRender()) {
            $block->setTemplate($this->_placeholder->getAttribute('template'));

            if ($helper->ajaxTracking()) {
                $this->_cartCookieValue = 'AJAX';
                $this->_saveBlock = true;
            }

            return $block->toHtml();
        } else {
            $this->_saveBlock = true;
            if (!$this->_cartCookieValue) {
                $this->_cartCookieValue = $helper->initCartCookie();
            }

            return "";
        }
    }

    /**
     * Manages block storage
     *
     * @param string $data     Block content to store
     * @param string $id       Cache ID
     * @param array  $tags     Tags
     * @param mixed  $lifetime EOL
     *
     * @return bool
     */
    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        if ($this->_saveBlock) {
            return parent::_saveCache($data, $id, $tags, $lifetime);
        }

        return false;
    }
}
