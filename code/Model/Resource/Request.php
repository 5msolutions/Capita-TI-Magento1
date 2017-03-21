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

        $select = $adapter->select();
        $select->from($this->getTable('capita_ti/block'), 'block_id')
            ->where('request_id=?', $request->getId());
        $request->setBlockIds($adapter->fetchCol($select));

        $select = $adapter->select();
        $select->from($this->getTable('capita_ti/page'), 'page_id')
            ->where('request_id=?', $request->getId());
        $request->setPageIds($adapter->fetchCol($select));

        $select = $adapter->select();
        $select->from($this->getTable('capita_ti/attribute'), 'attribute_id')
            ->where('request_id=?', $request->getId());
        $request->setAttributeIds($adapter->fetchCol($select));

        return parent::_afterLoad($request);
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $request)
    {
        if (is_array($request->getDestLanguage())) {
            $request->setDestLanguage(
                implode(',', $request->getDestLanguage())
            );
        }

        if (!$request->hasProductCount()) {
            $request->setProductCount(
                is_string($request->getProductIds()) ?
                substr_count($request->getProductIds(), ',') + 1 :
                count($request->getProductIds())
            );
        }

        if (!$request->hasCategoryCount()) {
            $request->setCategoryCount(
                is_string($request->getCategoryIds()) ?
                substr_count($request->getCategoryIds(), ',') + 1 :
                count($request->getCategoryIds())
            );
        }

        if (!$request->hasBlockCount()) {
            $request->setBlockCount(
                is_string($request->getBlockIds()) ?
                substr_count($request->getBlockIds(), ',') + 1 :
                count($request->getBlockIds())
            );
        }

        if (!$request->hasPageCount()) {
            $request->setPageCount(
                is_string($request->getPageIds()) ?
                substr_count($request->getPageIds(), ',') + 1 :
                count($request->getPageIds())
            );
        }

        if (!$request->hasAttributeCount()) {
            $request->setAttributeCount(
                is_string($request->getAttributeIds()) ?
                substr_count($request->getAttributeIds(), ',') + 1 :
                count($request->getAttributeIds())
            );
        }

        $request->setUpdatedAt($this->formatDate(true));

        return parent::_beforeSave($request);
    }

    protected function _afterSave(Mage_Core_Model_Abstract $request)
    {
        if ($request->dataHasChangedFor('documents')) {
            $this->_saveDocuments($request);
        }

        if ($request->dataHasChangedFor('product_ids')) {
            $this->_saveLinks($request, 'capita_ti/product', 'product_id');
        }

        if ($request->dataHasChangedFor('category_ids')) {
            $this->_saveLinks($request, 'capita_ti/category', 'category_id');
        }

        if ($request->dataHasChangedFor('block_ids')) {
            $this->_saveLinks($request, 'capita_ti/block', 'block_id');
        }

        if ($request->dataHasChangedFor('page_ids')) {
            $this->_saveLinks($request, 'capita_ti/page', 'page_id');
        }

        if ($request->dataHasChangedFor('attribute_ids')) {
            $this->_saveLinks($request, 'capita_ti/attribute', 'attribute_id');
        }

        return parent::_afterSave($request);
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

        return parent::_afterDelete($request);
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

    protected function _saveLinks(Capita_TI_Model_Request $request, $tableEntity, $idFieldName)
    {
        $linkTable = $this->getTable($tableEntity);
        $idsFieldName = $idFieldName . 's';
        $entityIds = $request->getData($idsFieldName);
        if (!is_array($entityIds)) {
            $entityIds = explode(',', (string) $entityIds);
        }
        
        $adapter = $this->_getWriteAdapter();
        $condition = sprintf(
            '(%s) AND (%s)',
            $adapter->prepareSqlCondition('request_id', $request->getId()),
            $adapter->prepareSqlCondition($idFieldName, array('nin' => $entityIds)));
        $adapter->delete($linkTable, $condition);
        
        $insertData = array();
        foreach ($entityIds as $entityId) {
            $insertData[] = array(
                'request_id' => $request->getId(),
                $idFieldName => $entityId
            );
        }
        $adapter->insertOnDuplicate($linkTable, $insertData);
        $request->setOrigData($idsFieldName, $entityIds);
    }
}
