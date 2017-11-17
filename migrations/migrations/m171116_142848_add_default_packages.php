<?php

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;

/**
 * Class m171116_142848_add_default_packages
 */
class m171116_142848_add_default_packages extends \app\classes\Migration
{
    /**
     * Up
     *
     * @throws \app\exceptions\ModelValidationException
     */
    public function safeUp()
    {
        $query = AccountTariff::find()
            ->where([
                'service_type_id' => ServiceType::ID_VOIP,
                'tariff_period_id' => null,
            ])
            ->andWhere(['>', 'insert_time', '2017-11-01']);
        /** @var AccountTariff $accountTariff */
        foreach ($query->each() as $accountTariff) {
            \app\classes\Event::go(\app\modules\uu\Module::EVENT_ADD_DEFAULT_PACKAGES, [
                    'account_tariff_id' => $accountTariff->id,
                    'client_account_id' => $accountTariff->client_account_id,
                ]
            );
        }
    }

    /**
     * Down
     */
    public function safeDown()
    {
    }
}
