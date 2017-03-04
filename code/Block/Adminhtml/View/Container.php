<?php

class Capita_TI_Block_Adminhtml_View_Container extends Mage_Adminhtml_Block_Widget_Container
{

    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('widget/view/container.phtml');

        $this->_addButton('close', array(
            'label'     => $this->__('Close'),
            'onclick'   => 'window.close()',
            'class'     => 'delete',
        ));
    }

    public function setHeaderText($text)
    {
        $this->_headerText = $text;
    }

    public function getViewHtml()
    {
        $html = '';
        foreach ($this->getChild() as $child) {
            if (!($child instanceof Mage_Adminhtml_Block_Widget_Button)) {
                $html .= $child->toHtml();
            }
        }
        return $html;
    }
}
