<?php

class Capita_TI_Model_Xliff_Import_Page extends Capita_TI_Model_Xliff_Import_Abstract
{

    public function getEntityType()
    {
        return Mage_Cms_Model_Page::CACHE_TAG;
    }

    public function import($id, $sourceLanguage, $destLanguage, $sourceData, $destData)
    {
        if ($this->getRequest()) {
            if (!in_array($id, $this->getRequest()->getPageIds())) {
                // prevent accidentally importing data which shouldn't be
                // perhaps it wasn't requested or the page was deleted afterwards
                return;
            }
            if (!in_array($destLanguage, $this->getRequest()->getDestLanguage())) {
                // was not expecting this language
                return;
            }
        }

        /* @var $origPage Mage_Cms_Model_Page */
        $origPage = Mage::getModel('cms/page')->load($id);
        if ($identifier = $origPage->getIdentifier()) {
            // do not change original page
            // create new page only for targetted stores and retire old one from those stores

            $destStores = Mage::helper('capita_ti')->getStoreIdsByLanguage($destLanguage);
            $newStores = $destStores;

            /* @var $transaction Mage_Core_Model_Resource_Transaction */
            $transaction = Mage::getResourceModel('core/transaction');

            /* @var $page Mage_Cms_Model_Page */
            /* @var $pages Mage_Cms_Model_Resource_Page_Collection */
            $pages = Mage::getResourceModel('cms/page_collection');
            $pages->addFieldToFilter('identifier', $identifier);
            foreach ($pages as $page) {
                // lookupStoreIds() is normally called in afterLoad but collection does not do it
                $pageStores = $page->getResource()->lookupStoreIds($page->getId());
                if ($pageStores == array(0)) {
                    // equivalent to "All Store Views"
                    $pageStores = array_keys(Mage::app()->getStores());
                }

                $exStores = array_diff($pageStores, $destStores);
                if ($exStores) {
                    // page cannot be translated without interfering with other locales
                    if ($pageStores != $exStores) {
                        // page must also be removed from targets
                        $page->setStores($exStores);
                        $transaction->addObject($page);
                    }
                    continue;
                }

                $inStores = array_diff($destStores, $pageStores);
                if ($inStores) {
                    // page covers at least one target
                    $page->setTitle(@$destData['title'])
                        ->setContent(@$destData['content'])
                        ->setContentHeading(@$destData['content_heading'])
                        ->setMetaDescription(@$destData['meta_description'])
                        ->setMetaKeywords(@$destData['meta_keywords']);
                    $transaction->addObject($page);
                }

                $newStores = array_diff($newStores, $inStores);
            }
            if ($newStores) {
                /* @var $newPage Mage_Cms_Model_Page */
                $newPage = Mage::getModel('cms/page');
                $newPage->setData($origPage->getData())
                    ->unsetData($newPage->getIdFieldName())
                    ->unsetData('creation_time')
                    ->unsetData('update_time')
                    ->setTitle(@$destData['title'])
                    ->setContent(@$destData['content'])
                    ->setContentHeading(@$destData['content_heading'])
                    ->setMetaDescription(@$destData['meta_description'])
                    ->setMetaKeywords(@$destData['meta_keywords'])
                    ->setStores($newStores);
                $transaction->addObject($newPage);
            }
            $transaction->save();
        }
    }
}
