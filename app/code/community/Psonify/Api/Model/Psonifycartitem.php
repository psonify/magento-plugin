<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Psonify_Api_Model_Psonifycartitem extends Mage_Core_Model_Abstract {

	/**
	 * [_construct description]
	 * @return [type] [description]
	 */
	public function _construct() {
		parent::_construct();
		$this->_init('api/psonifycartitem');
	}
}

?>