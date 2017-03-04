<?php

class Capita_TI_Adminhtml_Capita_HelpController extends Capita_TI_Controller_Action
{

    public function requestsAction()
    {
        $this->loadLayout();
        $this->_title($this->__('Capita Translations'))
            ->_title($this->__('Help'))
            ->_title($this->__('Requests'));
        $this->renderLayout();
    }
}
