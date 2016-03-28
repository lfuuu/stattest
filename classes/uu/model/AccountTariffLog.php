<?php

namespace app\classes\uu\model;

use app\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Лог тарифов универсальной услуги
 *
 * @property int $id
 * @property int $account_tariff_id
 * @property int $tariff_period_id если null, то закрыто
 * @property string $actual_from !
 *
 * @property TariffPeriod $tariffPeriod
 */
class AccountTariffLog extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    // Методы для полей insert_time, insert_user_id
    use \app\classes\traits\InsertUserTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_account_tariff_log';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['account_tariff_id', 'tariff_period_id'], 'integer'],
            [['account_tariff_id', 'actual_from'], 'required'],
            ['actual_from', 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getTariffPeriod()
    {
        return $this->hasOne(TariffPeriod::className(), ['id' => 'tariff_period_id']);
    }

    /**
     * Вернуть сгенерированное имя
     * @return string
     */
    public function getName()
    {
        return $this->tariffPeriod ?
            $this->tariffPeriod->getName() :
            Yii::t('common', 'Closed');
    }
}