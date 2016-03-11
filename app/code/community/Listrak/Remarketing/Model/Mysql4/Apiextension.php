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
 * Class Listrak_Remarketing_Model_Mysql4_Apiextension
 */
class Listrak_Remarketing_Model_Mysql4_Apiextension
    extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Initializes resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('listrak/session', 'id');
    }

    /**
     * Retrieves subscriber information
     *
     * @param int  $storeId   Magento store ID
     * @param null $startDate Data constraint, lower
     * @param int  $perPage   Page size
     * @param int  $page      Cursor
     *
     * @return Mage_Newsletter_Model_Resource_Subscriber_Collection
     */
    public function subscribers(
        $storeId = 1, $startDate = null, $perPage = 50, $page = 1
    ) {
        /* @var Mage_Newsletter_Model_Resource_Subscriber_Collection $collection */
        $collection = Mage::getModel("newsletter/subscriber")->getCollection();

        $collection->addStoreFilter($storeId)
            ->setPageSize($perPage)
            ->setCurPage($page);

        $collection->getSelect()
            ->group('main_table.subscriber_id')
            ->join(
                array('su' => $collection->getTable('listrak/subscriber_update')),
                'main_table.subscriber_id = su.subscriber_id',
                array('updated_at')
            )
            ->where('su.updated_at > ?', $startDate)
            ->distinct();

        $collection->setOrder('su.updated_at', 'ASC');

        /* @var Mage_Newsletter_Model_Subscriber $c */
        foreach ($collection as $c) {
            switch ($c->getSubscriberStatus()) {
                case "1":
                    $c->setSubscriberStatus("subscribed");
                    break;

                case "2":
                    $c->setSubscriberStatus("inactive");
                    break;

                case "3":
                    $c->setSubscriberStatus("unsubscribed");
                    break;

                case "4":
                    $c->setSubscriberStatus("unconfirmed");
                    break;

                default:
                    break;
            }
        }

        return $collection;
    }
}