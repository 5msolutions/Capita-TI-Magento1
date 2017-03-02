<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Blocks
extends Mage_Adminhtml_Block_Widget_Form
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

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        /* @var $collection Mage_Cms_Model_Resource_Block_Collection */
        $collection = Mage::helper('capita_ti')->getCmsBlocksWithLanguages();
        $languagesJson = $this->helper('capita_ti')->jsonEncode(
            array_filter($collection->walk('getLanguages')));
        
        $fieldset = $form->addFieldset('blocks', array(
            'legend' => $this->__('CMS Blocks')
        ));
        $fieldset->addField('block_ids', 'multiselect', array(
            'name' => 'block_ids',
            'label' => $this->__('CMS Blocks'),
            'note' => $this->__('Only blocks that match the source language can be selected.'),
            'values' => $collection->toOptionArray(),
            'value' => $this->getBlockIds()
        ))
        ->setAfterElementHtml('<script type="text/javascript">
            (function(){
            var autoable = function(event) {
                var lang = $F(this);
                $$("#block_ids option").each(function(option) {
                    var langs = '.$languagesJson.'[option.value];
                    if (langs) {
                        option.writeAttribute("disabled", langs.indexOf(lang) >= 0 ? null : "disabled");
                    }
                    // else blocks without definite language are ignored
                });
            };
            Event.observe("source_language", "change", autoable);
            autoable.call("source_language");
            })();
            </script>')
                    ;

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
