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
    const HTM_NAMESPACE = 'urn:magento:html';
    const CMS_NAMESPACE = 'urn:magento:cms';

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
            $xml->writeAttribute('xmlns:htm', self::HTM_NAMESPACE);
            $xml->writeAttribute('xmlns:cms', self::CMS_NAMESPACE);
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
                    $source = $this->_getInlineXml($source);
                    foreach ($writers as $xml) {
                        $xml->startElement('trans-unit');
                        $xml->writeAttribute('id', $id);
                        $xml->startElement('source');
                        $xml->writeRaw($source);
                        $xml->endElement(); // source
                        $xml->startElement('target');
                        $xml->writeRaw($source);
                        $xml->endElement(); // target
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

    protected function _getInlineXml($source)
    {
        $source = str_replace("\r\n", "\n", $source);
        // use second XMLWriter without a document to produce valid, raw XML
        $xml = new XMLWriter();
        $xml->openMemory();

        // split text into array of basic HTML tags and CMS directives and text
        $parts = preg_split('/(<(?:{{.*?}}|.)*?>|{{.*?}})/', $source, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        // only tag names that were parsed, used for finding closing partners
        $tagStack = array();
        foreach ($parts as $part) {
            if (preg_match('/<(\w+)(.*?)>/', $part, $tag)) {
                list(, $tagName, $attributes) = $tag;
                $attributes = $this->_parseAttributes($attributes);
                switch ($tagName) {
                    case 'area':
                    case 'br':
                    case 'col':
                    case 'hr':
                    case 'img':
                    case 'input':
                    case 'nobr':
                    case 'wbr':
                        // do not push stack since 'x' is an empty type
                        $this->_writeEmptyElement($xml, $tagName, $attributes);
                        break;
                    default:
                        $tagStack[] = $tagName;
                        $this->_writeGroupElement($xml, $tagName, $attributes);
                }
            }
            elseif (preg_match('/<\/(\w+)>/', $part, $tag)) {
                list(, $tagName) = $tag;
                // closing tag without opening tag is ignored
                if (array_search($tagName, $tagStack) === false) continue;
                // pop off as many tags as necessary
                do {
                    $xml->endElement();
                } while ($tagName != array_pop($tagStack));
            }
            elseif (preg_match('/^{{.*}}$/', $part)) {
                // base64 encode all CMS directives whether opening, closing, or empty
                $xml->startElement('ph');
                $xml->writeAttribute('ctype', 'x-cms-directive');
                if (preg_match('/{{var (.*?)}}/', $part, $variable)) {
                    $xml->writeAttribute('equiv-text', $variable[1]);
                }
                $xml->text(base64_encode(trim($part, '{}')));
                $xml->endElement();
            }
            else {
                $xml->text($part);
            }
        }
        while ($xml->endElement());

        return $xml->outputMemory();
    }

    protected function _parseAttributes($text)
    {
        $attributes = array();
        preg_match_all('/\s*(\w+)\s*=\s*("(?:{{.+?}}|.)*?"|\'(?:{{.+?}}|.)*?\'|\S+?)/', $text, $pairs, PREG_SET_ORDER);
        foreach ($pairs as $pair) {
            list(, $name, $val) = $pair;
            $val = trim($val, '"\'');
            if (preg_match('/{{.*?}}/', $val)) {
                $attributes['cms:'.$name] = base64_encode($val);
            }
            else {
                $attributes['htm:'.$name] = $val;
            }
        }
        // TODO: generate a unique htm:id
        return $attributes;
    }

    protected function _writeEmptyElement(XMLWriter $xml, $tagName, $attributes)
    {
        // translateable attributes
        $subs = array_intersect_key($attributes, array(
            'htm:abbr' => true,
            'htm:alt' => true,
            'htm:content' => true,
            'htm:label' => true,
            'htm:standby' => true,
            'htm:summary' => true,
            'htm:title' => true
        ));
        // ignore empty values
        $subs = array_filter($subs);

        if ($subs) {
            $xml->startElement('ph');
            $xml->writeAttribute('ctype', $this->ctype($tagName));
            foreach (array_diff_key($attributes, $subs) as $name => $value) {
                $xml->writeAttribute($name, $value);
            }
            foreach ($subs as $name => $value) {
                $xml->startElement('sub');
                $xml->writeAttribute('ctype', "x-html-$tagName-".substr($name, 4));
                $xml->text($value);
                $xml->endElement();
            }
            $xml->endElement();
        }
        else {
            $xml->startElement('x');
            $xml->writeAttribute('ctype', $this->ctype($tagName));
            foreach ($attributes as $name => $value) {
                $xml->writeAttribute($name, $value);
            }
            $xml->endElement();
        }
    }

    protected function _writeGroupElement(XMLWriter $xml, $tagName, $attributes)
    {
        $xml->startElement('g');
        $xml->writeAttribute('ctype', $this->ctype($tagName));
        foreach ($attributes as $name => $value) {
            $xml->writeAttribute($name, $value);
        }
        // TODO: use <bpt> when an attribute can be translated with a <sub>
        // this will be hard because it needs a matching <ept> somewhere
    }

    protected function ctype($tagName)
    {
        switch ($tagName) {
            case 'img':
                return 'image';
            case 'hr':
                return 'pb';
            case 'br':
                return'lb';
            case 'b':
            case 'strong':
                return 'bold';
            case 'em':
            case 'i':
                return 'italic';
            case 'u':
                return 'underline';
            case 'a':
                return 'link';
            default:
                return 'x-html-'.$tagName;
        }
    }
}
