<?php

class Capita_TI_Model_Resource_Block_Collection extends Mage_Cms_Model_Resource_Block_Collection
{

    protected function _initSelect()
    {
        parent::_initSelect();
        $storeTable = $this->getTable('cms/block_store');
        $configTable = $this->getTable('core/config_data');
        $diffTable = $this->getTable('capita_ti/block_diff');

        $diffSelect = $this->getConnection()->select()
            ->from(array('blocks' => $this->getMainTable()), 'identifier')
            ->join(array('diff' => $diffTable), 'blocks.block_id=diff.block_id', array('language', 'changes' => 'old_md5'))
            ->group('blocks.identifier');
        $groupSelect = $this->getConnection()->select()
            ->from(array('blocks' => $this->getMainTable()), 'identifier')
            ->join(array('stores' => $storeTable), 'blocks.block_id = stores.block_id', '')
            ->join(
                array('config' => $configTable),
                '(config.scope_id=stores.store_id) AND (config.path="general/locale/code")',
                array('translated' => 'GROUP_CONCAT(DISTINCT config.value)'))
            ->joinLeft(
                array('diff' => $diffSelect),
                '(blocks.identifier = diff.identifier) AND (config.value = diff.language)',
                'changes')
            ->group('blocks.identifier')
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
}
