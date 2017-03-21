<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Category_Serializer
extends Mage_Adminhtml_Block_Widget_Grid_Serializer
{

    protected function _beforeToHtml()
    {
        parent::_construct();
        $this->initSerializerBlock(
            'request_tab_categories_grid',
            'getEntityIds',
            'category_ids',
            'category_ids');
        return $this;
    }
}
