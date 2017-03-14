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

        $this->addColumn('translated', array(
            'header' => $this->__('Translated'),
            'type' => 'text',
            'filter' => 'capita_ti/adminhtml_column_filter_languages',
            'renderer' => 'capita_ti/adminhtml_column_renderer_languages',
            'width' => 100,
            'index' => 'translated'
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
        /* @var $collection Capita_TI_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('capita_ti/product_collection');
        $collection->addAttributeToSelect(array(
            'name',
            'status',
            'visibility'
        ));
        $filter = $this->getParam($this->getVarNameFilter(), null);
        $filterData = is_null($filter) ? $this->_defaultFilter : $this->helper('adminhtml')->prepareFilterString($filter);
        $in_products = @$filterData['in_products'];
        // 0 = No, 1 = Yes, NULL = Any
        // need to filter on No and Yes
        if (!is_null($in_products) && $this->getProductIds()) {
            $collection->addIdFilter($this->getProductIds(), !$in_products);
        }
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/capita_request/productsGrid');
    }
}
