<?php

/*
 * Collection class required to fetch all record from the table.
 */

class Psonify_Api_Model_Mysql4_Psonifycart_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('api/psonifycart');
    }
}
?>
