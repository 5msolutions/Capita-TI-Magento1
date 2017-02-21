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
        /* @var $documents Capita_TI_Model_Resource_Request_Document_Collection */
        $documents = Mage::getResourceModel('capita_ti/request_document_collection');
        $documents->addStatusFilter('importing');

        /* @var $reader Capita_TI_Model_Xliff_Reader */
        $reader = Mage::getSingleton('capita_ti/xliff_reader');
        $reader->addType(Mage::getSingleton('capita_ti/xliff_import_product'));
        $varDir = Mage::getConfig()->getVarDir() . DS;

        foreach ($documents as $document) {
            $filename = $varDir . $document->getLocalName();
            $reader->import($filename);
            $document->setStatus('completed')
                ->save();
        }
    }
}
