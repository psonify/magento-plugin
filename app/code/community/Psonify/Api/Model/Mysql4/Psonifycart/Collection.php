<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


class Psonify_Api_Model_Mysql4_Psonifycart_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        //parent::__construct();
        $this->_init('api/psonifycart');
    }
}
?>
