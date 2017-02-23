<?php

class Capita_TI_Model_Resource_Request_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('capita_ti/request');
    }

    protected function _afterLoad()
    {
        foreach ($this as $request) {
            if ($request->hasDestLanguage()) {
                $request->setDestLanguage(
                    explode(',', $request->getDestLanguage())
                );
            }
        }
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
}
