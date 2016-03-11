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
 * Class Listrak_Remarketing_Block_Tracking_Email
 */
class Listrak_Remarketing_Block_Tracking_Email
    extends Listrak_Remarketing_Block_Require_Sca
{
    /**
     * Render block
     *
     * @return string
     */
    public function _toHtml()
    {
        try {
            if (!$this->canRender()) {
                return '';
            }

            foreach ($this->getFields() as $field) {
                $this->addLine(
                    "_ltk.SCA.CaptureEmail({$this->toJsString($field)});"
                );
            }

            return parent::_toHtml();
        } catch(Exception $e) {
            $this->getLogger()->addException($e);
            return '';
        }
    }

    /**
     * Get fields to capture on current page
     *
     * @return array
     */
    public function getFields()
    {
        /* @var Listrak_Remarketing_Model_Mysql4_Emailcapture_Collection $col */
        $col = Mage::getModel('listrak/emailcapture')
            ->getCollection();

        $col->addFieldToFilter(
            'page', array('in' => $this->_getMatchingPages())
        );

        $result = array();
        foreach ($col as $field) {
            $result[] = $field->getFieldId();
        }

        return $result;
    }

    /**
     * Retrieve all routes leading to this page
     *
     * @return array
     */
    private function _getMatchingPages()
    {
        $fullMatches = array();

        try {
            $route = Mage::app()->getRequest()->getRouteName();
            $controller = Mage::app()->getRequest()->getControllerName();
            $action = Mage::app()->getRequest()->getActionName();

            $matches = array();
            $matches[] = '/' . $route . '/' . $controller . '/' . $action . '/';
            $matches[] = '/' . $route . '/' . $controller . '/' . $action;
            $matches[] = '/' . $route . '/' . $controller . '/*';
            $matches[] = '/' . $route . '/' . $controller . '/';
            $matches[] = '/' . $route . '/*/*';
            $matches[] = '/' . $route . '/*';
            $matches[] = '/*/*/*';
            $matches[] = '/*/*';
            $matches[] = '/*';
            $matches[] = '';

            if (strtolower($action) == 'index') {
                $matches[] = '/' . $route . '/' . $controller;
            }
            if (strtolower($action) == 'index'
                && strtolower($controller) == 'index'
            ) {
                $matches[] = '/' . $route;
            }

            foreach ($matches as $match) {
                $fullMatches[] = $match;
                if ($match && $match{0} && $match{0} == '/') {
                    $fullMatches[] = substr($match, 1);
                }
            }
        } catch (Exception $ex) {
            $this->getLogger()->addException($ex);
        }

        return $fullMatches;
    }
}
