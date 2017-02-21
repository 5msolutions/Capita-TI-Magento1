<?php

/* @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$tableDName = $this->getTable('capita_ti/document');
$this->getConnection()->addColumn($tableDName, 'status', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length' => 20,
    'nullable' => false,
    'default' => '',
    'comment' => 'One of completed/onHold/inProgress/importing'
));

$this->endSetup();
