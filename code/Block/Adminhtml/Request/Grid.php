<?php

class Capita_TI_Block_Adminhtml_Request_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('request_id');
        $this->setDefaultDir('DSEC');

        $this->setId('request_id');
    }

	protected function _prepareCollection()
	{
		$collection = Mage::getResourceModel('capita_ti/request_collection');
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

    protected function _prepareColumns()
    {
		$this->addColumn('request_id', array(
			'index' => 'request_id',
			'header' => $this->__('ID'),
		    'type' => 'number',
			'width' => '100px'
		));
		$this->addColumn('dest_language', array(
			'index' => 'dest_language',
			'header' => $this->__('Languages'),
		    'type' => 'options',
		    'options' => Mage::getSingleton('capita_ti/api_languages')->getLanguagesInUse(),
		    'filter_condition_callback' => array($this, 'filterLanguages')
		));
		$this->addColumn('delivery_date', array(
			'index' => 'delivery_date',
			'header' => $this->__('Expected Delivery'),
		    'type' => 'datetime',
			'width' => '100px'
		));
		$this->addColumn('status', array(
			'index' => 'status',
			'header' => $this->__('Status'),
		    'type' => 'options',
		    'options' => Mage::getSingleton('capita_ti/source_status')->getOptions(),
			'width' => '100px'
		));

		return parent::_prepareColumns();
    }

    public function filterLanguages(Capita_TI_Model_Resource_Request_Collection $collection, Mage_Adminhtml_Block_Widget_Grid_Column $column)
    {
        $collection->addFilterLikeLanguage($column->getFilter()->getValue());
    }
}
