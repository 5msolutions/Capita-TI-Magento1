<?php

class Capita_TI_Model_Xliff_Import_Product extends Capita_TI_Model_Xliff_Import_Abstract
{

    public function getEntityType()
    {
        return Mage_Catalog_Model_Product::ENTITY;
    }

    public function import($id, $sourceLanguage, $destLanguage, $sourceData, $destData)
    {
        if ($this->getRequest()) {
            if (!in_array($id, $this->getRequest()->getProductIds())) {
                // prevent accidentally importing data which shouldn't be
                // perhaps it wasn't requested or the product was deleted afterwards
                return;
            }
            if (!in_array($destLanguage, $this->getRequest()->getDestLanguage())) {
                // was not expecting this language
                return;
            }
        }

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
