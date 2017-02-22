<?php

class Capita_TI_Model_Resource_Request extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('capita_ti/request', 'request_id');
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $request)
    {
        /* @var $documents Capita_TI_Model_Resource_Request_Document_Collection */
        $documents = Mage::getResourceModel('capita_ti/request_document_collection');
        $documents->addFieldToFilter($request->getIdFieldName(), $request->getId());
        $request->setDocuments($documents->getItems());

        // product IDs don't have their own model class
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select();
        $select->from($this->getTable('capita_ti/product'), 'product_id')
            ->where('request_id=?', $request->getId());
        $request->setProductIds($adapter->fetchCol($select));

        // do the same for categories
        $select = $adapter->select();
        $select->from($this->getTable('capita_ti/category'), 'category_id')
            ->where('request_id=?', $request->getId());
        $request->setCategoryIds($adapter->fetchCol($select));
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $request)
    {
        if (is_array($request->getDestLanguage())) {
            $request->setDestLanguage(
                implode(',', $request->getDestLanguage())
            );
        }

        $request->setProductCount(
            is_string($request->getProductIds()) ?
            substr_count($request->getProductIds(), ',') + 1 :
            count($request->getProductIds())
        );

        $request->setCategoryCount(
            is_string($request->getCategoryIds()) ?
            substr_count($request->getCategoryIds(), ',') + 1 :
            count($request->getCategoryIds())
        );

        $request->setUpdatedAt($this->formatDate(true));

        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $request)
    {
        if ($request->dataHasChangedFor('documents')) {
            $this->_saveDocuments($request);
        }

        if ($request->dataHasChangedFor('product_attributes') || $request->dataHasChangedFor('category_attributes')) {
            $this->_saveAttributes($request);
        }

        if ($request->dataHasChangedFor('product_ids')) {
            $this->_saveProducts($request);
        }

        if ($request->dataHasChangedFor('category_ids')) {
            $this->_saveCategories($request);
        }
    }

    protected function _afterDelete(Mage_Core_Model_Abstract $request)
    {
        foreach ($request->getDocuments() as $document) {
            if (is_array($document)) {
                $document = Mage::getModel('capita_ti/request_document')
                    ->setData($document);
            }
            $document->delete();
        }
    }

    protected function _saveDocuments(Capita_TI_Model_Request $request)
    {
        $documents = $request->getDocuments();
        /* @var $document Capita_TI_Model_Request_Document */
        foreach ($documents as &$document) {
            if (is_array($document)) {
                $document = Mage::getModel('capita_ti/request_document')
                    ->setData($document)
                    ->setRequestId($request->getId())
                    ->setStatus($request->getStatus());
            }
            $document->save();
        }
        $request->setData('documents', $documents)
            ->setOrigData('documents', $documents);
    }

    protected function _saveAttributes(Capita_TI_Model_Request $request)
    {
        $attrTable = $this->getTable('capita_ti/attribute');
        $productAttributes = $request->getProductAttributesArray();
        $categoryAttributes = $request->getCategoryAttributesArray();
        /* @var $eavConfig Mage_Eav_Model_Config */
        $eavConfig = Mage::getSingleton('eav/config');
        $productTypeId = $eavConfig->getEntityType(Mage_Catalog_Model_Product::ENTITY)->getEntityTypeId();
        $categoryTypeId = $eavConfig->getEntityType(Mage_Catalog_Model_Category::ENTITY)->getEntityTypeId();

        /* @var $attributes Mage_Eav_Model_Resource_Entity_Attribute_Collection */
        $attributes = Mage::getResourceModel('eav/entity_attribute_collection');
        $attributes->getSelect()
            ->where("(entity_type_id={$productTypeId}) AND (attribute_code IN (?))", $productAttributes)
            ->orWhere("(entity_type_id={$categoryTypeId}) AND (attribute_code IN (?))", $categoryAttributes);
        $attrIds = $attributes->getColumnValues('attribute_id');

        $adapter = $this->_getWriteAdapter();
        // delete rows no longer in collection
        $condition = sprintf(
            '(%s) AND (%s)',
            $adapter->prepareSqlCondition('request_id', $request->getId()),
            $adapter->prepareSqlCondition('attribute_id', array('nin' => $attrIds)));
        $adapter->delete($attrTable, $condition);

        $insertData = array();
        foreach ($attrIds as $attrId) {
            $insertData[] = array(
                'request_id' => $request->getId(),
                'attribute_id' => $attrId
            );
        }
        $adapter->insertOnDuplicate($attrTable, $insertData);

        // mark field as unchanged
        $request->setOrigData('product_attributes', $request->getProductAttributes());
        $request->setOrigData('category_attributes', $request->getCategoryAttributes());
    }

    protected function _saveProducts(Capita_TI_Model_Request $request)
    {
        $productTable = $this->getTable('capita_ti/product');
        $productIds = $request->getProductIds();
        if (!is_array($productIds)) {
            $productIds = explode(',', (string) $productIds);
        }

        $adapter = $this->_getWriteAdapter();
        $condition = sprintf(
            '(%s) AND (%s)',
            $adapter->prepareSqlCondition('request_id', $request->getId()),
            $adapter->prepareSqlCondition('product_id', array('nin' => $productIds)));
        $adapter->delete($productTable, $condition);

        $insertData = array();
        foreach ($productIds as $productId) {
            $insertData[] = array(
                'request_id' => $request->getId(),
                'product_id' => $productId
            );
        }
        $adapter->insertOnDuplicate($productTable, $insertData);
        $request->setOrigData('product_ids', $productIds);
    }

    protected function _saveCategories(Capita_TI_Model_Request $request)
    {
        $categoryTable = $this->getTable('capita_ti/category');
        $categoryIds = $request->getCategoryIds();
        if (!is_array($categoryIds)) {
            $categoryIds = explode(',', (string) $categoryIds);
        }

        $adapter = $this->_getWriteAdapter();
        $condition = sprintf(
            '(%s) AND (%s)',
            $adapter->prepareSqlCondition('request_id', $request->getId()),
            $adapter->prepareSqlCondition('category_id', array('nin' => $categoryIds)));
        $adapter->delete($categoryTable, $condition);

        $insertData = array();
        foreach ($categoryIds as $categoryId) {
            $insertData[] = array(
                'request_id' => $request->getId(),
                'category_id' => $categoryId
            );
        }
        $adapter->insertOnDuplicate($categoryTable, $insertData);
        $request->setOrigData('category_ids', $categoryIds);
    }
}
