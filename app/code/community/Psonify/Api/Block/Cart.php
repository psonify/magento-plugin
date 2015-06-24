<?php

/**
 * 
 */
class Psonify_Api_Block_Cart extends Mage_Core_Block_Template {

	/**
	 * [__construct description]
	 */
	public function __construct() {
		parent::__construct();
		$this->setTemplate('psonify/abandonedCart.phtml');
	}

	/**
	 * [methodBlock description]
	 * @return [type] [description]
	 */
	public function methodBlock() {
		return 'information about my block.';
	}

}

?>