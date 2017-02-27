<?php

class Capita_TI_Test_Model_Xliff_Writer2 extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @var string[]
     */
    protected $filenames;

    /**
     * @var Capita_TI_Model_Xliff_Writer
     */
    protected $writer;

    /**
     * @var DomDocument[]
     */
    protected $documents;

    protected function setUp()
    {
        $this->filenames['fr'] = tempnam(sys_get_temp_dir(), 'mgxliff');
        $this->filenames['de'] = tempnam(sys_get_temp_dir(), 'mgxliff');
        foreach ($this->filenames as $filename) touch($filename);
        $this->writer = Mage::getModel('capita_ti/xliff_writer');
    }

    protected function tearDown()
    {
        foreach ($this->filenames as $filename) unlink($filename);
        unset($this->document);
    }

    protected function assertXPathMatches($expected, $language, $path, $message = null)
    {
        if (!@$this->documents[$language]) {
            $this->documents[$language] = new DOMDocument();
            $this->documents[$language]->load($this->filenames[$language]);
        }
        $xpath = new DOMXPath($this->documents[$language]);
        $xpath->registerNamespace('x', Capita_TI_Model_Xliff_Writer::XML_NAMESPACE);
        $this->assertEquals($expected, $xpath->evaluate($path), $message);
    }

    /**
     * @test
     */
    public function writesTwoFiles()
    {
        $collection = new Varien_Data_Collection();
        $collection->addItem(new Varien_Object());
        $this->writer->addCollection('foo', $collection, array());
        $this->writer->output($this->filenames);
        $this->assertXPathMatches(1, 'fr', 'count(/x:xliff)', 'Document starts with "xliff" element');
        $this->assertXPathMatches(1, 'de', 'count(/x:xliff)', 'Document starts with "xliff" element');
    }
}
