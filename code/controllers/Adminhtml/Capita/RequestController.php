<?php

class Capita_TI_Adminhtml_Capita_RequestController extends Capita_TI_Controller_Action
{

    const MENU_PATH = 'system/capita_request';

    public function indexAction()
    {
        $this->loadLayout();
        $this->_checkConnection();
        $this->_title($this->__('Capita Translations'))
            ->_title($this->__('Requests'))
            ->_setActiveMenu(self::MENU_PATH);
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->loadLayout();
        $this->_checkConnection();
        $this->_title($this->__('Capita Translations'))
            ->_title($this->__('New Request'))
            ->_setActiveMenu(self::MENU_PATH);
        $this->renderLayout();
    }

    public function saveAction()
    {
        /* @var $requests Capita_TI_Model_Api_Requests */
        $requests = Mage::getModel('capita_ti/api_requests');
        $filename = $requests->saveNewRequest($this->getRequest());
        $this->_getSession()->addSuccess($filename.' was saved!');
        $this->_redirect('*/*');
    }
}
