<?php

class Capita_TI_Model_Request_Document extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('capita_ti/request_document');
    }

    protected function _initOldFieldsMap()
    {
        $this->_oldFieldsMap = array(
            'DocumentId' => 'remote_id',
            'DocumentName' => 'remote_name',
            'IsoCode' => 'language'
        );
        return $this;
    }
}
