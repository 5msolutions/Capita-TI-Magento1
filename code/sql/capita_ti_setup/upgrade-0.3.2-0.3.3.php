<?php

/* @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

// EAV Blocks

$tableAt = new Varien_Db_Ddl_Table();
$tableAtName = $this->getTable('capita_ti/attribute_diff');
$tableAt->setName($tableAtName);
$tableAt->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false
), 'Entity Attribute ID');
$tableAt->addColumn('language', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
    'nullable' => false,
), 'ISO 639 code');
$tableAt->addColumn('attribute', Varien_Db_Ddl_Table::TYPE_TEXT, 16, array(
    'nullable' => false
), 'Either frontend_label or option_id');
$tableAt->addColumn('old_md5', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
    'nullable' => false
), 'Hash of last known translated value');
$tableAt->addForeignKey(
    $this->getFkName($tableAtName, 'attribute_id', $this->getTable('eav/entity_attribute'), 'attribute_id'),
    'attribute_id',
    $this->getTable('eav/entity_attribute'),
    'attribute_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);
$tableAt->addIndex(
    $this->getIdxName($tableAtName, array('attribute_id', 'attribute', 'language'), Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY),
    array('attribute_id', 'attribute', 'language'),
    array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY));
$tableAt->setComment('EAV attribute labels that have changed since last request');
$tableAt->setOption('type', 'InnoDB');
$tableAt->setOption('charset', 'utf8');
$this->getConnection()->createTable($tableAt);

$this->endSetup();
