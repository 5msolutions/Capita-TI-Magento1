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

        if (is_array($request->getProductAttributes())) {
            $request->setProductAttributes(
                implode(',', $request->getProductAttributes())
            );
        }

        $request->setUpdatedAt($this->formatDate(true));

        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $request)
    {
        if ($request->dataHasChangedFor('documents')) {
            $this->_saveDocuments($request);
        }

        if ($request->dataHasChangedFor('product_attributes')) {
            $this->_saveProductAttributes($request);
        }

        if ($request->dataHasChangedFor('product_ids')) {
            $this->_saveProducts($request);
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
                    ->setRequestId($request->getId());
            }
            $document->save();
        }
        $request->setData('documents', $documents)
            ->setOrigData('documents', $documents);
    }

    protected function _saveProductAttributes(Capita_TI_Model_Request $request)
    {
        $attrTable = $this->getTable('capita_ti/attribute');
        $attrCodes = $request->getProductAttributes();
        if (!is_array($attrCodes)) {
            $attrCodes = explode(',', (string) $attrCodes);
        }
        /* @var $attributes Mage_Catalog_Model_Resource_Product_Attribute_Collection */
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection');
        $attributes->addFieldToFilter('main_table.attribute_code', array('in' => $attrCodes));
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
}
