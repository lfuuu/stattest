<?php

namespace app\models\billing;

use Yii;

/**
 * This is the model class for table "stats_nnp_package_minute".
 *
 * @property integer $id
 * @property integer $server_id
 * @property integer $nnp_account_tariff_light_id
 * @property integer $nnp_package_minute_id
 * @property string $activate_from
 * @property string $deactivate_from
 * @property integer $used_seconds
 * @property integer $paid_seconds
 * @property double $used_credit
 * @property integer $min_call_id
 * @property integer $max_call_id
 * @property string $amount_day
 * @property double $used_credit_amount_day
 */
class StatNnpPackageMinute extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'billing.stats_nnp_package_minute';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'server_id', 'nnp_account_tariff_light_id', 'activate_from', 'deactivate_from', 'used_seconds', 'paid_seconds', 'used_credit', 'min_call_id', 'max_call_id'], 'required'],
            [['id', 'server_id', 'nnp_account_tariff_light_id', 'nnp_package_minute_id', 'used_seconds', 'paid_seconds', 'min_call_id', 'max_call_id'], 'integer'],
            [['activate_from', 'deactivate_from', 'amount_day'], 'safe'],
            [['used_credit', 'used_credit_amount_day'], 'number'],
        ];
    }
}
