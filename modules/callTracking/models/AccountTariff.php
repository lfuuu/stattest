<?php

namespace app\modules\callTracking\models;

use app\exceptions\ModelValidationException;
use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int $id
 * @property string $secret_key
 * @property bool $is_active
 * @property string $calltracking_params
 */
class AccountTariff extends ActiveRecord
{
    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'secret_key' => 'Секретный ключ',
            'is_active' => 'Вкл.',
            'calltracking_params' => 'Параметры',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'call_tracking.account_tariff';
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgCallTracking;
    }

    /**
     * @param int $accountTariffId
     * @param bool $isActive
     * @param string $calltrackingParams
     * @throws ModelValidationException
     */
    public static function setActive($accountTariffId, $isActive, $calltrackingParams)
    {
        $accountTariff = self::findOne(['id' => $accountTariffId]);
        if (!$accountTariff) {
            if (!$isActive) {
                return;
            }
            $accountTariff = new self;
            $accountTariff->id = $accountTariffId;
            $accountTariff->is_active = true;
        } else {
            $accountTariff->is_active = $isActive;
        }
        $accountTariff->calltracking_params = $calltrackingParams;

        if (!$accountTariff->save()) {
            throw new ModelValidationException($accountTariff);
        }
    }
}