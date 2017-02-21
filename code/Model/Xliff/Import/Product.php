<?php

class Capita_TI_Model_Xliff_Import_Product implements Capita_TI_Model_Xliff_Import_Interface
{

    public function getEntityType()
    {
        return Mage_Catalog_Model_Product::ENTITY;
    }

    public function import($id, $sourceLanguage, $destLanguage, $sourceData, $destData)
    {
        /* @var $action Mage_Catalog_Model_Product_Action */
        $action = Mage::getModel('catalog/product_action');

        /* @var $store Mage_Core_Model_Store */
        foreach (Mage::app()->getStores() as $store) {
            $code = (string) $store->getConfig('general/locale/code');
            if ($code == $destLanguage) {
                $action->updateAttributes(array($id), $destData, $store->getId());
            }
        }
    }
}
