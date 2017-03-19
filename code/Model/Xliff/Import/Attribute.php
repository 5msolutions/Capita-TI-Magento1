<?php

class Capita_TI_Model_Xliff_Import_Attribute extends Capita_TI_Model_Xliff_Import_Abstract
{

    public function getEntityType()
    {
        return 'eav_attribute';
    }

    public function import($id, $sourceLanguage, $destLanguage, $sourceData, $destData)
    {
        if ($this->getRequest()) {
            if (!in_array($id, $this->getRequest()->getAttributeIds())) {
                // prevent accidentally importing data which shouldn't be
                return;
            }
            if (!in_array($destLanguage, $this->getRequest()->getDestLanguage())) {
                // was not expecting this language
                return;
            }
        }
        if (!isset($destData['frontend_label'])) {
            // this is the only value needed
            return;
        }

        /* @var $attribute Mage_Eav_Model_Entity_Attribute */
        $attribute = Mage::getModel('eav/entity_attribute')->load($id);
        $labels = $attribute->getStoreLabels();

        /* @var $store Mage_Core_Model_Store */
        foreach (Mage::app()->getStores() as $store) {
            $code = (string) $store->getConfig('general/locale/code');
            if ($code == $destLanguage) {
                $labels[$store->getId()] = $destData['frontend_label'];
                $attribute->setStoreLabels($labels);
            }
        }

        // does nothing if labels have not changed
        $attribute->save();
    }
}
