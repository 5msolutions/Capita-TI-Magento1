<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Product_Grid
extends Capita_TI_Block_Adminhtml_Grid_Translatable
{

    public function getEntityIds()
    {
        if (parent::getEntityIds()) {
            return (array) parent::getEntityIds();
        }
        return (array) Mage::getSingleton('adminhtml/session')->getCapitaProductIds();
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('request_new_tab_products');
        $this->setDefaultSort('entity_id');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        return $this;
    }

    protected function _prepareColumns()
    {
        $catalogHelper = Mage::helper('catalog');
        $this->addColumn('entity_id', array(
            'header' => $catalogHelper->__('ID'),
            'type' => 'number',
            'index' => 'entity_id'
        ));

        $this->addColumn('name', array(
            'header' => $catalogHelper->__('Name'),
            'index' => 'name'
        ));

        $this->addColumn('type', array(
            'header'    => $catalogHelper->__('Type'),
            'width'     => 100,
            'index'     => 'type_id',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name', array(
            'header'    => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width'     => 130,
            'index'     => 'attribute_set_id',
            'type'      => 'options',
            'options'   => $sets,
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('catalog')->__('Status'),
            'width'     => 90,
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('visibility', array(
            'header'    => Mage::helper('catalog')->__('Visibility'),
            'width'     => 90,
            'index'     => 'visibility',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('catalog')->__('SKU'),
            'width'     => 80,
            'index'     => 'sku'
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareCollection()
    {
        /* @var $collection Capita_TI_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('capita_ti/product_collection');
        $collection->addAttributeToSelect(array(
            'name',
            'status',
            'visibility'
        ));
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/capita_request/productsGrid');
    }
}
