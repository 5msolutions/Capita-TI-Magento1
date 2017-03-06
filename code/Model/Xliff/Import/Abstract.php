<?php

abstract class Capita_TI_Model_Xliff_Import_Abstract
{

    private $_request;

    /**
     * This value will be used in the "original" attribute
     * 
     * @return string
     */
    abstract public function getEntityType();

    /**
     * Import the supplied data to the appropriate entity
     * 
     * @param scalar $id
     * @param string $sourceLanguage
     * @param string $destLanguage
     * @param array $sourceData
     * @param array $destData
     */
    abstract public function import($id, $sourceLanguage, $destLanguage, $sourceData, $destData);

    /**
     * Probably the original request instance that started this translation
     * 
     * @return Capita_TI_Model_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Provides a hint on how or where to import the following data
     * 
     * @param Capita_TI_Model_Request $request
     * @return Capita_TI_Model_Xliff_Import_Abstract
     */
    public function setRequest(Capita_TI_Model_Request $request)
    {
        $this->_request = $request;
        return $this;
    }
}
