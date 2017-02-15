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

    public function addFilterLikeLanguage($language)
    {
        // addFieldToFilter escapes values for us
        $this->addFieldToFilter(
            'dest_language',
            array('like' => '%'.$language.'%'));
    }
}
