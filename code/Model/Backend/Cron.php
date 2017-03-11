<?php

class Capita_TI_Model_Backend_Cron extends Mage_Core_Model_Config_Data
{

    const CRON_STRING_PATH = 'crontab/jobs/capita_ti_%s/schedule/cron_expr';

    protected function _afterSave()
    {
        $path = sprintf(self::CRON_STRING_PATH, $this->getField());
        if ($this->isValueChanged() || !Mage::getStoreConfigFlag($path)) {
            switch ($this->getValue()) {
                case 'always': // most cron setups are 5 mins or less, this is close enough
                    $cronExpr = '*/5 * * * *';
                    break;
                case 'hourly':
                    $cronExpr = sprintf('%d * * * *', rand(0, 59));
                    break;
                case 'daily': // before 7AM
                    $cronExpr = sprintf('%d %d * * *', rand(0, 59), rand(0, 6));
                    break;
                case 'weekly': // saturday or sunday
                    $cronExpr = sprintf('%d %d * * %d', rand(0, 59), rand(0, 6), rand(0,1)*6);
                    break;
                case 'monthly': // always 1st day before 7AM
                    $cronExpr = sprintf('%d %d 1 * *', rand(0, 59), rand(0, 6));
                    break;
                case 'yearly': // always 1st of Jan before 7AM
                    $cronExpr = sprintf('%d %d 1 1 *', rand(0, 59), rand(0, 6));
                    break;
                case 'never':
                default:
                    $cronExpr = '';
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
