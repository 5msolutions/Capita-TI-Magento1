<?php

class Capita_TI_Block_Adminhtml_Request extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    protected $_blockGroup = 'capita_ti';
    protected $_controller = 'adminhtml_request';

    public function __construct()
    {
        $this->_headerText = $this->__('Translation Requests');
        parent::__construct();
    }

    protected function getAddButtonLabel()
    {
        return $this->__('New Request');
    }

}
