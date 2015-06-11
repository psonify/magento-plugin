<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Psonify_Api_Model_Mysql4_Psonifycart extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('api/psonifycart', 'id');
    }
}
?>
