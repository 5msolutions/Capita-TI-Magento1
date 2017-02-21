<?php

class Capita_TI_Controller_Action extends Mage_Adminhtml_Controller_Action
{


    const ACL_PATH = 'system/capita_request';

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(self::ACL_PATH);
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
                Mage::getSingleton('capita_ti/api_languages')->setLocalLanguages();
            }
        }
    }
}
