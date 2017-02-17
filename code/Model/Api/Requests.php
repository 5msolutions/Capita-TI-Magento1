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
     * 
     * @param Zend_Controller_Request_Abstract $input
     * @throws Mage_Adminhtml_Exception
     * @throws Zend_Http_Exception
     * @return string
     */
    public function saveNewRequest(Zend_Controller_Request_Abstract $input)
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
        $filename = sprintf('capita-ti-%s.mgxliff', $nextWeek->toString('y-MM-d-HH-mm-ss'));

        /* @var $output Capita_TI_Model_Xliff_Writer */
        $output = Mage::getModel('capita_ti/xliff_writer');
        $output->output($varDir.$filename, $products, Mage_Catalog_Model_Product::ENTITY, $productAttributes, array(
            'source_language' => $sourceLanguage
        ));
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
        return $newRequest->save();
    }
}
