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
        $this->filename = tempnam(sys_get_temp_dir(), 'xliff');
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
        $this->writer->output($this->filename, array());
        $this->assertXPathMatches(1, 'count(/x:xliff)', 'Document starts with "xliff" element');
        $this->assertXPathMatches(1, 'count(/x:xliff/x:file)', 'Document has one "file" element');
        $this->assertXPathMatches(1, 'count(/x:xliff/x:file/x:body)', 'Document has one "body" element');
    }

    /**
     * @test
     */
    public function xmlCanHaveGroups()
    {
        $this->writer->output($this->filename, array(), 'genus');
        $this->assertXPathMatches(1, 'count(//x:group)', 'One group was specified');
        $this->assertXPathMatches('genus', 'string(//x:group/@id)', '"genus" is the latin for group');
    }

    /**
     * @test
     */
    public function attributesAreIncluded()
    {
        $this->writer->output($this->filename, array(
            array(
                'type'=>'A cup',
            )
        ));
        $this->assertXPathMatches(1, 'count(//x:group)', 'Entities/models are represented as groups');
        $this->assertXPathMatches('A cup', 'string(//x:trans-unit[@id="type"]/x:source)', 'Trans unit IDs are attribute key');
    }

    /**
     * @test
     */
    public function attributesAreExcluded()
    {
        $this->writer->output($this->filename, array(
            array(
                'name'=>'World\'s best dad mug',
                'type'=>'A cup',
                'description'=>'A modern classic'
            )
        ), null, array('type', 'description', 'imaginary'));
        $this->assertXPathMatches(2, 'count(//x:source)', 'Attributes are filterable');
        $this->assertXPathMatches('A cup', 'string(//x:trans-unit[@id="type"]/x:source)');
        $this->assertXPathMatches('A modern classic', 'string(//x:trans-unit[@id="description"]/x:source)');
    }
}
