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
 * Class Listrak_Remarketing_Block_Adminhtml_EmailCapture_Edit_Tab_Form
 */
class Listrak_Remarketing_Block_Adminhtml_EmailCapture_Edit_Tab_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'emailcapture_form',
            array('legend' => Mage::helper('remarketing')->__('Field information'))
        );

        $fieldset->addField(
            'page', 'text', array(
                      'label' => Mage::helper('remarketing')->__('Page'),
                      'class' => 'required-entry',
                      'required' => true,
                      'name' => 'page',
                      'after_element_html' => '<p class="note"><span>For example: '
                          . '/checkout/onepage/index. Each URL has 3 parts. If yours'
                          . ' does not have 3 parts, fill the last with "index". '
                          . 'You can also use wildcard character "*" to capture '
                          . 'field on all pages in the store.</span></p>'
                  )
        );

        $fieldset->addField(
            'field_id', 'text', array(
                          'label' => Mage::helper('remarketing')->__('Field ID'),
                          'name' => 'field_id',
                          'required' => true,
                          'class' => 'required-entry',
                          'after_element_html' => '<p class="note"><span>Field id '
                              . 'attribute. You can check it in HTML code '
                              . 'preview.</span></p>'
                      )
        );

        if (Mage::getSingleton('adminhtml/session')->getEmailCaptureData()) {
            /* @var Mage_Adminhtml_Model_Session $adminSession */
            $adminSession = Mage::getSingleton('adminhtml/session');

            $form->setValues($adminSession->getEmailCaptureData());
            $adminSession->setEmailCaptureData(null);
        } elseif (Mage::registry('emailcapture_data')) {
            $form->setValues(
                Mage::registry('emailcapture_data')
                    ->getData()
            );
        }

        return parent::_prepareForm();
    }
}