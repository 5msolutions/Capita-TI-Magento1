<?php

/* @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$tableRName = $this->getTable('capita_ti/request');
$this->getConnection()->addColumn($tableRName, 'category_count', array(
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'after' => 'product_count',
    'comment' => 'Number of categories before translation'
));
$this->getConnection()->addColumn($tableRName, 'category_attributes', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 1000,
    'after' => 'product_attributes',
    'comment' => 'Comma separated category attribute codes'
));

$tableC = new Varien_Db_Ddl_Table();
$tableCName = $this->getTable('capita_ti/category');
$tableC->setName($tableCName);
$tableC->addColumn('request_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false
), 'Request entity ID');
$tableC->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false
), 'Category entity ID');
$tableC->addForeignKey(
    $this->getFkName($tableCName, 'request_id', $tableRName, 'request_id'),
    'request_id',
    $tableRName,
    'request_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);
$tableC->addForeignKey(
    $this->getFkName($tableCName, 'category_id', $this->getTable('catalog/category'), 'entity_id'),
    'category_id',
    $this->getTable('catalog/category'),
    'entity_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);
$tableC->setComment('Categories referenced for each request');
$tableC->setOption('type', 'InnoDB');
$tableC->setOption('charset', 'utf8');
$this->getConnection()->createTable($tableC);

$this->endSetup();
