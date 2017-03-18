<?php

class Capita_TI_Model_Resource_Page_Collection extends Mage_Cms_Model_Resource_Page_Collection
{

    protected function _initSelect()
    {
        parent::_initSelect();
        $storeTable = $this->getTable('cms/page_store');
        $configTable = $this->getTable('core/config_data');
        $diffTable = $this->getTable('capita_ti/page_diff');

        $diffSelect = $this->getConnection()->select()
            ->from(array('pages' => $this->getMainTable()), 'identifier')
            ->join(array('diff' => $diffTable), 'pages.page_id=diff.page_id', array('language', 'changes' => 'old_md5'))
            ->group('pages.identifier');
        $groupSelect = $this->getConnection()->select()
            ->from(array('pages' => $this->getMainTable()), 'identifier')
            ->join(array('stores' => $storeTable), 'pages.page_id = stores.page_id', '')
            ->join(
                array('config' => $configTable),
                '(config.scope_id=stores.store_id) AND (config.path="general/locale/code")',
                array('translated' => 'GROUP_CONCAT(DISTINCT config.value)'))
            ->joinLeft(
                array('diff' => $diffSelect),
                '(pages.identifier = diff.identifier) AND (config.value = diff.language)',
                'changes')
            ->group('pages.identifier')
            ->where('config.value != ?', Mage::getStoreConfig('general/locale/code'))
            ->where('diff.changes IS NULL');

        $this->getSelect()->joinLeft(
            array('locales' => $groupSelect),
            '(main_table.identifier = locales.identifier)',
            array('translated'));
        $this->_joinFields['translated'] = array(
            'table' => 'locales',
            'field' => 'translated'
        );
        return $this;
    }

    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();

        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->columns('COUNT(DISTINCT main_table.page_id)');

        return $countSelect;
    }
}
