<?php

class Capita_TI_Model_Tracker
{

    /**
     * Direct access to DB adapter
     * 
     * @return Mage_Core_Model_Resource
     */
    protected function getResource()
    {
        return Mage::getSingleton('core/resource');
    }

    /**
     * Direct access to DB adapter
     * 
     * @return Varien_Db_Adapter_Interface
     */
    protected function getConnection()
    {
        return $this->getResource()->getConnection('capita_ti_setup');
    }

    /**
     * A safe form of INSERT IGNORE with multiple rows
     * 
     * @param string $tableEntity
     * @param array $data 2D array of column => values
     */
    protected function insertRetire($tableEntity, $data)
    {
        if (!$data) {
            return;
        }

        $tableName = $this->getResource()->getTableName($tableEntity);
        $top = reset($data);
        unset($top['old_value'], $top['new_value']);
        $top['old_md5'] = true;
        $cols = array_keys($top);

        $inserts = array();
        $deletes = array();
        foreach ($data as $row) {
            $oldValue = @$row['old_value'];
            $newValue = @$row['new_value'];
            $row = array_intersect_key($row, $top);
            $row = array_merge($top, $row);

            $row['old_md5'] = md5($oldValue);
            $inserts[] = '('.implode(',', array_map(array($this->getConnection(), 'quote'), $row)).')';

            $row['old_md5'] = md5($newValue);
            $delete = array();
            foreach ($row as $col => $val) {
                $col = $this->getConnection()->quoteIdentifier($col);
                $val = $this->getConnection()->quote($val);
                $delete[] = '('.$col.'='.$val.')';
            }
            $deletes[] = '('.implode(' AND ', $delete).')';
        }

        $cols = array_map(array($this->getConnection(), 'quoteIdentifier'), $cols);
        $id = reset($cols);
        // actual INSERT IGNORE is dangerous because it ignores all errors
        // ON DUPLICATE KEY in this way is better because it only ignores key collisions
        $sql = 'INSERT INTO '.$this->getConnection()->quoteIdentifier($tableName, true);
        $sql .= ' ('.implode(',', $cols).') VALUES ';
        $sql .= implode(',', $inserts);
        $sql .= ' ON DUPLICATE KEY UPDATE '.$id.'='.$id;
        $this->getConnection()->query($sql);

        $where = implode(' OR ', $deletes);
        $this->getConnection()->delete($tableName, $where);
    }

    protected function deleteRecords($tableEntity, $condition)
    {
        $connection = $this->getConnection();
        $tableName = $this->getResource()->getTableName($tableEntity);
        $where = array();
        foreach ($condition as $col => $val) {
            $col = $connection->quoteIdentifier($col);
            if (is_array($val)) {
                $where[] = sprintf('%s IN (%s)', $col, $connection->quote($val));
            }
            else {
                $where[] = sprintf('%s LIKE %s', $col, $connection->quote($val));
            }
        }
        if ($where) {
            $where = implode(Zend_Db_Select::SQL_AND, $where);
            return $connection->delete($tableName, $where);
        }
        return false;
    }

    protected function watchEntity($tableEntity, Varien_Object $object, $attributes)
    {
        $values = array();
        $languages = Mage::helper('capita_ti')->getNonDefaultLocales();
        foreach ($attributes as $attribute) {
            if ($object->dataHasChangedFor($attribute)) {
                foreach ($languages as $language) {
                    $values[] = array(
                        $object->getIdFieldName() => $object->getId(),
                        'language' => $language,
                        'attribute' => $attribute,
                        'old_value' => $object->getOrigData($attribute),
                        'new_value' => $object->getData($attribute)
                    );
                }
            }
        }
        $this->insertRetire($tableEntity, $values);
    }

    public function blockSaveAfter(Varien_Event_Observer $observer)
    {
        /* @var $block Mage_Cms_Model_Block */
        $block = $observer->getObject();
        $stores = array(
            Mage_Core_Model_App::ADMIN_STORE_ID,
            Mage::app()->getDefaultStoreView()->getId()
        );
        if ($block && array_intersect($block->getStores(), $stores)) {
            $this->watchEntity(
                'capita_ti/block_diff',
                $block,
                array('title', 'content'));
            return $this;
        }
    }

    public function categorySaveAfter(Varien_Event_Observer $observer)
    {
        $category = $observer->getCategory();
        if ($category && !$category->getStoreId()) {
            $attributes = Mage::getSingleton('capita_ti/source_category_attributes')->getBestAttributes();
            $this->watchEntity(
                'capita_ti/category_diff',
                $observer->getCategory(),
                $attributes->getColumnValues('attribute_code'));
        }
    }

    public function pageSaveAfter(Varien_Event_Observer $observer)
    {
        /* @var $page Mage_Cms_Model_Page */
        $page = $observer->getObject();
        $stores = array(
            Mage_Core_Model_App::ADMIN_STORE_ID,
            Mage::app()->getDefaultStoreView()->getId()
        );
        if ($page && array_intersect($page->getStores(), $stores)) {
            $this->watchEntity(
                'capita_ti/page_diff',
                $observer->getObject(),
                array('title', 'meta_keywords', 'meta_description', 'content_heading', 'content'));
        }
    }

    public function productSaveAfter(Varien_Event_Observer $observer)
    {
        $product = $observer->getProduct();
        // only changes in global scope
        if ($product && !$product->getStoreId()) {
            $attributes = Mage::getSingleton('capita_ti/source_product_attributes')->getBestAttributes();
            $this->watchEntity(
                'capita_ti/product_diff',
                $product,
                $attributes->getColumnValues('attribute_code'));
        }
    }

    public function modelSaveAfter(Varien_Event_Observer $observer)
    {
        // CMS Blocks do not set an event_prefix so cannot dispatch a specific event
        // test if generic event contains a block and pass to appropriate handler
        if ($observer->getObject() instanceof Mage_Cms_Model_Block) {
            $this->blockSaveAfter($observer);
        }
    }

    public function endWatch(Capita_TI_Model_Request $request)
    {
        $languages = explode(',', $request->getDestLanguage());
        if ($request->getProductIds() && $request->getProductAttributes()) {
            $this->deleteRecords(
                'capita_ti/product_diff',
                array(
                    'entity_id' => $request->getProductIds(),
                    'language'  => $languages,
                    'attribute' => $request->getProductAttributesArray()
                ));
        }
        if ($request->getCategoryIds() && $request->getCategoryAttributes()) {
            $this->deleteRecords(
                'capita_ti/category_diff',
                array(
                    'entity_id' => $request->getCategoryIds(),
                    'language'  => $languages,
                    'attribute' => $request->getCategoryAttributesArray()
                ));
        }
        if ($request->getBlockIds()) {
            $this->deleteRecords(
                'capita_ti/block_diff',
                array(
                    'block_id' => $request->getBlockIds(),
                    'language'  => $languages
                ));
        }
        if ($request->getPageIds()) {
            $this->deleteRecords(
                'capita_ti/page_diff',
                array(
                    'page_id' => $request->getPageIds(),
                    'language'  => $languages
                ));
        }
        return $this;
    }
}
