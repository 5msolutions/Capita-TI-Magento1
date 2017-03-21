<?php

class Capita_TI_Helper_Data extends Mage_Core_Helper_Data
{

    /**
     * Returns module string as specified in config.xml
     * 
     * @return string
     */
    public function getModuleVersion()
    {
        return (string) Mage::getConfig()->getNode('modules/Capita_TI/version');
    }

    public function convertHashToOptions($hash)
    {
        $options = array();
        foreach ($hash as $value => $label) {
            $options[] = array(
                'value' => $value,
                'label' => $label
            );
        }
        return $options;
    }

    /**
     * Get list of store IDs that use the given locale code
     * 
     * If $language is NULL then return 2D array keyed by locale code instead.
     * 
     * @param string $language
     * @return array
     */
    public function getStoreIdsByLanguage($language = null)
    {
        $stores = array();
        /* @var $store Mage_Core_Model_Store */
        foreach (Mage::app()->getStores() as $store) {
            $storeLang = $store->getConfig('general/locale/code');
            $stores[$storeLang][] = $store->getId();
        }
        return $language ? (array) @$stores[$language] : $stores;
    }

    /**
     * Get possible locale codes (other than default store)
     * 
     * Does not compare with remote languages so might include
     * untranslatable languages, albeit unlikely.
     * 
     * @return string[]
     */
    public function getNonDefaultLocales()
    {
        $locales = array();
        foreach (Mage::app()->getStores() as $store) {
            $locales[$store->getConfig('general/locale/code')] = true;
        }
        unset($locales[Mage::getStoreConfig('general/locale/code')]);
        return array_keys($locales);
    }
}
