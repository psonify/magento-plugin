<?php

/**
 * 
 */
class Psonify_Api_Block_Cart extends Mage_Core_Block_Template {

	/**
	 * Function to set the template when particular block is called.
	 */
	public function __construct() {
		parent::__construct();
		$this->setTemplate('psonify/abandonedCart.phtml');
	}
}

?>
