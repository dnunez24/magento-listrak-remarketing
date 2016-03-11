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
 * Class Listrak_Remarketing_Block_Adminhtml_EmailCapture_Grid
 */
class Listrak_Remarketing_Block_Adminhtml_EmailCapture_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initializes the object
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('emailcaptureGrid');
        $this->setDefaultSort('emailcapture_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        /* @var Listrak_Remarketing_Model_Mysql4_Emailcapture_Collection $col */
        $col = Mage::getModel('listrak/emailcapture')->getCollection();
        $this->setCollection($col);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'emailcapture_id',
            array(
                'header' => Mage::helper('remarketing')->__('ID'),
                'align' => 'right',
                'width' => '50px',
                'index' => 'emailcapture_id',
            )
        );

        $this->addColumn(
            'page',
            array(
                'header' => Mage::helper('remarketing')->__('Page'),
                'align' => 'left',
                'index' => 'page',
            )
        );

        $this->addColumn(
            'field_id',
            array(
                'header' => Mage::helper('remarketing')->__('Field ID'),
                'align' => 'left',
                'index' => 'field_id',
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve the edit page location for a row
     *
     * @param mixed $row Grid row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }


}