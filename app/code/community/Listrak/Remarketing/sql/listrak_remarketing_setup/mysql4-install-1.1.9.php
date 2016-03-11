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

/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$installer->run(
    "
DROP TABLE IF EXISTS {$this->getTable('listrak/session')};

DROP TABLE IF EXISTS {$this->getTable('listrak/session_email')};

DROP TABLE IF EXISTS {$this->getTable('listrak/click')};

DROP TABLE IF EXISTS {$this->getTable('listrak/emailcapture')};
CREATE TABLE {$this->getTable('listrak/emailcapture')} (
  `emailcapture_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `page` varchar(255) NOT NULL,
  `field_id` varchar(255) NOT NULL,
  PRIMARY KEY (`emailcapture_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS {$this->getTable('listrak/subscriber_update')} (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subscriber_id` int(10) unsigned NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS {$this->getTable('listrak/log')} (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(11) DEFAULT NULL,
  `date_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `message` text CHARACTER SET utf8 NOT NULL,
  `log_type_id` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `log_type_id` (`log_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


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

INSERT INTO {$this->getTable('listrak/emailcapture')} (`emailcapture_id` ,`page` ,`field_id`)
VALUES (NULL , '/checkout/onepage/index', 'billing:email')
    , (NULL , '/checkout/onepage/index', 'login-email')
    , (NULL , '*', 'newsletter')
    , (NULL , '*', 'ltkmodal-email');
"
);

try {
    /* @var Listrak_Remarketing_Model_Log $log */
    $log = Mage::getModel("listrak/log");
    $log->addMessage("1.1.9 install");
} catch (Exception $e) {
}

$config = Mage::getConfig();
$config->saveConfig('remarketing/config/tracking_tables_deleted', '1');
$config->saveConfig('remarketing/config/onescript_ready', '1');

$installer->endSetup();
