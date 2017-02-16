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
    }
}
