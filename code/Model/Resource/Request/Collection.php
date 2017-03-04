<?php

class Capita_TI_Model_Resource_Request_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('capita_ti/request');
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinLeft(
                array('products' => $this->getTable('capita_ti/product')),
                'main_table.request_id=products.request_id',
                array('product_ids' => 'GROUP_CONCAT(DISTINCT product_id)'))
            ->joinLeft(
                array('categorys' => $this->getTable('capita_ti/category')),
                'main_table.request_id=categorys.request_id',
                array('category_ids' => 'GROUP_CONCAT(DISTINCT category_id)'))
            ->joinLeft(
                array('blocks' => $this->getTable('capita_ti/block')),
                'main_table.request_id=blocks.request_id',
                array('block_ids' => 'GROUP_CONCAT(DISTINCT block_id)'))
            ->joinLeft(
                array('pages' => $this->getTable('capita_ti/page')),
                'main_table.request_id=pages.request_id',
                array('page_ids' => 'GROUP_CONCAT(DISTINCT page_id)'))
            ->group('main_table.request_id');
        return $this;
    }

    public function getSelectCountSql()
    {
        // undo effects of _initSelect() above
        $select = parent::getSelectCountSql();
        $select->reset(Zend_Db_Select::GROUP);
        $select->resetJoinLeft();
        return $select;
    }

    protected function _afterLoad()
    {
        if ($this->count()) {
            /* @var $documents Capita_TI_Model_Resource_Request_Document_Collection */
            $documents = Mage::getResourceModel('capita_ti/request_document_collection');
            $documents->addFieldToFilter('request_id', array('in' => array_keys($this->_items)));

            foreach ($this as $request) {
                if ($request->hasDestLanguage()) {
                    $request->setDestLanguage(
                        explode(',', $request->getDestLanguage())
                    );
                }

                $reqdocs = array();
                foreach ($documents as $document) {
                    if ($document->getRequestId() == $request->getId()) {
                        $reqdocs[$document->getId()] = $document;
                    }
                }
                $request->setDocuments($reqdocs);

                $request->setProductIds(array_filter(explode(',', $request->getProductIds())));
                $request->setCategoryIds(array_filter(explode(',', $request->getCategoryIds())));
                $request->setBlockIds(array_filter(explode(',', $request->getBlockIds())));
                $request->setPageIds(array_filter(explode(',', $request->getPageIds())));
            }
        }

        return parent::_afterLoad();
    }

    /**
     * Allows convenient searching of comma separated lists
     * 
     * Because destination language is one text field it can be searched with
     * a simple LIKE clause.
     * Used by adminhtml grid with a select control so input is limited.
     * Input is still escaped properly.
     * 
     * @param string $language
     * @return Capita_TI_Model_Resource_Request_Collection
     */
    public function addFilterLikeLanguage($language)
    {
        // addFieldToFilter escapes values for us
        $this->addFieldToFilter(
            'dest_language',
            array('like' => '%'.$language.'%'));
        return $this;
    }

    /**
     * Restrict to records with a status indicating a remote job
     * 
     * @return Capita_TI_Model_Resource_Request_Collection
     */
    public function addRemoteFilter()
    {
        $this->addFieldToFilter('status', array('in' => array('onHold', 'inProgress')));
        return $this;
    }

    /**
     * Restrict to records with a status == 'importing'
     * 
     * @return Capita_TI_Model_Resource_Request_Collection
     */
    public function addImportingFilter()
    {
        $this->addFieldToFilter('status', 'importing');
        return $this;
    }

    public function addProductFilter($productId)
    {
        if ($productId instanceof Varien_Object) {
            $productId = $productId->getId();
        }
        $this->addFieldToFilter('products.product_id', $productId);
        return $this;
    }
}
