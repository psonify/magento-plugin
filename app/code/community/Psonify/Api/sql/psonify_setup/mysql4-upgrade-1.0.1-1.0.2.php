<?php
$installer = $this;
  
$installer->startSetup();
  
$installer->run("

ALTER TABLE `{$this->getTable('psonify_cart_item')}`
	ADD CONSTRAINT `FK_psonify_cart_item_psonify_cart` FOREIGN KEY (`psonify_cart_id`) REFERENCES `{$this->getTable('psonify_cart')}` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE;
	
    ");
  
$installer->endSetup();
?>
