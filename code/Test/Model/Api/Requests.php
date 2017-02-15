<?php

class Capita_TI_Test_Model_Api_Requests extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @var Zend_Http_Client_Adapter_Test
     */
    protected $adapter;

    /**
     * @var Capita_TI_Model_Api_Requests
     */
    protected $subject;

    protected function setUp()
    {
        $this->adapter = new Zend_Http_Client_Adapter_Test();
        $this->adapter->setResponse("HTTP/1.1 200 OK\r\n".
            "Content-Type: application/json\r\n".
            "\r\n".
            '{}'
        );
        $this->subject = Mage::getModel('capita_ti/api_requests', array(
            'adapter' => $this->adapter
        ));
    }

    /**
     * Realistic data
     * 
     */
    public function extractLanguagesFromFeed()
    {
        $case = new Zend_Controller_Request_HttpTestCase();
        $case->setMethod('POST');
        $case->setParam('source_language', 'en_US');
        $case->setParam('dest_language', array('fr_FR'));
        $case->setParam('products_ids', '1');
        $case->setParam('product_attributes', array('name', 'description'));

        $request = $this->subject->saveNewRequest($case);
        $this->assertInstanceOf('Capita_TI_Model_Request', $request);
        $this->assertTrue($request->hasData(), 'Request model has been populated');
        $this->assertFalse($request->hasDataChanges(), 'Resource model is not different from database');
    }
}
