<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Psonify_Api_Adminhtml_PsonifyController extends Mage_Adminhtml_Controller_Action {

	/**
	 * admin index controller to render the phtml view.
	 */
	public function indexAction() {
		//load layout set menu of and set title and render.
		$this->loadLayout()
			->_setActiveMenu('psonify')
			->_title($this->__('Index Action'));
		$this->renderLayout();
	}
}

?>
