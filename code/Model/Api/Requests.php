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
        if (!@$config['adapter']) {
            // libcurl is faster but breaks on streaming large downloads
            $config['adapter'] = 'Zend_Http_Client_Adapter_Socket';
        }
        if (!@$config['timeout']) {
            $config['timeout'] = 120;
        }

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
        $sourceLanguage = $input->getParam('source_language', Mage::getStoreConfig('general/locale/code'));
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
        $productIds = $input->getParam('product_ids', '');
        $productIds = array_filter(array_unique(preg_split('/[,&]/', $productIds)));
        $productAttributes = $input->getParam('product_attributes', array());
        /* @var $products Mage_Catalog_Model_Resource_Product_Collection */
        $products = Mage::getResourceModel('catalog/product_collection');
        $products->addIdFilter($productIds);
        $products->addAttributeToSelect($productAttributes);

        $categoryIds = $input->getParam('category_ids', '');
        $categoryIds = array_filter(array_unique(explode(',', $categoryIds)));
        $categoryAttributes = $input->getParam('category_attributes', array());
        /* @var $categories Mage_Catalog_Model_Resource_Category_Collection */
        $categories = Mage::getResourceModel('catalog/category_collection');
        $categories->addIdFilter($categoryIds);
        $categories->addAttributeToSelect($categoryAttributes);

        $blockIds = $input->getParam('block_ids', array());
        $blockIds = array_filter(array_unique($blockIds));
        /* @var $blocks Mage_Cms_Model_Resource_Block_Collection */
        $blocks = Mage::getResourceModel('cms/block_collection');
        $blocks->addFieldToFilter('block_id', array('in' => $blockIds));

        $pageIds = $input->getParam('page_ids', array());
        $pageIds = array_filter(array_unique($pageIds));
        /* @var $pages Mage_Cms_Model_Resource_Page_Collection */
        $pages = Mage::getResourceModel('cms/page_collection');
        $pages->addFieldToFilter('page_id', array('in' => $pageIds));

        if (!$productIds && !$categoryIds && !$blockIds && !$pageIds) {
            throw new InvalidArgumentException(
                Mage::helper('capita_ti')->__('Must specify at least one product, category, block or page'));
        }

        /* @var $newRequest Capita_TI_Model_Request */
        $newRequest = Mage::getModel('capita_ti/request');
        $newRequest
            ->setSourceLanguage($sourceLanguage)
            ->setDestLanguage($destLanguage)
            ->setProductAttributes($productAttributes)
            ->setProductIds($productIds)
            ->setCategoryIds($categoryIds)
            ->setCategoryAttributes($categoryAttributes)
            ->setBlockIds($blockIds)
            ->setPageIds($pageIds);

        $varDir = Mage::getConfig()->getVarDir('export') . DS;
        if (!$varDir) {
            throw new Mage_Adminhtml_Exception(Mage::helper('capita_ti')->__('Cannot write to "%s"', $varDir));
        }
        $filenames = array();
        $absFilenames = array();
        foreach (explode(',', $destLanguage) as $language) {
            $language = strtr($language, '_', '-');
            $filenames[$language] = sprintf(
                'capita-ti-%d-%s.mgxliff',
                time(),
                $language);
            $absFilenames[$language] = $varDir.$filenames[$language];
        }

        /* @var $output Capita_TI_Model_Xliff_Writer */
        $output = Mage::getModel('capita_ti/xliff_writer');
        $output->addCollection(Mage_Catalog_Model_Product::ENTITY, $products, $newRequest->getProductAttributesArray());
        $output->addCollection(Mage_Catalog_Model_Category::ENTITY, $categories, $newRequest->getCategoryAttributesArray());
        $output->addCollection(Mage_Cms_Model_Block::CACHE_TAG, $blocks, array('title', 'content'));
        $output->addCollection(Mage_Cms_Model_Page::CACHE_TAG, $pages, array('title', 'content', 'content_heading', 'meta_keywords', 'meta_description'));
        $output->setSourceLanguage($sourceLanguage);
        $output->output($absFilenames);
        foreach ($absFilenames as $absFilename) {
            $this->setFileUpload($absFilename, 'files');
        }
        $response = $this->decode($this->request(self::POST));

        $newRequest->addData($response);
        foreach ($filenames as $filename) {
            $newRequest->addLocalDocument('export'.DS.$filename);
        }
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
        try {
            $response = $this->decode($this->request(self::GET));

            // downloads might be empty
            $downloads = $request->updateStatus($response);
            $varDir = Mage::getConfig()->getVarDir() . DS;
            /* @var $document Capita_TI_Model_Request_Document */
            foreach ($downloads as $document) {
                $uri = $this->getEndpoint('document/'.$document->getRemoteId());
                $this->setUri($uri)
                    ->setStream($varDir . $document->getLocalName())
                    ->request(self::GET);
                $document->setStatus('importing');
                $request->setStatus('importing');
            }
    
            // also saves all documents
            $request->save();
        }
        catch (Zend_Http_Exception $e) {
            // 404 means probably cancelled, delete our record of it
            if ($e->getCode() == 404) {
                $request->delete();
            }
            else {
                throw $e;
            }
        }
    }
}
