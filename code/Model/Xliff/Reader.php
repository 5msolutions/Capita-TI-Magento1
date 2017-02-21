<?php

class Capita_TI_Model_Xliff_Reader
{

    protected $_types;

    /**
     * Register an importer to be matched to each entity tpe
     * 
     * @param string $type
     * @param string $model
     * @return Capita_TI_Model_Xliff_Reader
     */
    public function addType(Capita_TI_Model_Xliff_Import_Interface $type)
    {
        $this->_types[$type->getEntityType()] = $type;
        return $this;
    }

    /**
     * Retrieve a specific type importer
     * 
     * @param string $type
     * @return Capita_TI_Model_Xliff_Import_Interface|null
     */
    public function getImporter($type)
    {
        return isset($this->_types[$type]) ? $this->_types[$type] : null;
    }

    public function import($uri)
    {
        $xml = new XMLReader();
        $xml->open($uri) or $this->__('Could not open "%s"', $uri);

        $xml->read() or $this->__('Could not read root element');
        $xml->name == 'xliff' or $this->__('Root element is not XLIFF');
        $version = $xml->getAttribute('version');
        $version == '1.2' or $this->__('XLIFF version is "%s" and needs to be "1.2"', $version);

        while ($this->_readFile($xml));

        $xml->close();
    }

    /**
     * Finds either a child or a sibling but not a parent
     * 
     * @param XMLReader $xml
     * @return boolean
     */
    protected function _nextElement(XMLReader $xml)
    {
        while ($xml->read() && $xml->nodeType != XMLReader::END_ELEMENT) {
            if ($xml->nodeType == XMLReader::ELEMENT) {
                return true;
            }
        }
        return false;
    }

    protected function _readFile(XMLReader $xml)
    {
        if (!$this->_nextElement($xml)) {
            return false;
        }

        $xml->name == 'file' or $this->__('Expected "%s" element but got "%s"', 'file', $xml->name);
        $origin = $xml->getAttribute('original') or $this->__('File origin is not specified');
        $sourceLanguage = $xml->getAttribute('source-language') or $this->__('Source language is not specified');
        $sourceLanguage = strtr($sourceLanguage, '-', '_');
        $destLanguage = $xml->getAttribute('target-language') or $this->__('Target language is not specified');
        $destLanguage = strtr($destLanguage, '-', '_');
        if (strpos($origin, '/') !== false) {
            list($origin, $id) = explode('/', $origin);
        }
        else {
            $id == '';
        }
        $importer = $this->getImporter($origin) or $this->__('Unrecognised file origin: "%s"', $origin);

        $this->_nextElement($xml) or $this->__('File element has no body');

        if ($xml->name == 'header') {
            $xml->next();
        }
        $xml->name == 'body' or $this->__('File element has no body');

        $sourceData = array();
        $destData = array();
        while ($this->_nextElement($xml)) {
            $xml->name == 'trans-unit' or $this->__('Expected "%s" element but got "%s"', 'trans-unit', $xml->name);
            $key = $xml->getAttribute('id');
            $key or $this->__('Trans-unit has no ID');

            $xml->read();
            $xml->name == 'source' or $this->__('Expected "%s" element but got "%s"', 'source', $xml->name);
            $sourceData[$key] = $xml->readString();

            while ($xml->name != 'target') {
                $xml->next();
            }
            $destData[$key] = $xml->readString();

            while ($xml->next() && $xml->name != 'trans-unit');
        }
        $importer->import($id, $sourceLanguage, $destLanguage, $sourceData, $destData);

        return true;
    }

    /**
     * Throws an exception with a localised message
     * 
     * Named after translation function because this is the class's only output.
     * Non-public to avoid too much confusion.
     * 
     * @param string $message
     * @param mixed $args
     * @throws Exception
     */
    protected function __($message, $args = null)
    {
        $helper = Mage::helper('capita_ti');
        $message = call_user_func_array(array($helper, '__'), func_get_args());
        // TODO: custom exception types
        throw new Exception($message);
    }
}
