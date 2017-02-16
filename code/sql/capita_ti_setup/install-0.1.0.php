<?php

/* @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$tableR = new Varien_Db_Ddl_Table();
$tableRName = $this->getTable('capita_ti/request');
$tableR->setName($tableRName);
$tableR->addColumn('request_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'primary' => true,
    'identity' => true
), 'Request object ID');
$tableR->addColumn('source_language', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
    'nullable' => false,
    'default' => 'en_US'
), 'ISO 639 compatible language code');
$tableR->addColumn('dest_language', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    'nullable' => false
), 'Comma separated ISO 639 langauge codes');
$tableR->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
    'nullable' => false
), 'One of completed/onHold/inProgress/uploading/downloading');
$tableR->addColumn('product_count', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
    array(), 'Number of products before translation');
$tableR->addColumn('product_attributes', Varien_Db_Ddl_Table::TYPE_TEXT, 1000,
    array(), 'Comma separated product attribute codes');
$tableR->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    'default' => Varien_Db_Ddl_Table::TIMESTAMP_INIT
), 'Creation Time');
$tableR->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    'default' => Varien_Db_Ddl_Table::TIMESTAMP_UPDATE
), 'Update Time');
$tableR->setComment('Track remote Capita requests');
$tableR->setOption('type', 'InnoDB');
$tableR->setOption('charset', 'utf8');
$this->getConnection()->createTable($tableR);

$tableP = new Varien_Db_Ddl_Table();
$tablePName = $this->getTable('capita_ti/product');
$tableP->setName($tablePName);
$tableP->addColumn('request_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false
), 'Request entity ID');
$tableP->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false
), 'Product entity ID');
$tableP->addForeignKey(
    $this->getFkName($tablePName, 'request_id', $tableRName, 'request_id'),
    'request_id',
    $tableRName,
    'request_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);
$tableP->addForeignKey(
    $this->getFkName($tablePName, 'product_id', $this->getTable('catalog/product'), 'entity_id'),
    'product_id',
    $this->getTable('catalog/product'),
    'entity_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);
$tableP->setComment('Products referenced for each request');
$tableP->setOption('type', 'InnoDB');
$tableP->setOption('charset', 'utf8');
$this->getConnection()->createTable($tableP);

$tableA = new Varien_Db_Ddl_Table();
$tableAName = $this->getTable('capita_ti/attribute');
$tableA->setName($tableAName);
$tableA->addColumn('request_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false
), 'Request entity ID');
$tableA->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 5, array(
    'unsigned' => true,
    'nullable' => false
), 'Entity attribute ID');
$tableA->addForeignKey(
    $this->getFkName($tableAName, 'request_id', $tableRName, 'request_id'),
    'request_id',
    $tableRName,
    'request_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);
$tableA->addForeignKey(
    $this->getFkName($tableAName, 'attribute_id', $this->getTable('eav/attribute'), 'attribute_id'),
    'attribute_id',
    $this->getTable('eav/attribute'),
    'attribute_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);
$tableA->setComment('Attributes used in each request');
$tableA->setOption('type', 'InnoDB');
$tableA->setOption('charset', 'utf8');
$this->getConnection()->createTable($tableA);

$this->endSetup();
