<?php

class Capita_TI_Test_Model_Api_Requests extends EcomDev_PHPUnit_Test_Case
{

    /**
     * @var Capita_TI_Model_Api_Requests
     */
    protected $subject;

    protected function setUp()
    {
        $this->subject = Mage::getModel('capita_ti/api_requests', array(
            'adapter' => Mage::getModel('capita_ti/api_adapter_samplePostRequest')
        ));
    }

    /**
     * Data copied from API spec.
     * 
     * @see https://api.capitatranslationinterpreting.com/
     * @test
     */
    public function newRequestReturnsStatusInfo()
    {
        $case = new Zend_Controller_Request_HttpTestCase();
        $case->setMethod('POST');
        $case->setParam('source_language', 'en_US');
        $case->setParam('dest_language', array('fr_FR'));
        $case->setParam('product_ids', '1');
        $case->setParam('product_attributes', array('name', 'description'));

        $request = $this->subject->startNewRequest($case);
        $this->assertInstanceOf('Capita_TI_Model_Request', $request);
        $this->assertTrue($request->hasData() && $request->hasDataChanges(), 'Request model has been populated');
        $this->assertEquals('1250936094-13321', $request->getRemoteId());
        $this->assertEquals('CTI-160302-1', $request->getRemoteNo());
        $this->assertEquals('onHold', $request->getStatus());
        $this->assertCount(2, $request->getDocuments());
        $request->delete();
    }
}
