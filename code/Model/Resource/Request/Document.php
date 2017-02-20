<?php

class Capita_TI_Model_Resource_Request_Document extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('capita_ti/document', 'document_id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $document)
    {
        if ($document->hasLanguage()) {
            $document->setLanguage(strtr($document->getLanguage(), '-', '_'));
        }
        return parent::_beforeSave($document);
    }

    protected function _afterDelete(Mage_Core_Model_Abstract $document)
    {
        if ($document->getLocalName()) {
            $filename = Mage::getConfig()->getVarDir().DS.$document->getLocalName();
            is_writable($filename) && unlink($filename);
        }
        return parent::_afterDelete($document);
    }
}
