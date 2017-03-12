<?php

/* @var $this Mage_Core_Model_Resource_Setup */

$this->startSetup();

// CMS Blocks

$tableBl = new Varien_Db_Ddl_Table();
$tableBlName = $this->getTable('capita_ti/block_diff');
$tableBl->setName($tableBlName);
$tableBl->addColumn('block_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false
), 'Block entity ID');
$tableBl->addColumn('attribute', Varien_Db_Ddl_Table::TYPE_TEXT, 8, array(
    'nullable' => false
), 'Either title or content');
$tableBl->addColumn('old_md5', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
    'nullable' => false
), 'Hash of last known translated value');
$tableBl->addForeignKey(
    $this->getFkName($tableBlName, 'block_id', $this->getTable('cms/block'), 'block_id'),
    'block_id',
    $this->getTable('cms/block'),
    'block_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);
$tableBl->addIndex(
    $this->getIdxName($tableBlName, array('block_id', 'attribute'), Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY),
    array('block_id', 'attribute'),
    array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY));
$tableBl->setComment('CMS blocks that have changed since last request');
$tableBl->setOption('type', 'InnoDB');
$tableBl->setOption('charset', 'utf8');
$this->getConnection()->createTable($tableBl);

// CMS Pages

$tablePa = new Varien_Db_Ddl_Table();
$tablePaName = $this->getTable('capita_ti/page_diff');
$tablePa->setName($tablePaName);
$tablePa->addColumn('page_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false
), 'Page entity ID');
$tablePa->addColumn('attribute', Varien_Db_Ddl_Table::TYPE_TEXT, 16, array(
    'nullable' => false
), 'Title, content or meta fields');
$tablePa->addColumn('old_md5', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
    'nullable' => false
), 'Hash of last known translated value');
$tablePa->addForeignKey(
    $this->getFkName($tablePaName, 'page_id', $this->getTable('cms/page'), 'page_id'),
    'page_id',
    $this->getTable('cms/page'),
    'page_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);
$tablePa->addIndex(
    $this->getIdxName($tablePaName, array('page_id', 'attribute'), Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY),
    array('page_id', 'attribute'),
    array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY));
$tablePa->setComment('CMS pages that have changed since last request');
$tablePa->setOption('type', 'InnoDB');
$tablePa->setOption('charset', 'utf8');
$this->getConnection()->createTable($tablePa);

// Catalog Categories

$tableCa = new Varien_Db_Ddl_Table();
$tableCaName = $this->getTable('capita_ti/category_diff');
$tableCa->setName($tableCaName);
$tableCa->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false
), 'Category entity ID');
$tableCa->addColumn('attribute', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
    'nullable' => false
), 'Attribute code not ID');
$tableCa->addColumn('old_md5', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
    'nullable' => false
), 'Hash of last known translated value');
$tableCa->addForeignKey(
    $this->getFkName($tableCaName, 'entity_id', $this->getTable('catalog/category'), 'entity_id'),
    'entity_id',
    $this->getTable('catalog/category'),
    'entity_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);
$tableCa->addIndex(
    $this->getIdxName($tableCaName, array('entity_id', 'attribute'), Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY),
    array('entity_id', 'attribute'),
    array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY));
$tableCa->setComment('Catalog categories that have changed since last request');
$tableCa->setOption('type', 'InnoDB');
$tableCa->setOption('charset', 'utf8');
$this->getConnection()->createTable($tableCa);

// Catalog Products

$tablePr = new Varien_Db_Ddl_Table();
$tablePrName = $this->getTable('capita_ti/product_diff');
$tablePr->setName($tablePrName);
$tablePr->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
    'unsigned' => true,
    'nullable' => false
), 'Product entity ID');
$tablePr->addColumn('attribute', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
    'nullable' => false
), 'Attribute code not ID');
$tablePr->addColumn('old_md5', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
    'nullable' => false
), 'Hash of last known translated value');
$tablePr->addForeignKey(
    $this->getFkName($tablePrName, 'entity_id', $this->getTable('catalog/product'), 'entity_id'),
    'entity_id',
    $this->getTable('catalog/product'),
    'entity_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE);
$tablePr->addIndex(
    $this->getIdxName($tablePrName, array('entity_id', 'attribute'), Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY),
    array('entity_id', 'attribute'),
    array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_PRIMARY));
$tablePr->setComment('Catalog products that have changed since last request');
$tablePr->setOption('type', 'InnoDB');
$tablePr->setOption('charset', 'utf8');
$this->getConnection()->createTable($tablePr);

$this->endSetup();
