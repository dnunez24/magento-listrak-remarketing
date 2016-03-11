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
 * Class Listrak_Remarketing_Block_Base
 */
class Listrak_Remarketing_Block_Base
    extends Mage_Core_Block_Template
{
    private $_alwaysRenderTemplate = false;
    private $_lines = array();

    /**
     * Render block
     *
     * @return string
     */
    public function _toHtml()
    {
        if ($this->getTemplate()) {
            if (!$this->_alwaysRenderTemplate && !trim($this->getScript())) {
                return "";
            }

            return parent::_toHtml();
        } else {
            return $this->getScript();
        }
    }

    /**
     * Retrieve whether the block settings allow for rendering
     *
     * @return bool
     */
    public function canRender()
    {
        return true;
    }

    /**
     * Retrieve the Javascript code
     *
     * @param bool $addWhitespace Add whitespace characters on each line
     *
     * @return string
     */
    public function getScript($addWhitespace = true)
    {
        $code = "";
        foreach ($this->_lines as $line) {
            $code .= $line . "\n";
            if ($addWhitespace) {
                $code .= "        ";
            }
        }

        return $code . $this->getChildHtml();
    }

    /**
     * Adds a JS line to the output
     *
     * @param string $js Javascript line
     *
     * @return void
     */
    protected function addLine($js)
    {
        $this->_lines[] = $js;
    }

    /**
     * Escapes special characters in a string
     *
     * @param string $value Value
     * @param string $quote Special quote character
     *
     * @return string
     */
    public function jsEscape($value, $quote = "'")
    {
        return addcslashes($value, "\\{$quote}");
    }

    /**
     * Make string JS-friendly
     *
     * @param string $value Value
     *
     * @return string
     */
    public function toJsString($value)
    {
        return "'{$this->jsEscape($value)}'";
    }

    /**
     * Set flag to require a template
     *
     * @param mixed $val Value
     *
     * @return void
     */
    public function setAlwaysRenderTemplate($val)
    {
        $this->_alwaysRenderTemplate = (bool)$val;
    }

    /**
     * Retrieve whether current page is product page
     *
     * @return bool
     */
    public function isProductPage()
    {
        return (
            Mage::app()->getRequest()->getModuleName() == 'catalog'
            && Mage::app()->getRequest()->getControllerName() == 'product'
            && Mage::app()->getRequest()->getActionName() == 'view'
        );
    }

    /**
     * Retrieve whether current page is cart page
     *
     * @return bool
     */
    public function isCartPage()
    {
        return (
            Mage::app()->getRequest()->getModuleName() == 'checkout'
            && Mage::app()->getRequest()->getControllerName() == 'cart'
            && Mage::app()->getRequest()->getActionName() == 'index'
        );
    }

    /**
     * Retrieve whether current page is order confirmation
     *
     * @return bool
     */
    public function isOrderConfirmationPage()
    {
        $controller = Mage::app()->getRequest()->getControllerName();

        return (
            Mage::app()->getRequest()->getModuleName() == 'checkout'
            && ($controller == 'multishipping' || $controller == 'onepage')
            && Mage::app()->getRequest()->getActionName() == 'success'
        );
    }

    /**
     * Logger
     *
     * @return Listrak_Remarketing_Model_Log
     */
    protected function getLogger()
    {
        return Mage::getModel('listrak/log');
    }
}
