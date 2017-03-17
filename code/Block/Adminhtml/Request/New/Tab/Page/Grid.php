<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Page_Grid
extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('request_new_tab_pages');
        $this->setDefaultSort('identifier');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        $this->setDefaultFilter(array(
            'in_pages' => $this->getPageIds() ? 1 : null // include = Yes
        ));
        return $this;
    }

    protected function _prepareColumns()
    {
        $cmsHelper = Mage::helper('cms');
        $this->addColumn('in_pages', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'renderer'          => 'capita_ti/adminhtml_column_renderer_checkbox',
            'name'              => 'in_pages',
            'values'            => (array) $this->getPageIds(),
            'align'             => 'center',
            'index'             => 'page_id'
        ));

        $this->addColumn('translated', array(
            'header' => $this->__('Translated'),
            'type' => 'text',
            'filter' => 'capita_ti/adminhtml_column_filter_languages',
            'renderer' => 'capita_ti/adminhtml_column_renderer_languages',
            'width' => 100,
            'align' => 'center',
            'index' => 'translated',
            'sortable' => false
        ));

        $this->addColumn('title', array(
            'header' => $cmsHelper->__('Title'),
            'index' => 'title'
        ));

        $this->addColumn('identifier', array(
            'header' => $cmsHelper->__('Identifier'),
            'index' => 'identifier'
        ));

        $this->addColumn('is_active', array(
            'header'    => $cmsHelper->__('Status'),
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array(
                0 => $cmsHelper->__('Disabled'),
                1 => $cmsHelper->__('Enabled')
            ),
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareCollection()
    {
        /* @var $collection Capita_TI_Model_Resource_Page_Collection */
        $collection = Mage::getResourceModel('capita_ti/page_collection');
        // filter by all default-locale stores including global
        $collection->addStoreFilter(Mage::helper('capita_ti')->getStoreIdsByLanguage(
            Mage::getStoreConfig('general/locale/code')));
        $filter = $this->getParam($this->getVarNameFilter(), null);
        $filterData = is_null($filter) ? $this->_defaultFilter : $this->helper('adminhtml')->prepareFilterString($filter);
        $in_pages = @$filterData['in_pages'];
        // 0 = No, 1 = Yes, NULL = Any
        // need to filter on No and Yes
        if (!is_null($in_pages) && $this->getPageIds()) {
            $collection->addIdFilter($this->getPageIds(), !$in_pages);
        }
        $this->setCollection($collection);

        // filters are applied here
        parent::_prepareCollection();

        $ids = $collection->getAllIds();
        $this->getColumn('in_pages')->setAllValues($ids);

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/capita_request/pagesGrid');
    }
}
