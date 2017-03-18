<?php

/**
 * @method int[] getEntityIds()
 * @method Capita_TI_Block_Adminhtml_Grid_Translatable setEntityIds(int[] $ids)
 */
abstract class Capita_TI_Block_Adminhtml_Grid_Translatable
extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _construct()
    {
        parent::_construct();
        if ($this->getEntityIds()) {
            $this->setDefaultFilter(array(
                'include' => 1
            ));
        }
    }

    protected function _prepareColumns()
    {
        /* @var $incColumn Mage_Adminhtml_Block_Widget_Grid_Column */
        $incColumn = $this->getLayout()->createBlock('adminhtml/widget_grid_column');
        $incColumn->setData(array(
            'header_css_class' => 'a-center',
            'type'             => 'checkbox',
            'renderer'         => 'capita_ti/adminhtml_grid_column_renderer_include',
            'values'           => (array) $this->getEntityIds(),
            'align'            => 'center',
            'id'               => 'include',
            'filter_condition_callback' => array($this, 'addIncludeFilter')
        ));
        $incColumn->setGrid($this);

        /* @var $transColumn Mage_Adminhtml_Block_Widget_Grid_Column */
        $transColumn = $this->getLayout()->createBlock('adminhtml/widget_grid_column');
        $transColumn->setData(array(
            'header'   => $this->__('Translated'),
            'type'     => 'text',
            'filter'   => 'capita_ti/adminhtml_grid_column_filter_languages',
            'renderer' => 'capita_ti/adminhtml_grid_column_renderer_languages',
            'width'    => 170,
            'align'    => 'center',
            'index'    => 'translated',
            'id'       => 'translated',
            'sortable' => false
        ));
        $transColumn->setGrid($this);

        $this->_columns = array_merge(array(
            'include' => $incColumn,
            'translated' => $transColumn
        ), $this->_columns);

        return parent::_prepareColumns();
    }

    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        $ids = $this->getCollection()->getAllIds();
        $idFieldName = $this->getCollection()->getResource()->getIdFieldName();
        $this->getColumn('include')
            ->setAllValues($ids)
            ->setIndex($idFieldName);

        return $this;
    }

    protected function addIncludeFilter(Varien_Data_Collection_Db $collection, Mage_Adminhtml_Block_Widget_Grid_Column $column)
    {
        // 0 = No, 1 = Yes, NULL = Any
        // need to filter on No and Yes
        $include = $column->getFilter()->getValue();
        if (!is_null($include) && ($this->getEntityIds())) {
            if ($collection instanceof Mage_Eav_Model_Entity_Collection_Abstract) {
                $collection->addAttributeToFilter(
                    'entity_id',
                    array($include ? 'in' : 'nin' => $this->getEntityIds()));
            }
            else {
                $from = $collection->getSelect()->getPart(Zend_Db_Select::FROM);
                reset($from);
                $table = key($from);
                $collection->addFieldToFilter(
                    $table.'.'.$collection->getResource()->getIdFieldName(),
                    array($include ? 'in' : 'nin' => $this->getEntityIds()));
            }
        }
    }
}
