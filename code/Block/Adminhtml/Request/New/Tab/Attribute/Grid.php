<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Attribute_Grid
extends Capita_TI_Block_Adminhtml_Grid_Translatable
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('request_new_tab_attribute');
        $this->setDefaultSort('attribute_code');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        return $this;
    }

    protected function _prepareColumns()
    {
        $helper = Mage::helper('catalog');
        $yesno = array(
            1 => $helper->__('Yes'),
            0 => $helper->__('No')
        );

        $this->addColumn('attribute_code', array(
            'header' => $helper->__('Attribute Code'),
            'index' => 'attribute_code'
        ));

        $this->addColumn('frontend_label', array(
            'header' => $helper->__('Attribute Label'),
            'index' => 'frontend_label'
        ));

        $this->addColumn('is_user_defined', array(
            'header' => Mage::helper('eav')->__('System'),
            'index' => 'is_user_defined',
            'type' => 'options',
            'align' => 'center',
            'options' => array(
                '0' => Mage::helper('eav')->__('Yes'),   // intended reverted use
                '1' => Mage::helper('eav')->__('No'),    // intended reverted use
            ),
        ));

        $this->addColumn('is_visible', array(
            'header' => $helper->__('Visible'),
            'index' => 'is_visible_on_front',
            'type' => 'options',
            'options' => $yesno,
            'align' => 'center',
        ), 'frontend_label');

        $this->addColumn('is_searchable', array(
            'header' => $helper->__('Searchable'),
            'index' => 'is_searchable',
            'type' => 'options',
            'options' => $yesno,
            'align' => 'center',
        ));

        $this->addColumn('is_filterable', array(
            'header' => $helper->__('Use in Layered Navigation'),
            'index' => 'is_filterable',
            'type' => 'options',
            'options' => array(
                '1' => $helper->__('Filterable (with results)'),
                '2' => $helper->__('Filterable (no results)'),
                '0' => $helper->__('No'),
            ),
            'align' => 'center',
        ));

        $this->addColumn('is_comparable', array(
            'header' => $helper->__('Comparable'),
            'index' => 'is_comparable',
            'type' => 'options',
            'options' => $yesno,
            'align' => 'center',
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareCollection()
    {
        /* @var $collection Capita_TI_Model_Resource_Attribute_Collection */
        $collection = Mage::getResourceModel('capita_ti/attribute_collection')
            // visible on backend, not same as is_visible_on_front column
            ->addVisibleFilter();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/capita_request/attributesGrid');
    }
}
