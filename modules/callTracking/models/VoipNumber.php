<?php

namespace app\modules\callTracking\models;

use app\exceptions\ModelValidationException;
use app\modules\uu\models\AccountTariff as UuAccountTariff;
use app\classes\model\ActiveRecord;
use Yii;

/**
 * @property int $number
 * @property int $last_account_tariff_id
 * @property int $last_stop_dt
 * @property bool $is_active
 * @property string $last_user_hash
 */
class VoipNumber extends ActiveRecord
{
    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'number' => 'Телефонный номер',
            'last_account_tariff_id' => 'Последняя услуга',
            'last_stop_dt' => 'Время окончания последней аренды номера',
            'is_active' => 'Вкл.',
            'last_user_hash' => 'Последний пользователь',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'call_tracking.voip_number';
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
     * @param UuAccountTariff $uuAccountTariff
     * @param bool $isActive
     * @throws ModelValidationException
     */
    public static function setActive(UuAccountTariff $uuAccountTariff, $isActive)
    {
        $voipNumber = self::findOne(['voip_number' => $uuAccountTariff->voip_number]);
        if (!$voipNumber) {
            if (!$isActive) {
                return;
            }
            $voipNumber = new self;
            $voipNumber->number = $uuAccountTariff->voip_number;
            $voipNumber->is_active = true;
        } else {
            $voipNumber->is_active = $isActive;
        }

        if (!$voipNumber->save()) {
            throw new ModelValidationException($voipNumber);
        }
    }
}