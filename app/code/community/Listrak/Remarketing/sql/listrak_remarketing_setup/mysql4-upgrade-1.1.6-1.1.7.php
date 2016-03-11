<?php
/**
 * Listrak Remarketing Magento Extension Ver. 1.1.7
 *
 * PHP version 5
 *
 * @category  Listrak
 * @package   Listrak_Remarketing
 * @author    Listrak Magento Team <magento@listrak.com>
 * @copyright 2013 Listrak Inc
 * @license   http://s1.listrakbi.com/licenses/magento.txt License For Customer Use of Listrak Software
 * @link      http://www.listrak.com
 */

/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

try {
    $installer->run(
        "
ALTER TABLE {$this->getTable('listrak/session')}
DROP COLUMN `had_items`
"
    );
} catch(Exception $e) {
}

$installer->run(
    "
ALTER TABLE {$this->getTable('listrak/session')}
  ADD COLUMN `had_items` boolean NOT NULL DEFAULT 0;

UPDATE {$this->getTable('listrak/session')} s SET s.`had_items` = 1
  WHERE EXISTS (
    SELECT *
    FROM {$this->getTable('sales/quote')} q
    WHERE s.quote_id = q.entity_id AND q.items_count > 0
  );
"
);

try {
    /* @var Listrak_Remarketing_Model_Log $log */
    $log = Mage::getModel("listrak/log");
    $log->addMessage("1.1.6-1.1.7 upgrade");
} catch (Exception $e) {
}

$installer->endSetup();
