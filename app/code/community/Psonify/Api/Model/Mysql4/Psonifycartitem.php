<?php

class Psonify_Api_Model_Mysql4_Psonifycartitem extends Mage_Core_Model_Mysql4_Abstract
{
    //magento model contruct function to define the model defination.
    public function _construct()
    {   
        $this->_init('api/psonifycartitem', 'id');
    }
}
?>
