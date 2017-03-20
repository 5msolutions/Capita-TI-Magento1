<?php

class Capita_TI_Model_Resource_Attribute_Collection extends Mage_Catalog_Model_Resource_Product_Attribute_Collection
{

    protected function _initSelect()
    {
        parent::_initSelect();
        $labelTable = $this->getTable('eav/attribute_label');
        $configTable = $this->getTable('core/config_data');
        $diffTable = $this->getTable('capita_ti/attribute_diff');

        $langSelect = $this->getConnection()->select()
            ->from(array('labels' => $labelTable), 'attribute_id')
            ->join(
                array('config' => $configTable),
                '(scope_id=store_id) AND (path="general/locale/code")',
                array('language' => 'value'))
            ->joinLeft(
                array('diff' => $diffTable),
                '(diff.attribute_id=labels.attribute_id) AND (diff.language=config.value)',
                array())
            ->where('diff.old_md5 IS NULL')
            ->group(array('labels.attribute_id', 'config.value'));
        $labelSelect = $this->getConnection()->select()
            ->from(array('labels' => $labelTable), 'attribute_id')
            ->joinLeft(
                array('langs' => $langSelect),
                '(labels.attribute_id=langs.attribute_id)',
                array('translated' => 'GROUP_CONCAT(DISTINCT language)'))
            ->where('store_id > 0')
            ->group('labels.attribute_id');

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
