<?php

class Capita_TI_Model_Resource_Request_Document_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('capita_ti/request_document');
    }

    /**
     * Filter by either a string literal or a typical Varien data condition
     * 
     * @param mixed $status
     * @return Capita_TI_Model_Resource_Request_Document_Collection
     */
    public function addStatusFilter($status)
    {
        $this->addFieldToFilter('status', $status);
        return $this;
    }
}
