<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Category_Grid
extends Mage_Adminhtml_Block_Widget_Grid
{

    public function getCategoryIds()
    {
        if ($this->hasCategoryIds()) {
            return (array) parent::getCategoryIds();
        }
        return (array) Mage::getSingleton('adminhtml/session')->getCapitaCategoryIds();
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('request_new_tab_categories');
        $this->setDefaultSort('entity_id');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        $this->setDefaultFilter(array(
            'in_categories' => $this->getCategoryIds() ? 1 : null // include = Yes
        ));
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('in_categories', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'renderer'          => 'capita_ti/adminhtml_column_renderer_checkbox',
            'name'              => 'in_categories',
            'values'            => $this->getCategoryIds(),
            'align'             => 'center',
            'index'             => 'entity_id'
        ));

        $this->addColumn('translated', array(
            'header' => $this->__('Translated'),
            'type' => 'text',
            'filter' => 'capita_ti/adminhtml_column_filter_languages',
            'renderer' => 'capita_ti/adminhtml_column_renderer_languages',
            'width' => 170,
            'align' => 'center',
            'index' => 'translated',
            'sortable' => false
        ));

        $this->addColumn('entity_id', array(
            'header' => $this->__('ID'),
            'type' => 'number',
            'index' => 'entity_id'
        ));

        $this->addColumn('name', array(
            'header' => $this->__('Name'),
            'index' => 'name',
        ));

        $this->addColumn('url_path', array(
            'header' => $this->__('URL Path'),
            'index' => 'url_path'
        ));

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('catalog')->__('Is Active'),
            'width'     => 60,
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
        ));

        $this->addColumn('include_in_menu', array(
            'header'    => Mage::helper('catalog')->__('Include in Navigation Menu'),
            'width'     => 160,
            'index'     => 'include_in_menu',
            'type'      => 'options',
            'options'   => Mage::getSingleton('adminhtml/system_config_source_yesno')->toArray(),
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareCollection()
    {
        /* @var $collection Capita_TI_Model_Resource_Category_Collection */
        $collection = Mage::getResourceModel('capita_ti/category_collection');
        $collection->addAttributeToSelect(array(
            'name',
            'url_path',
            'is_active',
            'include_in_menu'
        ));
        $collection->addAttributeToFilter('level', array('gt' => 1));
        $filter = $this->getParam($this->getVarNameFilter(), null);
        $filterData = is_null($filter) ? $this->_defaultFilter : $this->helper('adminhtml')->prepareFilterString($filter);
        $in_categories = @$filterData['in_categories'];
        // 0 = No, 1 = Yes, NULL = Any
        // need to filter on No and Yes
        if (!is_null($in_categories) && $this->getCategoryIds()) {
            $collection->addIdFilter($this->getCategoryIds(), !$in_categories);
        }
        $this->setCollection($collection);

        // filters are applied here
        parent::_prepareCollection();

        $ids = $collection->getAllIds();
        $this->getColumn('in_categories')->setAllValues($ids);

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/capita_request/categoriesGrid');
    }
}
