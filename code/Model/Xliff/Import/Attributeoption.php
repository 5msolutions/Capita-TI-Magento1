<?php

class Capita_TI_Model_Xliff_Import_Attributeoption extends Capita_TI_Model_Xliff_Import_Abstract
{

    public function getEntityType()
    {
        return 'eav_attribute_option';
    }

    public function import($id, $sourceLanguage, $destLanguage, $sourceData, $destData)
    {
        if ($this->getRequest()) {
            if (!in_array($destLanguage, $this->getRequest()->getDestLanguage())) {
                // was not expecting this language
                return;
            }
        }
        if (!isset($destData['value'])) {
            // this is the only value needed
            return;
        }

        // The intention was to save all imports through existing models which respect events, etc.
        // But the only way to save attribute option values is with the entire attribute.
        // This works by loading all options into memory, deleting from DB,
        // then saving them all back which is far too slow.
        // It doesn't even use a transaction so could lose data due to time
        // limits during big imports.

        /* @var $option Mage_Eav_Model_Entity_Attribute_Option */
        $option = Mage::getModel('eav/entity_attribute_option')->load($id);

        // prevent accidentally importing data which shouldn't be
        if ($option->isObjectNew()) {
            return;
        }
        if ($this->getRequest() && !in_array($option->getAttributeId(), $this->getRequest()->getAttributeIds())) {
            return;
        }

        $adapter = $option->getResource()->getReadConnection();
        $tableName = $option->getResource()->getTable('eav/attribute_option_value');
        /* @var $store Mage_Core_Model_Store */
        foreach (Mage::app()->getStores() as $store) {
            $code = (string) $store->getConfig('general/locale/code');
            if ($code == $destLanguage) {
                // would like to do INSERT ON DUPLICATE UPDATE here but table lacks an appropriate index
                $adapter->delete($tableName, array(
                    'option_id = ?' => $id,
                    'store_id = ?' => $store->getId()
                ));
                $adapter->insert($tableName, array(
                    'option_id' => $id,
                    'store_id' => $store->getId(),
                    'value' => $destData['value']
                ));
            }
        }
    }
}
