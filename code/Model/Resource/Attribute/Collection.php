<?php

class Capita_TI_Model_Resource_Attribute_Collection extends Mage_Catalog_Model_Resource_Product_Attribute_Collection
{

    protected function _initSelect()
    {
        parent::_initSelect();
        $labelTable = $this->getTable('eav/attribute_label');
        $configTable = $this->getTable('core/config_data');

        $labelSelect = $this->getConnection()->select()
            ->distinct()
            ->from(array('labels' => $labelTable), 'attribute_id')
            ->join(
                array('config' => $configTable),
                '(scope_id=store_id) AND (path="general/locale/code")',
                array('translated' => 'GROUP_CONCAT(DISTINCT config.value)'))
            ->where('store_id > 0');

        $this->getSelect()->joinLeft(
            array('labels' => $labelSelect),
            '(main_table.attribute_id = labels.attribute_id)',
            array('translated'));
        $this->_joinFields['translated'] = array(
            'table' => 'labels',
            'field' => 'translated'
        );
        return $this;
    }
}
