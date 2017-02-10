<?php

class Capita_TI_Adminhtml_Capita_RequestController extends Mage_Adminhtml_Controller_Action
{

    const MENU_PATH = 'system/capita_request';

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(self::MENU_PATH);
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_title($this->__('Capita Translations'))
            ->_title($this->__('Requests'))
            ->_setActiveMenu(self::MENU_PATH);
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->loadLayout();
        $this->_title($this->__('Capita Translations'))
            ->_title($this->__('New Request'))
            ->_setActiveMenu(self::MENU_PATH);
        $this->renderLayout();
    }

}
