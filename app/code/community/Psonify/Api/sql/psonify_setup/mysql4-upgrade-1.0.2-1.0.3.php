<?php
$installer = $this;
  
$installer->startSetup();


$installer->run("
ALTER TABLE `{$this->getTable('psonify_cart_item')}`
	ADD COLUMN `token` VARCHAR(50) NULL AFTER `serialize_string`;
    ");

$installer->run("
ALTER TABLE `{$this->getTable('psonify_cart_item')}`
	ADD COLUMN `datetime` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `token`;
    ");
  
$installer->endSetup();
?>
