<?php

class Capita_TI_Model_Xliff_Import_Block extends Capita_TI_Model_Xliff_Import_Abstract
{

    public function getEntityType()
    {
        return Mage_Cms_Model_Block::CACHE_TAG;
    }

    public function import($id, $sourceLanguage, $destLanguage, $sourceData, $destData)
    {
        if ($this->getRequest()) {
            if (!in_array($id, $this->getRequest()->getBlockIds())) {
                // prevent accidentally importing data which shouldn't be
                // perhaps it wasn't requested or the block was deleted afterwards
                return;
            }
            if (strpos($destLanguage, $this->getRequest()->getDestLanguage()) === false) {
                // was not expecting this language
                return;
            }
        }

        /* @var $origBlock Mage_Cms_Model_Block */
        $origBlock = Mage::getModel('cms/block')->load($id);
        if ($identifier = $origBlock->getIdentifier()) {
            // do not change original block
            // create new block only for targetted stores and retire old one from those stores

            // find all stores which use target language
            $destStores = array();
            /* @var $store Mage_Core_Model_Store */
            foreach (Mage::app()->getStores() as $store) {
                if ($destLanguage == $store->getConfig('general/locale/code')) {
                    $destStores[] = $store->getId();
                }
            }
            $newStores = $destStores;

            /* @var $transaction Mage_Core_Model_Resource_Transaction */
            $transaction = Mage::getResourceModel('core/transaction');

            /* @var $block Mage_Cms_Model_Block */
            /* @var $blocks Mage_Cms_Model_Resource_Block_Collection */
            $blocks = Mage::getResourceModel('cms/block_collection');
            $blocks->addFieldToFilter('identifier', $identifier);
            foreach ($blocks as $block) {
                // lookupStoreIds() is normally called in afterLoad but collection does not do it
                $blockStores = $block->getResource()->lookupStoreIds($block->getId());
                if ($blockStores == array(0)) {
                    // equivalent to "All Store Views"
                    $blockStores = array_keys(Mage::app()->getStores());
                }

                $exStores = array_diff($blockStores, $destStores);
                if ($exStores) {
                    // block cannot be translated without interfering with other locales
                    if ($blockStores != $exStores) {
                        // block must also be removed from targets
                        $block->setStores($exStores);
                        $transaction->addObject($block);
                    }
                    continue;
                }

                $inStores = array_diff($destStores, $blockStores);
                if ($inStores) {
                    // block covers at least one target
                    $block->setTitle(@$destData['title'])
                        ->setContent(@$destData['content']);
                    $transaction->addObject($block);
                }

                $newStores = array_diff($newStores, $inStores);
            }
            if ($newStores) {
                /* @var $newBlock Mage_Cms_Model_Block */
                $newBlock = Mage::getModel('cms/block');
                $newBlock->setIdentifier($identifier)
                    ->setIsActive($origBlock->getIsActive())
                    ->setTitle(@$destData['title'])
                    ->setContent(@$destData['content'])
                    ->setStores($newStores);
                $transaction->addObject($newBlock);
            }
            $transaction->save();
        }
    }
}
