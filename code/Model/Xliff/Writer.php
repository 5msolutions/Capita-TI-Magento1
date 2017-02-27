<?php

/**
 * Writes an XML file without breaking memory limits (usually)
 * 
 * @method string getDatatype()
 * @method Capita_TI_Model_Xliff_Writer setDatatype(string $datatype)
 * @method Capita_TI_Model_Xliff_Writer setSourceLanguage(string $language)
 */
class Capita_TI_Model_Xliff_Writer
{

    const XML_NAMESPACE = 'urn:oasis:names:tc:xliff:document:1.2';

    protected $_collections = array();
    protected $_attributes = array();
    protected $_datatype = 'database';
    protected $_sourceLanguage = 'en-GB';
    protected $_autoClear = true;

    /**
     * Each collection becomes a <file> section when output.
     * 
     * Collections are loaded and cleared as they are processed.
     * Keys are visible as file origins.
     * 
     * @param string $key
     * @param Varien_Data_Collection $collection
     * @param string[] $attributes
     * @return Capita_TI_Model_Xliff_Writer $this
     */
    public function addCollection($key, Varien_Data_Collection $collection, $attributes)
    {
        $this->_collections[$key] = $collection;
        $this->_attributes[$key] = $attributes;
        return $this;
    }

    /**
     * Controls clearing after writing to save memory
     * 
     * Default is true.
     * Set to false to prevent collections being cleared and possibly losing data.
     * 
     * @param unknown $flag
     */
    public function setAutoClear($flag)
    {
        $this->_autoClear = (bool) $flag;
    }

    public function setDatatype($datatype)
    {
        $this->_datatype = (string) $datatype;
        return $this;
    }

    public function setSourceLanguage($language)
    {
        $this->_sourceLanguage = strtr($language, '_', '-');
        return $this;
    }

    /**
     * Write a collection of objects to $uri as translateable sources
     * 
     * If $uri is an array the keys should be language codes.
     * 
     * @param array|string $uri
     * @param traversable $entities
     * @param string $group
     * @param string[] $attributes
     */
    public function output($uri)
    {
        $uris = is_array($uri) ? $uri : array($this->_sourceLanguage => $uri);
        $writers = array();
        foreach ($uris as $language => $uri) {
            $xml = new XMLWriter();
            $xml->openUri($uri);
            $xml->startDocument();
            $xml->startElement('xliff');
            $xml->writeAttribute('version', '1.2');
            $xml->writeAttribute('xmlns', self::XML_NAMESPACE);
            $writers[$language] = $xml;
        }

        foreach ($this->_collections as $key => $collection) {
            $this->_writeCollection($writers, $key, $collection, @$this->_attributes[$key]);
        }

        foreach ($writers as $xml) {
            // end all open elements, easier than remembering how many to do
            while ($xml->endElement());
            // only ever one document to end
            $xml->endDocument();
            $xml->flush();
            // force file to close, just in case
            unset($xml);
        }
    }

    /**
     * Uses a collection once, writing it's objects to potentially several files
     * 
     * @param XMLWriter[] $writers
     * @param string $original
     * @param Varien_Data_Collection $collection
     * @param string[] $attributes
     */
    protected function _writeCollection($writers, $original, Varien_Data_Collection $collection, $attributes)
    {
        /* @var $item Varien_Object */
        foreach ($collection as $id => $item) {
            foreach ($writers as $language => $xml) {
                $xml->startElement('file');
                $xml->writeAttribute('original', $original . '/' . ($item->getId() ? $item->getId() : $id));
                $xml->writeAttribute('source-language', $this->_sourceLanguage);
                $xml->writeAttribute('target-language', $language);
                $xml->writeAttribute('datatype', $this->_datatype);
                $xml->startElement('body');
            }

            // tried $item->toArray() but products still fill stock values that weren't asked for
            $data = array_intersect_key(
                $item->getData(),
                array_fill_keys($attributes, true));
            // do not translate empty values
            $data = array_filter($data, 'strlen');
            if ($data) {
                foreach ($data as $id => $source) {
                    foreach ($writers as $xml) {
                        $xml->startElement('trans-unit');
                        $xml->writeAttribute('id', $id);
                        $xml->writeElement('source', $source);
                        $xml->writeElement('target', $source);
                        $xml->endElement(); // trans-unit
                    }
                }
            }

            foreach ($writers as $xml) {
                $xml->endElement(); // body
                $xml->endElement(); // file
            }
        }
        if ($this->_autoClear) {
            $collection->clear();
        }
    }
}
