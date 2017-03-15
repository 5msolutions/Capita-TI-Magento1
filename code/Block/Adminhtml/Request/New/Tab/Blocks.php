<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Blocks
extends Mage_Adminhtml_Block_Text_List
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function getTabLabel()
    {
        return $this->__('CMS Blocks');
    }

    public function getTabTitle()
    {
        return $this->__('CMS Blocks');
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
        $this->append($layout->createBlock('capita_ti/adminhtml_request_new_tab_block_grid', 'request_tab_blocks_grid'))
            ->append($layout->createBlock('capita_ti/adminhtml_request_new_tab_block_serializer', 'request_tab_blocks_grid_serializer'));
        return parent::_beforeToHtml();
    }
}
