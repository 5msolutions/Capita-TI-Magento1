<?php

class Capita_TI_Test_Model_Api_Languages extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @var Zend_Http_Client_Adapter_Test
     */
    protected $adapter;

    /**
     * @var Capita_TI_Model_Api_Languages
     */
    protected $subject;

    protected function setUp()
    {
        $this->adapter = new Zend_Http_Client_Adapter_Test();
        $this->subject = Mage::getModel('capita_ti/api_languages', array(
            'adapter' => $this->adapter
        ));
    }

    protected function tearDown()
    {
        $this->subject->clearCache();
    }

    /**
     * @test
     */
    public function jsonIsGoodResponse()
    {
        $this->adapter->setResponse("HTTP/1.1 200 OK\r\n".
                "Content-Type: application/json\r\n".
                "\r\n".
                '{"message":"All\'s well"}'
            );
        $this->assertTrue(is_array($this->subject->getLanguages()), 'JSON object is converted to array');
    }

    /**
     * @test
     * @expectedException Zend_Http_Exception
     * @expectedExceptionMessage Doom
     */
    public function jsonCanBeErrorMessage()
    {
        $this->adapter->setResponse("HTTP/1.1 403 Unauthorized\r\n".
                "Content-Type: application/json\r\n".
                "\r\n".
                '{"message":"Doom!"}'
            );
        $this->subject->getLanguages();
    }

    /**
     * @test
     * @expectedException Zend_Http_Exception
     * @expectedExceptionMessage Unauthorized
     */
    public function errorMessageCanBeStatusLine()
    {
        $this->adapter->setResponse("HTTP/1.1 403 Unauthorized\r\n".
                "Content-Type: text/plain\r\n".
                "\r\n".
                'Woe is me'
            );
        $this->subject->getLanguages();
    }

    /**
     * @test
     * @expectedException Zend_Json_Exception
     */
    public function jsonMustBeWellFormed()
    {
        $this->adapter->setResponse("HTTP/1.1 200 OK\r\n".
                "Content-Type: application/json\r\n".
                "\r\n".
                'This is not JSON'
            );
        $this->subject->getLanguages();
    }

    /**
     * @test
     * @expectedException Zend_Http_Exception
     */
    public function jsonMustBeTheContentType()
    {
        $this->adapter->setResponse("HTTP/1.1 200 OK\r\n".
                "Content-Type: text/plain\r\n".
                "\r\n".
                'I am Jack\'s smirking revenge.'
            );
        $this->subject->getLanguages();
    }

    /**
     * Realistic data
     * 
     * @test
     */
    public function extractLanguagesFromFeed()
    {
        $this->adapter->setResponse("HTTP/1.1 200 OK\r\n".
            "Content-Type: application/json\r\n".
            "\r\n".
            '[{"IsoCode":"en-US","LanguageName":"English"},{"IsoCode":"cy-GB","LanguageName":"Welsh"}]'
            );
        $languages = $this->subject->getLanguages();
        $this->assertTrue(is_array($languages), 'JSON object is converted to array');
        $this->assertNotEmpty($languages, 'At least one entry');
        $this->assertContainsOnly('string', $languages);
        $this->assertArrayHasKey('cy_GB', $languages, 'IsoCode is converted to local convention');
        $this->assertEquals('Welsh', @$languages['cy_GB']);

        // assume US English because EComDev builds database from install scripts and they always start with en_US store
        $usefulLanguages = $this->subject->getLanguagesInUse();
        $this->assertTrue(is_array($usefulLanguages));
        $this->assertCount(1, $usefulLanguages);
        $this->assertArrayHasKey('en_US', $usefulLanguages);
    }
}
