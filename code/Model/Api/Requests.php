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

    public function saveNewRequest(Zend_Controller_Request_Abstract $input)
    {
        $this->setParameterPost('CustomerName', $this->getCustomerName());
        $this->setParameterPost('ContactName', $this->getContactName());
        $this->setParameterPost('SourceLanguageCode', $input->getParam('source_language'));
        $this->setParameterPost('TargetLanguageCodes', implode(',', $input->getParam('dest_language')));

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
        $varDir = Mage::getConfig()->getVarDir('export');
        if (!$varDir) {
            throw new Mage_Adminhtml_Exception(Mage::helper('capita_ti')->__('Cannot write to "%s"', $varDir));
        }
        $filename = $varDir . DS . sprintf('capita-ti-%s.xliff', $nextWeek->toString('y-MM-d-HH-mm-ss'));

        /* @var $output Capita_TI_Model_Xliff_Writer */
        $output = Mage::getModel('capita_ti/xliff_writer');
        $output->output($filename, $products, null, $productAttributes);
        $this->setFileUpload($filename, 'files');
        $this->request('POST');
        return $filename;
    }
}
