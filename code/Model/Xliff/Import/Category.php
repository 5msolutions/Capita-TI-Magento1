<?php

class Capita_TI_Model_Xliff_Import_Category extends Capita_TI_Model_Xliff_Import_Abstract
{

    public function getEntityType()
    {
        return Mage_Catalog_Model_Category::ENTITY;
    }

    public function import($id, $sourceLanguage, $destLanguage, $sourceData, $destData)
    {
        if ($this->getRequest()) {
            if (!in_array($id, $this->getRequest()->getCategoryIds())) {
                // prevent accidentally importing data which shouldn't be
                // perhaps it wasn't requested or the category was deleted afterwards
                return;
            }
            if (strpos($destLanguage, $this->getRequest()->getDestLanguage()) === false) {
                // was not expecting this language
                return;
            }
        }

        /* @var $store Mage_Core_Model_Store */
        foreach (Mage::app()->getStores() as $store) {
            $code = (string) $store->getConfig('general/locale/code');
            if ($code == $destLanguage) {
                /* @var $category Mage_Catalog_Model_Category */
                $category = Mage::getModel('catalog/category')
                    ->setStoreId($store->getId())
                    ->load($id);
                if ($category->isObjectNew()) return;

                /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                foreach ($category->getAttributes() as $code => $attribute) {
                    if (!$attribute->isScopeGlobal() && !$category->getExistsStoreValueFlag($code)) {
                        $category->unsetData($code);
                    }
                }
                $category
                    ->addData($destData)
                    ->save();
            }
        }
    }
}
