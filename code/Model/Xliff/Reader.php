<?php

class Capita_TI_Model_Xliff_Reader
{

    /**
     * Each import type handles a specific entity type
     * 
     * @var Capita_TI_Model_Xliff_Import_Abstract[]
     */
    protected $_types;

    /**
     * An additional hint for importers to know where to save data
     * 
     * @var Capita_TI_Model_Request
     */
    protected $_request;

    /**
     * Register an importer to be matched to each entity tpe
     * 
     * @param string $type
     * @param string $model
     * @return Capita_TI_Model_Xliff_Reader
     */
    public function addType(Capita_TI_Model_Xliff_Import_Abstract $type)
    {
        $this->_types[$type->getEntityType()] = $type;
        return $this;
    }

    /**
     * Retrieve a specific type importer
     * 
     * @param string $type
     * @return Capita_TI_Model_Xliff_Import_Abstract|null
     */
    public function getImporter($type)
    {
        return isset($this->_types[$type]) ? $this->_types[$type] : null;
    }

    /**
     * Optionally set a request to be passed to importers in the following import() calls
     * 
     * @param Capita_TI_Model_Request $request
     * @return Capita_TI_Model_Xliff_Reader
     */
    public function setRequest(Capita_TI_Model_Request $request)
    {
        $this->_request = $request;
        return $this;
    }

    public function import($uri, $language = null)
    {
        $xml = new XMLReader();
        $xml->open($uri) or $this->__('Could not open "%s"', $uri);

        $xml->read() or $this->__('Could not read root element');
        $xml->name == 'xliff' or $this->__('Expected "%s" element but got "%s"', 'xliff', $xml->name);
        $version = $xml->getAttribute('version');
        $version == '1.2' or $this->__('XLIFF version is "%s" and needs to be "1.2"', $version);

        while ($this->_readFile($xml, $language));

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

    protected function _readFile(XMLReader $xml, $language = null)
    {
        if (!$this->_nextElement($xml)) {
            return false;
        }

        $xml->name == 'file' or $this->__('Expected "%s" element but got "%s"', 'file', $xml->name);
        $origin = $xml->getAttribute('original') or $this->__('File origin is not specified');
        $sourceLanguage = $xml->getAttribute('source-language') or $this->__('Source language is not specified');
        $sourceLanguage = strtr($sourceLanguage, '-', '_');
        $destLanguage = $xml->getAttribute('target-language');
        if (!$destLanguage) {
            $destLanguage = $language or $this->__('Target language is not specified');
        }
        $destLanguage = strtr($destLanguage, '-', '_');
        if (strpos($origin, '/') !== false) {
            list($origin, $id) = explode('/', $origin);
        }
        else {
            $id == '';
        }
        $importer = $this->getImporter($origin) or $this->__('Unrecognised file origin: "%s"', $origin);
        if ($this->_request) {
            $importer->setRequest($this->_request);
        }

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

            while ($xml->read() && $xml->nodeType != XMLReader::ELEMENT);
            $xml->name == 'source' or $this->__('Expected "%s" element but got "%s"', 'source', $xml->name);
            $sourceData[$key] = $this->readHtml($xml->expand());

            while ($xml->name != 'target') {
                $xml->next();
            }
            $destData[$key] = $this->readHtml($xml->expand());

            while ($xml->next() && $xml->name != 'trans-unit');
        }
        $importer->import($id, $sourceLanguage, $destLanguage, $sourceData, $destData);

        while ($xml->read() && $xml->name != 'file');
        return true;
    }

    /**
     * Decode XLIFF elements into raw HTML and CMS directives
     * 
     * @param DOMNode $source
     * @return string
     */
    public function readHtml(DOMNode $source)
    {
        $html = '';
        $node = $source->firstChild;
        while ($node) {
            if ($node instanceof DOMText) {
                $html .= $node->nodeValue;
            }
            elseif ($node instanceof DOMElement) {
                if ($node->getAttribute('ctype') == 'x-cms-directive') {
                    $html .= base64_decode($node->textContent);
                }
                else switch ($node->tagName) {
                    case 'g':
                        $tagName = $this->getTagFromCtype($node);
                        $attributes = $this->getAttributes($node);
                        $html .= '<'.$tagName.$attributes.'>';
                        $html .= $this->readHtml($node);
                        $html .= '</'.$tagName.'>';
                        break;
                    case 'ph':
                        $tagName = $this->getTagFromCtype($node);
                        $attributes = $this->getAttributes($node);
                        $html .= '<'.$tagName.$attributes.$this->readSubs($node).'>';
                        // no closing tag
                        break;
                    case 'x':
                        $tagName = $this->getTagFromCtype($node);
                        $attributes = $this->getAttributes($node);
                        $html .= '<'.$tagName.$attributes.'>';
                        // no closing tag
                        break;
                    default:
                        $this->__('Unrecognised element: <%s>', $node->tagName);
                }
            }
            $node = $node->nextSibling;
        }
        return $html;
    }

    protected function getTagFromCtype(DOMElement $element)
    {
        $ctype = $element->getAttribute('ctype');
        switch ($ctype) {
            case 'image':
                return 'img';
            case 'pb':
                return 'hr';
            case 'lb':
                return 'br';
            case 'bold':
                return 'strong';
            case 'italic':
                return 'em';
            case 'underline':
                return 'u';
            case 'link':
                return 'a';
            default:
                if (preg_match('/^x-html-(\w+)$/', $ctype, $result)) {
                    return $result[1];
                }
        }
        $this->__('Unrecognised ctype: "%s"', $ctype);
    }

    protected function getAttributes(DOMElement $element)
    {
        $attrs = '';
        foreach ($element->attributes as $attribute) {
            $name = $attribute->nodeName;
            $value = $attribute->nodeValue;
            if (strpos($name, 'htm:') === 0) {
                $name = substr($name, 4);
            }
            elseif (strpos($name, 'cms:') === 0) {
                $name = substr($name, 4);
                $value = base64_decode($value);
            }
            else {
                continue;
            }
            $attrs .= sprintf(' %s="%s"', $name, $value);
        }
        return $attrs;
    }

    protected function readSubs(DOMElement $element)
    {
        $attrs = '';
        $sub = $element->firstChild;
        while ($sub) {
            if ($sub instanceof DOMElement && $sub->tagName == 'sub') {
                $ctype = $sub->getAttribute('ctype');
                $name = preg_replace('/^x-html-\w+-(\w+)$/', '$1', $ctype);
                $value = $sub->textContent;
                $attrs .= sprintf(' %s="%s"', $name, $value);
            }
            $sub = $sub->nextSibling;
        }
        return $attrs;
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
