<?php

namespace app\modules\sbisTenzor\models;

use app\classes\model\ActiveRecord;
use app\models\ClientAccount;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Данные по каналам обмена контрагента
 *
 * @property int id
 * @property int contractor_id
 * @property string exchange_id
 * @property string operator_name
 * @property bool is_main
 * @property bool is_roaming
 * @property int exchange_state_code
 * @property string exchange_state_code_description
 * @property string created_at
 * @property string is_deleted
 * @property string deleted_at
 *
 * @property-read ClientAccount $clientAccount
 */
class SBISContractorExchange extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sbis_contractor_exchange';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['exchange_id', 'operator_name', 'exchange_state_code_description'], 'string', 'max' => 255],
            [['contractor_id', 'exchange_state_code'], 'integer'],
            [['is_main', 'is_roaming', 'is_deleted'], 'integer'],
            ['deleted_at', 'safe'],
        ];
    }


    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                // Установить "когда создал" и "когда обновил"
                'class' => TimestampBehavior::class,
                'value' => new Expression("UTC_TIMESTAMP()"), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
            ],
        ];
    }


    /**
     * Вернуть список всех доступных значений
     *
     * @param ClientAccount $client
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getList(
        $contractorId,
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    )
    {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'exchange_id',
            $select = new Expression("concat(if(is_deleted, '(X) ', ''), if(is_main, '(+) ', ''), operator_name, if (is_roaming, ' (R)', ''))"),
            $orderBy = ['id' => SORT_ASC],
            ['contractor_id' => $contractorId]
        );
    }

}