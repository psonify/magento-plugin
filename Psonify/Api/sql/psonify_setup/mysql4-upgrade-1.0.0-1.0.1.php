<?php

$installer = $this;
  
$installer->startSetup();
  
$installer->run("
  
ALTER TABLE `{$this->getTable('psonify_cart_item')}`
	ADD COLUMN `serialize_string` TEXT NULL AFTER `qty`;

    ");
  
$installer->endSetup();
?>
