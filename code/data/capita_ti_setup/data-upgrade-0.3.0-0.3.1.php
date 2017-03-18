<?php

/* @var $this Mage_Core_Model_Resource_Setup */

// compute possible target locale codes
$languages = Mage::helper('capita_ti')->getNonDefaultLocales();
$languageSelects = array();

foreach ($languages as $language) {
    $languageSelects[] = new Zend_Db_Expr('SELECT '.$this->getConnection()->quote($language).' AS language');
}
$unionSelect = $this->getConnection()->select()->union($languageSelects);

$tables = array(
    'capita_ti/block_diff' => 'block_id',
    'capita_ti/page_diff' => 'page_id',
    'capita_ti/category_diff' => 'entity_id',
    'capita_ti/product_diff' => 'entity_id');

foreach ($tables as $tableAlias => $idField) {
    $tableName = $this->getTable($tableAlias);
    $this->getConnection()->query($this->getConnection()->select()
        ->from($tableName, array($idField, 'attribute', 'old_md5'))
        ->joinCross($unionSelect, 'language')
        ->insertIgnoreFromSelect($tableName, array($idField, 'attribute', 'old_md5', 'language')));
    $this->getConnection()->delete($tableName, 'language = ""');
}
