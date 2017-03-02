<?php

class Capita_TI_Block_Adminhtml_Request_New_Tab_Pages
extends Mage_Adminhtml_Block_Widget_Form
implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    public function getTabLabel()
    {
        return $this->__('CMS Pages');
    }

    public function getTabTitle()
    {
        return $this->__('CMS Pages');
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
        /* @var $collection Mage_Cms_Model_Resource_Page_Collection */
        $collection = Mage::helper('capita_ti')->getCmsPagesWithLanguages();
        $languagesJson = $this->helper('capita_ti')->jsonEncode(
            array_filter($collection->walk('getLanguages')));
        $options = array();
        foreach ($collection as $page) {
            $options[] = array(
                'value' => $page->getId(),
                'label' => $page->getTitle()
            );
        }
        
        $fieldset = $form->addFieldset('pages', array(
            'legend' => $this->__('CMS Pages')
        ));
        $fieldset->addField('page_ids', 'multiselect', array(
            'name' => 'page_ids',
            'label' => $this->__('CMS Pages'),
            'note' => $this->__('Only pages that match the source language can be selected.'),
            'values' => $options,
            'value' => $this->getPageIds()
        ))
        ->setAfterElementHtml('<script type="text/javascript">
            (function(){
            var autoable = function(event) {
                var lang = $F(this);
                $$("#page_ids option").each(function(option) {
                    var langs = '.$languagesJson.'[option.value];
                    if (langs) {
                        option.writeAttribute("disabled", langs.indexOf(lang) >= 0 ? null : "disabled");
                    }
                    // else pages without definite language are ignored
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
