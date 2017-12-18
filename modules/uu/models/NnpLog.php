<?php

namespace app\modules\uu\models;

use app\classes\model\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Лог запросов к ННП-номерам
 *
 * @property integer $id
 * @property integer $account_tariff_id
 * @property string $insert_time
 *
 * @property-read AccountTariff $accountTariff
 *
 * @method static NnpLog findOne($condition)
 * @method static NnpLog[] findAll($condition)
 */
class NnpLog extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'uu_nnp_log';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['account_tariff_id'], 'integer'],
            [['account_tariff_id'], 'required'],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                [
                    // Установить "когда создал"
                    'class' => TimestampBehavior::className(),
                    'createdAtAttribute' => 'insert_time',
                    'updatedAtAttribute' => null,
                    'value' => new Expression('UTC_TIMESTAMP()'), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
                ],
            ]
        );
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountTariff()
    {
        return $this->hasOne(AccountTariff::className(), ['id' => 'account_tariff_id']);
    }
}
