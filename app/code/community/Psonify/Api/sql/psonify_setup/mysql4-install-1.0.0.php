<?php

/*
  File to create 2 tables which can hold the psonify cart values.
  Table 1:- psonify_cart
  Table 2:- psonify_cart_item
 */


$installer = $this;
  
$installer->startSetup();
  
$installer->run("
  
DROP TABLE IF EXISTS {$this->getTable('psonify_cart')};
CREATE TABLE `{$this->getTable('psonify_cart')}` (
	`id` INT(10) NOT NULL AUTO_INCREMENT,
	`customer_id` INT(11) NOT NULL DEFAULT '0',
        `ip` VARCHAR(50) NOT NULL DEFAULT '0',
	`discount` FLOAT NULL DEFAULT '0',
	`shipping_rate` FLOAT NULL DEFAULT '0',
	`token` VARCHAR(50) NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='UTF8'
ENGINE=InnoDB;

DROP TABLE IF EXISTS {$this->getTable('psonify_cart_item')};
CREATE TABLE `{$this->getTable('psonify_cart_item')}` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`psonify_cart_id` INT(11) NOT NULL,
	`cart_item_id` INT(11) NOT NULL,
	`qty` INT(11) NOT NULL,
	PRIMARY KEY (`id`)
)
ENGINE=InnoDB;


    ");
  
$installer->endSetup();
?>
