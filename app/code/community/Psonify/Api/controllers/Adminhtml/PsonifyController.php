<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Psonify_Api_Adminhtml_PsonifyController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $key = 'LO-PSONIFY';
		$data = array(
			'host' => $_SERVER['HTTP_HOST'],
			'redirect' => 'templates',
			'time' => time(),
		);
		$jData = json_encode($data);
		$param = $this->encrypt($jData,$key);
		
		$loginLink = 'http://psonifydev.com/action/?m='.$this->cleanString($_SESSION['iv']).'&h='.urlencode($param);
		
		
		Mage::getSingleton("core/session")->setPsonifyVars(
			array(
				'loginLink' => $loginLink,
			)
		);
		
		$this->loadLayout()
            ->_setActiveMenu('psonify')
            ->_title($this->__('Psonify Settings'));
        $this->renderLayout();
    }
	
	
	function encrypt($pure_string,$key) {
		$dirty = array("+", "/", "=");
		$clean = array("_PLUS_", "_SLASH_", "_EQUALS_");
		$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
		$_SESSION['iv'] = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $_SESSION['iv']);
		$encrypted_string = base64_encode($encrypted_string);
		return str_replace($dirty, $clean, $encrypted_string);
	}
	
	public function cleanString($str){
		$dirty = array("+", "/", "=");
		$clean = array("_PLUS_", "_SLASH_", "_EQUALS_");
		$str = base64_encode($str);
		return str_replace($dirty, $clean, $str);
	}
    
}
?>
