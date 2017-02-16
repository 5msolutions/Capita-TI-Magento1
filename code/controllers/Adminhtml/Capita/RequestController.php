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

    public function gridAction()
    {
        $this->loadLayout(array('default', 'adminhtml_capita_request_index'));
        $this->getResponse()->setBody(
            $this->getLayout()->getBlock('adminhtml_request.grid')->toHtml());
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
        $request = $requests->saveNewRequest($this->getRequest());
        $this->_getSession()->addSuccess($this->__('Request "%s" has been started', $request->getRemoteNo()));
        $this->_redirect('*/*');
    }

    public function viewAction()
    {
        try {
            $requestId = (int) $this->getRequest()->getParam('id');
            $request = Mage::getModel('capita_ti/request')->load($requestId);
            if ($request->isObjectNew()) {
                throw new Mage_Adminhtml_Exception($this->__('Request "%d" is unavailable', $requestId));
            }
            Mage::register('capita_request', $request);
    
            $this->loadLayout();
            $this->_checkConnection();
            $this->_title($this->__('Capita Translations'))
                ->_title($this->__('Request "%s"', $request->getRemoteNo()))
                ->_setActiveMenu(self::MENU_PATH);
            $this->renderLayout();
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirectReferer($this->getUrl('*/*'));
        }
    }
}
