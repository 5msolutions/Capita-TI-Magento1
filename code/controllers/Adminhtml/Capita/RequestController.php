<?php

class Capita_TI_Adminhtml_Capita_RequestController extends Mage_Adminhtml_Controller_Action
{

    const MENU_PATH = 'system/capita_request';

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(self::MENU_PATH);
    }

    protected function _checkConnection()
    {
        if ($this->_isLayoutLoaded) {
            try {
                Mage::getSingleton('capita_ti/api_languages')->getLanguages();
            }
            catch (Zend_Http_Exception $e) {
                Mage::logException($e);
                $this->getLayout()->getMessagesBlock()->addError($this->__('There was a problem connecting to the server: %s', $e->getMessage()));
            }
        }
    }

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

}
