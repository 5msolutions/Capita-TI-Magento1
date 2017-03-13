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

    public function getStoreIdsByLanguage($language)
    {
        $stores = array();
        /* @var $store Mage_Core_Model_Store */
        foreach (Mage::app()->getStores() as $store) {
            if ($language == $store->getConfig('general/locale/code')) {
                $stores[] = $store->getId();
            }
        }
        return $stores;
    }

    public function getCmsBlocksByLanguage($language)
    {
        $stores = $this->getStoreIdsByLanguage($language);
        /* @var $blocks Mage_Cms_Model_Resource_Block_Collection */
        $blocks = Mage::getModel('cms/block')->getCollection();
        $blocks->addStoreFilter($stores);
        return $blocks;
    }

    public function getCmsPagesByLanguage($language)
    {
        $stores = $this->getStoreIdsByLanguage($language);
        /* @var $pages Mage_Cms_Model_Resource_Page_Collection */
        $pages = Mage::getModel('cms/page')->getCollection();
        $pages->addStoreFilter($stores);
        return $pages;
    }
}
