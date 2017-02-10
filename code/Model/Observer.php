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
                'url'   => Mage::getUrl('*/capita_product/enqueue')
            ));
    }

}
