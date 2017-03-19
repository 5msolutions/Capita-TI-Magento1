<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Attribute_Serializer
extends Mage_Adminhtml_Block_Widget_Grid_Serializer
{

    protected function _beforeToHtml()
    {
        parent::_construct();
        $this->initSerializerBlock(
            'request_tab_attributes_grid',
            'getEntityIds',
            'attribute_ids',
            'attribute_ids');
        return $this;
    }
}
