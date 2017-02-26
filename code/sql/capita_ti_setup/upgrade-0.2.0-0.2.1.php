<?php

/* @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$tableRName = $this->getTable('capita_ti/request');
$this->getConnection()->addColumn($tableRName, 'page_count', array(
    'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'nullable' => false,
    'default' => 0,
    'after' => 'block_count',
    'comment' => 'Number of CMS pages before translation'
));

$tableP = new Varien_Db_Ddl_Table();
$tablePName = $this->getTable('capita_ti/page');
$tableP->setName($tablePName);
$tableP->addColumn('request_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false
), 'Request entity ID');
$tableP->addColumn('page_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false
), 'Page entity ID');
$tableP->addForeignKey(
    $this->getFkName($tablePName, 'request_id', $tableRName, 'request_id'),
    'request_id',
    $tableRName,
    'request_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);
$tableP->addForeignKey(
    $this->getFkName($tablePName, 'page_id', $this->getTable('cms/page'), 'page_id'),
    'page_id',
    $this->getTable('cms/page'),
    'page_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);
$tableP->setComment('CMS pages referenced for each request');
$tableP->setOption('type', 'InnoDB');
$tableP->setOption('charset', 'utf8');
$this->getConnection()->createTable($tableP);

$this->endSetup();
