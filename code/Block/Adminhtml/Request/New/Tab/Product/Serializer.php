<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Product_Serializer
extends Mage_Adminhtml_Block_Widget_Grid_Serializer
{

    protected function _beforeToHtml()
    {
        parent::_construct();
        $this->initSerializerBlock(
            'request_tab_products_grid',
            'getProductIds',
            'product_ids',
            'product_ids');
        return $this;
    }
}
