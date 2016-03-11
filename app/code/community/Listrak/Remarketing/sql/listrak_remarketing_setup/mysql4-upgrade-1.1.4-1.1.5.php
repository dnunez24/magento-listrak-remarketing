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
DROP TABLE IF EXISTS {$this->getTable('listrak/product_attribute_set_map')};
CREATE TABLE {$this->getTable('listrak/product_attribute_set_map')} (
	`map_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`attribute_set_id` smallint(5) unsigned NOT NULL,
	`brand_attribute_code` varchar(255),
	`categories_source` varchar(31),
	`use_config_categories_source` tinyint(1) NOT NULL DEFAULT 1,
	`category_attribute_code` varchar(255),
	`subcategory_attribute_code` varchar(255),
	`updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`map_id`),
	KEY `idx_attribute_set_id` (`attribute_set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
"
);

try {
    /* @var Listrak_Remarketing_Model_Log $log */
    $log = Mage::getModel("listrak/log");
    $log->addMessage("1.1.4-1.1.5 upgrade");
} catch (Exception $e) {
}

$installer->endSetup();
