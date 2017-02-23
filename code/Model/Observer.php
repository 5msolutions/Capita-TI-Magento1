<?php

class Capita_TI_Model_Observer
{

    /**
     * Handle adminhtml_catalog_product_grid_prepare_massaction
     * 
     * @param Varien_Event_Observer $observer
     */
    public function addMassActionToBlock(Varien_Event_Observer $observer)
    {
        /* @var $block Mage_Adminhtml_Block_Catalog_Product_Grid */
        $block = $observer->getBlock();
        $block->getMassactionBlock()->addItem('capita_translate', array(
                'label' => Mage::helper('capita_ti')->__('Translate'),
                'url'   => Mage::getUrl('*/catalog_product/translate')
            ));
    }

    public function cronImport(Mage_Cron_Model_Schedule $schedule)
    {
        /* @var $reader Capita_TI_Model_Xliff_Reader */
        $reader = Mage::getSingleton('capita_ti/xliff_reader');
        $reader->addType(Mage::getSingleton('capita_ti/xliff_import_product'));
        $reader->addType(Mage::getSingleton('capita_ti/xliff_import_category'));
        $varDir = Mage::getConfig()->getVarDir() . DS;

        /* @var $requests Capita_TI_Model_Resource_Request_Collection */
        $requests = Mage::getModel('capita_ti/request')->getCollection();
        $requests->addImportingFilter();

        /* @var $request Capita_TI_Model_Request */
        foreach ($requests as $request) {
            try {
                /* @var $document Capita_TI_Model_Request_Document */
                foreach ($request->getDocuments() as $document) {
                    if ($document->getStatus() == 'importing') {
                        $filename = $varDir . $document->getLocalName();
                        $reader->import($filename, $document->getLanguage());
                        $document->setStatus('completed')->save();
                    }
                }
                $request->setStatus('completed')->save();
            }
            catch (Exception $e) {
                Mage::logException($e);
                $request->setStatus('error')->save();
            }
        }
    }
}
