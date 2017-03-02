<?php

class Capita_TI_Block_Adminhtml_Request_New_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('capita_ti_new_request_tabs');
        $this->setDestElementId('edit_form');
        return $this;
    }

    public function addGeneralTab()
    {
        $this->addTab('general', 'capita_ti/adminhtml_request_new_tab_general');
        return $this;
    }

    public function addProductsTab()
    {
        $this->addTab('products', array(
            'label' => $this->__('Catalog Products'),
            'title' => $this->__('Catalog Products'),
            'url' => $this->getUrl('*/capita_request/productsTab'),
            'class' => 'ajax'
        ));
        return $this;
    }

    public function addCategoriesTab()
    {
        $this->addTab('categories', array(
            'label' => $this->__('Catalog Categories'),
            'title' => $this->__('Catalog Categories'),
            'url' => $this->getUrl('*/capita_request/categoriesTab'),
            'class' => 'ajax'
        ));
        return $this;
    }

    public function addBlocksTab()
    {
        $this->addTab('blocks', array(
            'label' => $this->__('CMS Blocks'),
            'title' => $this->__('CMS Blocks'),
            'url' => $this->getUrl('*/capita_request/blocksTab'),
            'class' => 'ajax'
        ));
        return $this;
    }

    public function addPagesTab()
    {
        $this->addTab('pages', array(
            'label' => $this->__('CMS Pages'),
            'title' => $this->__('CMS Pages'),
            'url' => $this->getUrl('*/capita_request/pagesTab'),
            'class' => 'ajax'
        ));
        return $this;
    }
}
