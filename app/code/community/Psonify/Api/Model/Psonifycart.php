<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Psonify_Api_Model_Psonifycart extends Mage_Core_Model_Abstract {

	/**
	 * model construct function.
	 * @return null
	 */
	public function _construct() {
		parent::_construct();
		$this->_init('api/psonifycart');
	}
}

?>