<?php

/**
 * @method int[] getProductIds()
 * @method Capita_TI_Model_Request setProductIds(int[])
 */
class Capita_TI_Model_Request extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('capita_ti/request');
    }
}
