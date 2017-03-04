<?php

class Capita_TI_Model_Source_Cron
{

    public function toOptionArray()
    {
        $helper = Mage::helper('capita_ti');
        return array(
            5 => $helper->__('Every 5 minutes'),
            60 => $helper->__('Each hour'),
            1440 => $helper->__('Each night'),
            10080 => $helper->__('On weekends')
        );
    }
}
