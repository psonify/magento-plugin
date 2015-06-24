<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Psonify_Api_Adminhtml_PsonifyController extends Mage_Adminhtml_Controller_Action {

	/**
	 * [indexAction description]
	 * @return [type] [description]
	 */
	public function indexAction() {
		$this->loadLayout()
			->_setActiveMenu('psonify')
			->_title($this->__('Index Action'));
		$this->renderLayout();
	}
}

?>