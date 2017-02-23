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
            'importing' => $helper->__('Importing'),
            'error' => $helper->__('Error')
        );
    }

    public function getOptionLabel($value)
    {
        /* @var $helper Capita_TI_Helper_Data */
        $helper = Mage::helper('capita_ti');
        switch ($value) {
            case 'completed':
                return $helper->__('Completed');
            case 'onHold':
                return $helper->__('On Hold');
            case 'inProgress':
                return $helper->__('In Progress');
            case 'importing':
                return $helper->__('Importing');
            case 'error':
                return $helper->__('Error');
            default:
                return '';
        }
    }
}
