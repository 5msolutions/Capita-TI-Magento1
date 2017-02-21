<?php

interface Capita_TI_Model_Xliff_Import_Interface
{

    /**
     * This value will be used in the "original" attribute
     * 
     * @return string
     */
    public function getEntityType();

    /**
     * Import the supplied data to the appropriate entity
     * 
     * @param scalar $id
     * @param string $sourceLanguage
     * @param string $destLanguage
     * @param array $sourceData
     * @param array $destData
     */
    public function import($id, $sourceLanguage, $destLanguage, $sourceData, $destData);
}
