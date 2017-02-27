<?php

class Capita_TI_Test_Model_Xliff_Writer extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var Capita_TI_Model_Xliff_Writer
     */
    protected $writer;

    /**
     * @var DomDocument
     */
    protected $document;

    protected function setUp()
    {
        $this->filename = tempnam(sys_get_temp_dir(), 'mgxliff');
        touch($this->filename);
        $this->writer = Mage::getModel('capita_ti/xliff_writer');
    }

    protected function tearDown()
    {
        unlink($this->filename);
        unset($this->document);
    }

    protected function assertXPathMatches($expected, $path, $message = null)
    {
        if (!$this->document) {
            $this->document = new DOMDocument();
            $this->document->load($this->filename);
        }
        $xpath = new DOMXPath($this->document);
        $xpath->registerNamespace('x', Capita_TI_Model_Xliff_Writer::XML_NAMESPACE);
        $this->assertEquals($expected, $xpath->evaluate($path), $message);
    }

    /**
     * @test
     */
    public function xmlHasBasicStructure()
    {
        $collection = new Varien_Data_Collection();
        $collection->addItem(new Varien_Object());
        $this->writer->addCollection('foo', $collection, array());
        $this->writer->output($this->filename);
        $this->assertXPathMatches(1, 'count(/x:xliff)', 'Document starts with "xliff" element');
        $this->assertXPathMatches(1, 'count(/x:xliff/x:file[@original="foo/0"])', 'Document has one "file" element');
        $this->assertXPathMatches(1, 'count(/x:xliff/x:file/x:body)', 'Document has one "body" element');
    }

    /**
     * @test
     */
    public function attributesAreIncluded()
    {
        $cupboard = new Varien_Data_Collection();
        $cupboard->addItem(new Varien_Object(array(
                'type'=>'A cup',
            )));
        $this->writer->addCollection('cupboard', $cupboard, array('type'));
        $this->writer->output($this->filename);
        $this->assertXPathMatches('A cup', 'string(//x:trans-unit[@id="type"]/x:source)', 'Trans unit IDs are attribute key');
    }

    /**
     * @test
     */
    public function attributesAreExcluded()
    {
        $cupboard = new Varien_Data_Collection();
        $cupboard->addItem(new Varien_Object(array(
                'name'=>'World\'s best dad mug',
                'type'=>'A cup',
                'description'=>'A modern classic'
            )));
        $this->writer->addCollection('cupboard', $cupboard, array('type', 'description', 'imaginary'));
        $this->writer->output($this->filename);
        $this->assertXPathMatches(2, 'count(//x:source)', 'Attributes are filterable');
        $this->assertXPathMatches('A cup', 'string(//x:trans-unit[@id="type"]/x:source)');
        $this->assertXPathMatches('A modern classic', 'string(//x:trans-unit[@id="description"]/x:source)');
    }
}
