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

    /**
     * Assign locale codes to CMS block objects
     * 
     * Blocks with global scope have ambiguous languages so nothing is added
     * 
     * @return Mage_Cms_Model_Resource_Block_Collection
     */
    public function getCmsBlocksWithLanguages()
    {
        $blocks = Mage::getModel('cms/block')->getCollection();
        /* @var $block Mage_Cms_Model_Block */
        foreach ($blocks as $id => $block) {
            $stores = $block->getResource()->lookupStoreIds($id);
            if (!$stores || ($stores == array(0))) continue;

            $languages = array();
            foreach ($stores as $store) {
                $languages[] = Mage::getStoreConfig('general/locale/code', $store);
            }
            $block->setLanguages(array_unique($languages));
        }
        return $blocks;
    }

    /**
     * Assign locale codes to CMS page objects
     * 
     * Pages with global scope have ambiguous languages so nothing is added
     * 
     * @return Mage_Cms_Model_Resource_Page_Collection
     */
    public function getCmsPagesWithLanguages()
    {
        $pages = Mage::getModel('cms/page')->getCollection();
        /* @var $page Mage_Cms_Model_Page */
        foreach ($pages as $id => $page) {
            $stores = $page->getResource()->lookupStoreIds($id);
            if (!$stores || ($stores == array(0))) continue;

            $languages = array();
            foreach ($stores as $store) {
                $languages[] = Mage::getStoreConfig('general/locale/code', $store);
            }
            $page->setLanguages(array_unique($languages));
        }
        return $pages;
    }
}
