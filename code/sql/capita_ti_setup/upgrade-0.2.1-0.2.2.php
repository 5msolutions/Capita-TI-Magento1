<?php

/* @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

$tableRName = $this->getTable('capita_ti/request');
$this->getConnection()->addIndex(
    $tableRName,
    $this->getIdxName($tableRName, array('status')),
    array('status'));
$this->getConnection()->addIndex(
    $tableRName,
    $this->getIdxName($tableRName, array('created_at')),
    array('created_at'));

$this->endSetup();
