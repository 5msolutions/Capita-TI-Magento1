<?php

class Capita_TI_Model_Resource_Request extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('capita_ti/request', 'request_id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $request)
    {
        if (is_array($request->getDestLanguage())) {
            $request->setDestLanguage(
                implode(',', $request->getDestLanguage())
            );
        }

        $request->setProductCount(
            count($request->getProductIds())
        );

        if (is_array($request->getProductAttributes())) {
            $request->setProductAttributes(
                implode(',', $request->getProductAttributes())
            );
        }

        return $this;
    }

    protected function _afterSave(Mage_Core_Model_Abstract $request)
    {
        $adapter = $this->_getWriteAdapter();

        if ($request->isDirty('product_ids')) {
            $productTable = $this->getTable('capita_ti/product');
            $productIds = $request->getProductIds();
            if (!is_array($productIds)) {
                $productIds = explode(',', (string) $productIds);
            }
            $condition = sprintf(
                '(%s) AND (%s)',
                $adapter->prepareSqlCondition('request_id', $request->getId()),
                $adapter->prepareSqlCondition('product_id', array('nin' => $productIds)));
            $adapter->delete($productTable, $condition);
            $adapter->insertOnDuplicate($productTable, array(
                'request_id',
                'product_id'
            ), array_map(
                null,
                array_fill(0, count($productIds), $request->getId()),
                $productIds));
            $request->flagDirty('product_ids', false);
        }
    }
}
