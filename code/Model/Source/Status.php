<?php

class Capita_TI_Model_Source_Status
{

    public function getOptions()
    {
        /* @var $helper Capita_TI_Helper_Data */
        $helper = Mage::helper('capita_ti');
        return array(
            'completed' => $helper->__('Completed'),
            'onHold' => $helper->__('On Hold'),
            'inProgress' => $helper->__('In Progress'),
        );
    }

}
