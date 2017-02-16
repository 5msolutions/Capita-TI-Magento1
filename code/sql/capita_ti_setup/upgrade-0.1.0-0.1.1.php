<?php

/* @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$tableRName = $this->getTable('capita_ti/request');
$this->getConnection()->addColumn($tableRName, 'remote_id', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 16,
    'comment' => 'ID provided by remote API'
));
$this->getConnection()->addColumn($tableRName, 'remote_no', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 16,
    'comment' => '# provided by remote API'
));


$tableD = new Varien_Db_Ddl_Table();
$tableDName = $this->getTable('capita_ti/document');
$tableD->setName($tableDName);
$tableD->addColumn('document_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'primary' => true,
    'identity' => true
), 'Internal reference ID');
$tableD->addColumn('request_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false
), 'Request entity ID');
$tableD->addColumn('remote_id', Varien_Db_Ddl_Table::TYPE_TEXT, 16, array(
), 'ID provided by remote API');
$tableD->addColumn('remote_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
), 'Filename in API');
$tableD->addColumn('local_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
), 'Filename relative to var directory');
$tableD->addColumn('language', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
), 'ISO 639 code');
$tableD->addForeignKey(
    $this->getFkName($tableDName, 'request_id', $tableRName, 'request_id'),
    'request_id',
    $tableRName,
    'request_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);
$tableD->setComment('Products referenced for each request');
$tableD->setOption('type', 'InnoDB');
$tableD->setOption('charset', 'utf8');
$this->getConnection()->createTable($tableD);

$this->endSetup();
