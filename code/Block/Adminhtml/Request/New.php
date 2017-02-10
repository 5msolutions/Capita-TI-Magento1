<?php

class Capita_TI_Block_Adminhtml_Request_New extends Mage_Adminhtml_Block_Widget_Form_Container
{

    protected $_blockGroup = 'capita_ti';
    protected $_controller = 'adminhtml_request';
    protected $_mode = 'new';

    public function __construct()
    {
        parent::__construct();

        $this->_updateButton('save', 'label', $this->__('Place Request'));
        $this->_headerText = $this->__('Request New Translation');
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/catalog_product');
    }

}
