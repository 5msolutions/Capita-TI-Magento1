<?php

class Capita_TI_Model_Api_Requests extends Capita_TI_Model_Api_Abstract
{

    protected function getCustomerName()
    {
        return Mage::getStoreConfig('capita_ti/authentication/customer_name');
    }

    protected function getContactName()
    {
        return Mage::getStoreConfig('capita_ti/authentication/contact_name');
    }

    public function __construct($config = null)
    {
        parent::__construct($this->getEndpoint('requests'), $config);
    }

    /**
     * Writes entities to a file, uploads it to API, and returns an object which describes it.
     * 
     * @param Zend_Controller_Request_Abstract $input
     * @throws Mage_Adminhtml_Exception
     * @throws Zend_Http_Exception
     * @return Capita_TI_Model_Request
     */
    public function startNewRequest(Zend_Controller_Request_Abstract $input)
    {
        $sourceLanguage = $input->getParam('source_language');
        $destLanguage = implode(',', $input->getParam('dest_language'));
        $this->setParameterPost('CustomerName', $this->getCustomerName());
        $this->setParameterPost('ContactName', $this->getContactName());
        $this->setParameterPost('SourceLanguageCode', $sourceLanguage);
        $this->setParameterPost('TargetLanguageCodes', $destLanguage);

        // any future date will probably do
        // API demands a date but doesn't use it
        $nextWeek = new Zend_Date();
        $nextWeek->addWeek(1);
        $this->setParameterPost('DeliveryDate', $nextWeek->toString('y-MM-d HH:mm:ss'));

        // now for the main content
        $productIds = $input->getParam('product_ids');
        $productAttributes = $input->getParam('product_attributes', array());
        /* @var $products Mage_Catalog_Model_Resource_Product_Collection */
        $products = Mage::getResourceModel('catalog/product_collection');
        $products->addAttributeToFilter('entity_id', array('in' => explode(',', $productIds)));
        $products->addAttributeToSelect($productAttributes);

        // limited to one file per upload for now
        $varDir = Mage::getConfig()->getVarDir('export') . DS;
        if (!$varDir) {
            throw new Mage_Adminhtml_Exception(Mage::helper('capita_ti')->__('Cannot write to "%s"', $varDir));
        }
        $filename = sprintf('capita-ti-%s.mgxliff', Zend_Date::now()->toString('y-MM-d-HH-mm-ss'));

        /* @var $output Capita_TI_Model_Xliff_Writer */
        $output = Mage::getModel('capita_ti/xliff_writer');
        $output->addCollection(Mage_Catalog_Model_Product::ENTITY, $products, $productAttributes);
        $output->setSourceLanguage($sourceLanguage);
        $output->output($varDir.$filename);
        $this->setFileUpload($varDir.$filename, 'files');
        $response = $this->decode($this->request('POST'));

        $newRequest = Mage::getModel('capita_ti/request');
        $newRequest
            ->setSourceLanguage($sourceLanguage)
            ->setDestLanguage($destLanguage)
            ->setProductAttributes($productAttributes)
            ->setProductIds($productIds)
            ->addData($response)
            ->addLocalDocument('export'.DS.$filename);
        return $newRequest;
    }

    /**
     * Retrieve latest request info from remote
     * 
     * If there are several updates to process it helps to set keepalive on this client.
     * 
     * @param Capita_TI_Model_Request $request
     */
    public function updateRequest(Capita_TI_Model_Request $request)
    {
        $path = 'request/'.urlencode($request->getRemoteId());
        $this->setUri($this->getEndpoint($path));
        $response = $this->decode($this->request('GET'));
        // downloads might be empty
        $downloads = $request->updateStatus($response);
        /* @var $document Capita_TI_Model_Request_Document */
        foreach ($downloads as $document) {
            $uri = $this->getEndpoint('document/'.$document->getRemoteId());
            $this->setUri($uri)
                ->setStream($document->getLocalName())
                ->request('GET');
        }
        $request->save();
    }
}
