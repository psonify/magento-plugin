<?php


class Psonify_Api_Block_Cart extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('psonify/abandonedCart.phtml');
    }

    public function methodBlock()
    {
        return 'informations about my block !!';
    }

}

?>
