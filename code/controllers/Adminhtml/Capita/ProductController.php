<?php

class Capita_TI_Adminhtml_Capita_ProductController extends Mage_Adminhtml_Controller_Action
{

    public function enqueueAction()
    {
        $this->_redirect('*/capita_request/new');
    }

}
