<?php
use app\modules\uu\models\AccountTariff;

/**
 * Class m170414_122928_uu_fix_packages
 */
class m170414_122928_uu_fix_packages extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $query = AccountTariff::find();
        /** @var AccountTariff $accountTariff */
        foreach ($query->each() as $accountTariff) {

            // при необходимости добавить дефолтные пакеты или закрыть все пакеты
            $accountTariff->addOrCloseDefaultPackage();

            echo '. ';
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
    }
}
