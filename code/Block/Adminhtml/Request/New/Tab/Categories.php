<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Categories
extends Mage_Adminhtml_Block_Text_List
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function getTabLabel()
    {
        return $this->__('Catalog Categories');
    }

    public function getTabTitle()
    {
        return $this->__('Catalog Categories');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _beforeToHtml()
    {
        $layout = $this->getLayout();
        $this->append($layout->createBlock('capita_ti/adminhtml_request_new_tab_category_grid', 'request_tab_categories_grid'))
            ->append($layout->createBlock('capita_ti/adminhtml_request_new_tab_category_serializer', 'request_tab_categories_grid_serializer'))
            ->append($layout->createBlock('capita_ti/adminhtml_request_new_tab_category_attributes', 'request_tab_categories_attributes'));
        return parent::_beforeToHtml();
    }
}
