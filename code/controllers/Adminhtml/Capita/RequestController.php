<?php

class Capita_TI_Adminhtml_Capita_RequestController extends Capita_TI_Controller_Action
{

    const MENU_PATH = 'system/capita_request';

    public function preDispatch()
    {
        parent::preDispatch();

        // might be called on any page, even by AJAX
        // can be used to refresh all or just one
        if ($this->getRequest()->getParam('refresh') == 'status') {
            try {
                $id = $this->getRequest()->getParam('id');
                /* @var $client Capita_TI_Model_Api_Requests */
                $client = Mage::getModel('capita_ti/api_requests', array(
                    'keepalive' => true
                ));
                if ($id) {
                    /* @var $request Capita_TI_Model_Request */
                    $request = Mage::getModel('capita_ti/request')->load($id);
                    if ($request->canUpdate()) {
                        $client->updateRequest($request);
                    }
                }
                else {
                    /* @var $requests Capita_TI_Model_Resource_Request_Collection */
                    $requests = Mage::getResourceModel('capita_ti/request_collection');
                    $requests->addRemoteFilter();
                    foreach ($requests as $request) {
                        if ($request->canUpdate()) {
                            $client->updateRequest($request);
                        }
                    }
                }
            }
            catch (Exception $e) {
                $this->_getSession()->addException($e, $this->__('There was a problem connecting to the server: %s', $e->getMessage()));
            }
        }

        return $this;
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_checkConnection();
        Mage::helper('adminhtml')->setPageHelpUrl($this->getUrl('*/capita_help/requests'));
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

    public function productsTabAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function productsGridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('request_tab_products_grid')->setProductIds(
            $this->getRequest()->getParam('product_ids', array()));
        $this->renderLayout();
    }

    public function categoriesTabAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function categoriesGridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('request_tab_categories_grid')->setCategoryIds(
            $this->getRequest()->getParam('category_ids', array()));
        $this->renderLayout();
    }

    public function blocksTabAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function blocksGridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('request_tab_blocks_grid')->setBlockIds(
            $this->getRequest()->getParam('block_ids', array()));
        $this->renderLayout();
    }

    public function pagesTabAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function pagesGridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('request_tab_pages_grid')->setPageIds(
            $this->getRequest()->getParam('page_ids', array()));
        $this->renderLayout();
    }

    public function deleteAction()
    {
        try {
            $requestId = $this->getRequest()->getParam('id');
            /* @var $request Capita_TI_Model_Request */
            $request = Mage::getModel('capita_ti/request')->load($requestId);
            if (!$request->isObjectNew() && $request->canDelete()) {
                $request->delete();
                $this->_getSession()->addSuccess($this->__('Request "%s" has been deleted', $request->getRemoteNo()));
            }
        }
        catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*');
    }

    public function saveAction()
    {
        try {
            /* @var $requests Capita_TI_Model_Api_Requests */
            $requests = Mage::getModel('capita_ti/api_requests', array(
                // enable following line to test without submitting to real API
//                 'adapter' => Mage::getModel('capita_ti/api_adapter_samplePostRequest')
            ));
            $request = $requests->startNewRequest($this->getRequest());
            $request->save();
            $this->_getSession()->unsCapitaProductIds()->unsCapitaCategoryIds();
            $this->_getSession()->addSuccess($this->__('Request "%s" has been started', $request->getRemoteNo()));

            Mage::getModel('capita_ti/email')->sendFirstUse();

            $this->_redirect('*/*');
        }
        catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($e->getMessage());
            $this->_redirectReferer($this->getUrl('*/*'));
        }
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
            $this->_redirect('*/*');
        }
    }
}
