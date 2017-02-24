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
     * Get used locales in options/values format
     * 
     * @return array
     */
    public function getStoreLocalesOptions()
    {
        $languages = Mage::getSingleton('capita_ti/api_languages')->getLanguagesInUse();
        return $this->convertHashToOptions($languages);
    }
}
