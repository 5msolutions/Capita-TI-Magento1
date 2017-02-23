<?php

class Capita_TI_Model_Resource_Request_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('capita_ti/request');
    }

    protected function _afterLoad()
    {
        if ($this->count()) {
            /* @var $documents Capita_TI_Model_Resource_Request_Document_Collection */
            $documents = Mage::getResourceModel('capita_ti/request_document_collection');
            $documents->addFieldToFilter('request_id', array('in' => array_keys($this->_items)));

            $adapter = $this->getConnection();
            $productIds = $adapter->select()
                ->from($this->getTable('capita_ti/product'), 'product_id')
                ->where('request_id=:request_id');
            
            $categoryIds = $adapter->select()
                ->from($this->getTable('capita_ti/category'), 'category_id')
                ->where('request_id=:request_id');

            foreach ($this as $request) {
                if ($request->hasDestLanguage()) {
                    $request->setDestLanguage(
                        explode(',', $request->getDestLanguage())
                    );
                }

                $bind = array(':request_id' => $request->getId());
                $request
                    ->setDocuments($documents->getItemsByColumnValue(
                        'request_id',
                        $request->getId()))
                    ->setProductIds($adapter->fetchCol($productIds, $bind))
                    ->setCategoryIds($adapter->fetchCol($categoryIds, $bind));
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
     * Restrict to records with a status != 'completed'
     * 
     * @return Capita_TI_Model_Resource_Request_Collection
     */
    public function addIncompleteFilter()
    {
        $this->addFieldToFilter('status', array('neq' => 'completed'));
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
}
