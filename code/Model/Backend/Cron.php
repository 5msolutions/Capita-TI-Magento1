<?php

class Capita_TI_Model_Backend_Cron extends Mage_Core_Model_Config_Data
{

    const CRON_STRING_PATH = 'crontab/jobs/capita_ti_%s/schedule/cron_expr';

    protected function _afterSave()
    {
        $path = sprintf(self::CRON_STRING_PATH, $this->getField());
        if ($this->isValueChanged() || !Mage::getStoreConfigFlag($path)) {
            switch ($this->getValue()) {
                case 0: // manually
                    // impossible expression = never run
                    $cronExpr = '99-99/99 * * * *';
                    break;
                case 1: // always
                    $cronExpr = '* * * * *';
                    break;
                case 5: // 5 minutes
                    $cronExpr = '*/5 * * * *';
                    break;
                case 1440: // 24 hours
                    $cronExpr = sprintf('%d %d * * *', rand(0, 59), rand(0, 6));
                    break;
                case 10080: // 7 days
                    $cronExpr = sprintf('%d %d * * %d', rand(0, 59), rand(0, 6), rand(5,6));
                    break;
                case 60: // 1 hour
                default:
                    $cronExpr = sprintf('%d * * * *', rand(0, 59));
            }
            try {
                Mage::getModel('core/config_data')
                ->load($path, 'path')
                ->setValue($cronExpr)
                ->setPath($path)
                ->save();
            } catch (Exception $e) {
                throw new Exception(Mage::helper('cron')->__('Unable to save the cron expression.'));
            }
        }
    }
}
