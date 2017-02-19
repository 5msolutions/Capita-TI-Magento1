<?php

class Capita_TI_Block_Adminhtml_Request_View extends Mage_Adminhtml_Block_Widget_Form_Container
{

    protected $_blockGroup = 'capita_ti';
    protected $_controller = 'adminhtml_request';
    protected $_mode = 'view';

    public function __construct()
    {
        parent::__construct();

        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_removeButton('save');
        $this->_headerText = $this->__('Request "%s"', $this->_getRequest()->getRemoteNo());
        $refreshUrl = $this->getUrl('*/*/*', array('_current'=>true, 'refresh'=>'status'));
        $this->_addButton('refresh', array(
            'label' => $this->__('Refresh Status'),
            'onclick' => "setLocation('{$refreshUrl}')",
            'class' => 'save'
        ));
    }

    /**
     * @return Capita_TI_Model_Request
     */
    protected function _getRequest()
    {
        return Mage::registry('capita_request');
    }
}