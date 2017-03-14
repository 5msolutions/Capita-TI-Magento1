<?php

class Capita_TI_Model_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{

    protected function _initSelect()
    {
        parent::_initSelect();
        $entityTable = $this->getEntity()->getEntityTable();
        $configTable = $this->getTable('core/config_data');

        // each subquery maps entity IDs to locale codes
        // TODO select media_gallery and media_gallery_value
        $textSelect = $this->getConnection()->select()
            ->distinct()
            ->from($entityTable.'_text', 'entity_id')
            ->join($configTable, '(scope_id=store_id) AND (path="general/locale/code")', 'value')
            ->where('store_id > 0');
        $varcharSelect = $this->getConnection()->select()
            ->distinct()
            ->from($entityTable.'_varchar', 'entity_id')
            ->join($configTable, '(scope_id=store_id) AND (path="general/locale/code")', 'value')
            ->where(('store_id > 0'));
        // subqueries have the same columns so can be unioned
        // UNION ALL is fastest option
        $unionSelect = $this->getConnection()->select()
            ->union(array($textSelect, $varcharSelect), Zend_Db_Select::SQL_UNION_ALL);
        // too many subqueries?.. nah
        $groupSelect = $this->getConnection()->select()
            ->from($unionSelect, array('entity_id', 'translated' => 'GROUP_CONCAT(DISTINCT value)'))
            ->group('entity_id');

        $this->getSelect()->joinLeft(
            array('locales' => $groupSelect),
            '(e.entity_id = locales.entity_id)',
            array('translated'));
        $this->_joinFields['translated'] = array(
            'table' => 'locales',
            'field' => 'translated'
        );
        return $this;
    }
}
