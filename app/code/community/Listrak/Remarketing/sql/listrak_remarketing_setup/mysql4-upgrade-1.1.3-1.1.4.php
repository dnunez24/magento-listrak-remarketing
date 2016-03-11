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

/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$installer->run(
    "
DROP TABLE IF EXISTS {$this->getTable('listrak/review_update')};
CREATE TABLE {$this->getTable('listrak/review_update')} (
    `update_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `review_id` bigint(20) NOT NULL,
    `entity_id` tinyint(4) NOT NULL,
    `entity_pk_value` bigint(20) NOT NULL,
    `activity_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `activity` tinyint(4) NOT NULL,
    PRIMARY KEY (`update_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);

try {
    /* @var Listrak_Remarketing_Model_Log $log */
    $log = Mage::getModel("listrak/log");
    $log->addMessage("1.1.3-1.1.4 upgrade");
} catch (Exception $e) {
}

$installer->endSetup();
