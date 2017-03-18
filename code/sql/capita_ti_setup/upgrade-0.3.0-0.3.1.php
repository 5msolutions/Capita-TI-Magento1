<?php

/* @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$tables = array(
    'capita_ti/block_diff' => 'block_id',
    'capita_ti/page_diff' => 'page_id',
    'capita_ti/category_diff' => 'entity_id',
    'capita_ti/product_diff' => 'entity_id');

foreach ($tables as $tableAlias => $idField) {
    $tableName = $this->getTable($tableAlias);
    $this->getConnection()->addColumn($tableName, 'language', array(
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => 5,
        'nullable' => false,
        'default' => '',
        'after' => $idField,
        'comment' => 'ISO 639 code'
    ));
    $this->getConnection()->addIndex(
        $tableName,
        Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY,
        array($idField, 'language', 'attribute'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY);
}

$this->endSetup();
