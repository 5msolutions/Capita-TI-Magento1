<?php

class Capita_TI_Block_Adminhtml_Categories extends Varien_Data_Form_Element_Abstract
{

    public function getElementHtml()
    {
        return Mage::getSingleton('core/layout')
            ->createBlock('capita_ti/adminhtml_categories_tree')
            ->setElement($this)
            ->toHtml();
    }
}
