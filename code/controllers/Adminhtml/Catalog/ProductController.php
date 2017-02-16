<?php

class Capita_TI_Adminhtml_Catalog_ProductController extends Capita_TI_Controller_Action
{

    public function translateAction()
    {
        try {
            if ($this->getRequest()->isPost()) {
                $productIds = (array)$this->getRequest()->getParam('product');
                if (!$productIds) {
                    throw new Mage_Core_Exception($this->__('No products were selected'));
                }
                $this->_getSession()->setCapitaProductIds($productIds);
            }

            $this->loadLayout();
            $this->_checkConnection();
            $this->_title($this->__('Catalog'))
                ->_title($this->__('Products'))
                ->_title($this->__('Translate'))
                ->_setActiveMenu('catalog/product');
            $this->renderLayout();
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirectReferer($this->getUrl('*/catalog_product'));
        }
        catch (Exception $e) {
            Mage::logException($e);
        }
    }

}
