<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Product_Grid
extends Mage_Adminhtml_Block_Widget_Grid
{

    public function getProductIds()
    {
        if ($this->hasProductIds()) {
            return (array) parent::getProductIds();
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
        $this->setDefaultFilter(array(
            'in_products' => $this->getProductIds() ? 1 : null // include = Yes
        ));
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('in_products', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'name'              => 'in_products',
            'values'            => $this->getProductIds(),
            'align'             => 'center',
            'index'             => 'entity_id'
        ));

        $this->addColumn('entity_id', array(
            'header' => $this->__('ID'),
            'type' => 'number',
            'index' => 'entity_id'
        ));

        $this->addColumn('name', array(
            'header' => $this->__('Name'),
            'index' => 'name'
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('catalog')->__('Type'),
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
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addAttributeToSelect(array(
            'name',
            'status',
            'visibility'
        ));
        $this->setCollection($collection);
        parent::_prepareCollection(); // filters are parsed here

        $in_products = $this->getColumn('in_products')->getFilter()->getValue();
        if (!is_null($in_products) && $this->getProductIds()) {
            $collection->clear()->addAttributeToFilter(
                'entity_id',
                array($in_products ? 'in' : 'nin' => $this->getProductIds()));
        }
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/capita_request/productsGrid');
    }
}
