<?php

class Capita_TI_Block_Adminhtml_Cron_Messages extends Mage_Core_Block_Messages
{

    public function _prepareLayout()
    {
        /* @var $cronjobs Mage_Cron_Model_Resource_Schedule_Collection */
        $cronjobs = Mage::getModel('cron/schedule')->getCollection();
        $cronjobs->addFieldToFilter('status', 'error');
        $cronjobs->addFieldToFilter('job_code', array('in' => array('capita_ti_import', 'capita_ti_export')));
        /* @var $schedule Mage_Cron_Model_Schedule */
        foreach ($cronjobs as $schedule) {
            $line = strtok($schedule->getMessages(), "\r\n");
            $this->addWarning($line);
        }

        // do not call parent
        return $this;
    }

    protected function _toHtml()
    {
        if ($this->getMessageCollection()->count()) {
            return '<div class="content-header"><h3 class="icon-head">'.
                $this->__('Recent Problems').
                '</h3></div>'.parent::_toHtml();
        }
        return parent::_toHtml();
    }
}
