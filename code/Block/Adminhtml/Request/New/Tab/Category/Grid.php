<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Category_Grid
extends Capita_TI_Block_Adminhtml_Grid_Translatable
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('request_new_tab_categories');
        $this->setDefaultSort('entity_id');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        return $this;
    }

    protected function _prepareColumns()
    {
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
        $this->setCollection($collection);

        parent::_prepareCollection();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/capita_request/categoriesGrid');
    }
}
