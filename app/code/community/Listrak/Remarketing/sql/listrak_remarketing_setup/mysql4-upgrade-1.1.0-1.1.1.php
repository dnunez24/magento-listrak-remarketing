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
ALTER TABLE {$this->getTable('listrak/session')}
ADD `pi_id` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL
"
);

try {
    /* @var Listrak_Remarketing_Model_Log $log */
    $log = Mage::getModel("listrak/log");
    $log->addMessage("1.1.0-1.1.1 upgrade");
} catch (Exception $e) {
}

$installer->endSetup();
