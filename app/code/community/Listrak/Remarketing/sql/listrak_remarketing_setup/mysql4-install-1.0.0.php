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
DROP TABLE IF EXISTS {$this->getTable('listrak/session')};
CREATE TABLE {$this->getTable('listrak/session')} (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(36) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `quote_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_quote_id` (`quote_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('listrak/session_email')};
CREATE TABLE {$this->getTable('listrak/session_email')} (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` int(10) unsigned NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 NOT NULL,
  `type` char(2) CHARACTER SET utf8 NOT NULL,
  `created_at` datetime NOT NULL,
  `emailcapture_id` int(10),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS {$this->getTable('listrak/click')};
CREATE TABLE {$this->getTable('listrak/click')} (
  `click_id` int(11) NOT NULL AUTO_INCREMENT,
  `token_uid` char(36) NOT NULL,
  `click_date` datetime NOT NULL,
  `session_id` int(10) unsigned NOT NULL,
  `querystring` varchar(250) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`click_id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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

INSERT INTO {$this->getTable('listrak/emailcapture')} (`emailcapture_id` ,`page` ,`field_id`)
VALUES (NULL , '/checkout/onepage/index', 'billing:email');
INSERT INTO {$this->getTable('listrak/emailcapture')} (`emailcapture_id` ,`page` ,`field_id`)
VALUES (NULL , '/checkout/onepage/index', 'login-email');
INSERT INTO {$this->getTable('listrak/emailcapture')} (`emailcapture_id` ,`page` ,`field_id`)
VALUES (NULL , '*', 'newsletter');
"
);

try {
    /* @var Listrak_Remarketing_Model_Log $log */
    $log = Mage::getModel("listrak/log");
    $log->addMessage("1.0.0 install");
} catch (Exception $e) {
}

$installer->endSetup();
