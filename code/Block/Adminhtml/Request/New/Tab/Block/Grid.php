<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Block_Grid
extends Capita_TI_Block_Adminhtml_Grid_Translatable
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('request_new_tab_blocks');
        $this->setDefaultSort('identifier');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        return $this;
    }

    protected function _prepareColumns()
    {
        $cmsHelper = Mage::helper('cms');

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
        /* @var $collection Capita_TI_Model_Resource_Block_Collection */
        $collection = Mage::getResourceModel('capita_ti/block_collection');
        // filter by all default-locale stores including global
        $collection->addStoreFilter(Mage::helper('capita_ti')->getStoreIdsByLanguage(
            Mage::getStoreConfig('general/locale/code')));
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/capita_request/blocksGrid');
    }
}
