<?php

/* @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$tableRName = $this->getTable('capita_ti/request');
$this->getConnection()->addColumn($tableRName, 'block_count', array(
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'nullable' => false,
    'default' => 0,
    'after' => 'category_count',
    'comment' => 'Number of CMS blocks before translation'
));

$tableB = new Varien_Db_Ddl_Table();
$tableBName = $this->getTable('capita_ti/block');
$tableB->setName($tableBName);
$tableB->addColumn('request_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false
), 'Request entity ID');
$tableB->addColumn('block_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false
), 'Block entity ID');
$tableB->addForeignKey(
    $this->getFkName($tableBName, 'request_id', $tableRName, 'request_id'),
    'request_id',
    $tableRName,
    'request_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);
$tableB->addForeignKey(
    $this->getFkName($tableBName, 'block_id', $this->getTable('cms/block'), 'block_id'),
    'block_id',
    $this->getTable('cms/block'),
    'block_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);
$tableB->setComment('CMS blocks referenced for each request');
$tableB->setOption('type', 'InnoDB');
$tableB->setOption('charset', 'utf8');
$this->getConnection()->createTable($tableB);

$this->endSetup();
