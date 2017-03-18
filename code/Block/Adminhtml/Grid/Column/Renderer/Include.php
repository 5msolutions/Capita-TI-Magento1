<?php

class Capita_TI_Block_Adminhtml_Grid_Column_Renderer_Include extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox
{

    public function renderHeader()
    {
        $html = parent::renderHeader();
        if ($this->getColumn()->getAllValues()) {
            $html = preg_replace(
                '/\/>$/',
                'value="'.htmlentities(implode('&', $this->getColumn()->getAllValues())).'"/>',
                $html);
        }
        return $html;
    }
}
