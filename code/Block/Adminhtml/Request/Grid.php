<?php

class Capita_TI_Block_Adminhtml_Request_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');

        $this->setId('request');
        $this->setUseAjax(true);
    }

	protected function _prepareCollection()
	{
		$collection = Mage::getResourceModel('capita_ti/request_collection');
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

    protected function _prepareColumns()
    {
		$this->addColumn('remote_no', array(
			'index' => 'remote_no',
			'header' => $this->__('Request No.'),
			'width' => '100px'
		));
		$this->addColumn('dest_language', array(
			'index' => 'dest_language',
			'header' => $this->__('Languages'),
		    'type' => 'options',
		    'options' => Mage::getSingleton('capita_ti/api_languages')->getLanguagesInUse(),
		    'filter_condition_callback' => array($this, 'filterLanguages')
		));
		$this->addColumn('product_count', array(
		    'index' => 'product_count',
		    'header' => $this->__('# of products'),
		    'type' => 'number',
		    'width' => '100px'
		));
		$this->addColumn('category_count', array(
		    'index' => 'category_count',
		    'header' => $this->__('# of categories'),
		    'type' => 'number',
		    'width' => '100px'
		));
		$this->addColumn('block_count', array(
		    'index' => 'block_count',
		    'header' => $this->__('# of blocks'),
		    'type' => 'number',
		    'width' => '100px'
		));
		$this->addColumn('page_count', array(
		    'index' => 'page_count',
		    'header' => $this->__('# of pages'),
		    'type' => 'number',
		    'width' => '100px'
		));
		$this->addColumn('created_at', array(
			'index' => 'created_at',
			'header' => $this->__('Submission Date'),
		    'type' => 'datetime',
			'width' => '150px'
		));
		$this->addColumn('status', array(
			'index' => 'status',
			'header' => $this->__('Status'),
		    'type' => 'options',
		    'options' => Mage::getSingleton('capita_ti/source_status')->getOptions(),
			'width' => '103px' // why 103? it aligns with the refresh button directly above
		));

		return parent::_prepareColumns();
    }

    protected function _prepareLayout()
    {
        $this->setChild('refresh_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => $this->__('Refresh Status'),
                'onclick'   => $this->getJsObjectName().'.refreshStatus()'
            ))
        );
        $this->setAdditionalJavaScript('
            varienGrid.prototype.refreshStatus = function() {
                this.reload(this._addVarToUrl(this.url, "refresh", "status"));
            }
        ');
        return parent::_prepareLayout();
    }

    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        $html .= $this->getChildHtml('refresh_button');
        return $html;
    }

    public function filterLanguages(Capita_TI_Model_Resource_Request_Collection $collection, Mage_Adminhtml_Block_Widget_Grid_Column $column)
    {
        $collection->addFilterLikeLanguage($column->getFilter()->getValue());
    }

    public function getGridUrl($params = array())
    {
        return $this->getUrl('*/*/grid', $params);
    }

    public function getRowUrl($item)
    {
        return $this->getUrl('*/*/view', array(
            'id' => $item->getId()
        ));
    }
}
