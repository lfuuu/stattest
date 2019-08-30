<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use DateTime;
use yii\db\Expression;

/**
 * @property int $contract_id
 * @property string $actual_from
 * @property int $once_only
 * @property int $percentage_of_fee
 * @property int $percentage_of_over
 * @property int $percentage_of_margin
 * @property int $period_month
 * @property string $period_type
 *
 * @property-read User $user
 */
class ClientContractReward extends ActiveRecord
{

    const PERIOD_ALWAYS = 'always';
    const PERIOD_MONTH = 'month';

    const SHOW_LAST_REWARDS = 3;

    public static $usages = [
        Transaction::SERVICE_VOIP => 'IP телефония',
        Transaction::SERVICE_VIRTPBX => 'ВАТС',
        Transaction::SERVICE_CALL_CHAT => 'Звонок и Чат',
        Transaction::SERVICE_TRUNK => 'Транки (Межоператорка)',
    ];

    public static $period = [
        self::PERIOD_MONTH => 'Кол-во продлений',
        self::PERIOD_ALWAYS => 'Всегда',
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'client_contract_reward';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'contract_id',
                    'once_only',
                    'percentage_once_only',
                    'percentage_of_fee',
                    'percentage_of_over',
                    'percentage_of_margin',
                    'period_month'
                ],
                'integer',
                'integerOnly' => true
            ],
            ['actual_from', 'string'],
            ['period_type', 'in', 'range' => [self::PERIOD_ALWAYS, self::PERIOD_MONTH]],
            ['usage_type', 'in', 'range' => array_keys(self::$usages)],

            [['once_only', 'percentage_once_only', 'percentage_of_fee', 'percentage_of_over', 'period_month'], 'default', 'value' => 0],
            [['period_type'], 'default', 'value' => self::PERIOD_ALWAYS],

            [['contract_id', 'usage_type', 'actual_from', 'period_type'], 'required'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'actual_from' => 'Дата активации',
            'once_only' => 'Разовое',
            'percentage_once_only' => 'От подключения',
            'percentage_of_fee' => 'От абонентской платы',
            'percentage_of_over' => 'От ресурса',
            'percentage_of_margin' => 'От маржи',
            'period_type' => 'Период выплат',
        ];
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        $now = (new DateTime('first day of this month'))->setTime(0, 0, 0);
        $actualFrom = (new DateTime($this->actual_from))->setTime(0, 0, 0);

        return $actualFrom > $now;
    }

    /**
     * Получение последних настроек партнерских вознаграждений, сгруппированных по типу
     *
     * @param integer $partnerContractId
     * @param string $createdAt
     * @param boolean $isSpecial
     * @return array
     */
    public static function getActualByContract($partnerContractId, $createdAt, $isSpecial = true)
    {
        $query = self::find();
        if ($isSpecial) {
            $query->select([
                'usage_type',
                'once_only',
                'percentage_once_only',
                'percentage_of_fee',
                'percentage_of_over',
                'percentage_of_margin',
                'period_type',
                'period_month',
            ]);
        }
        return $query
            ->innerJoin([
                'groupped' => ClientContractReward::find()
                    ->select(['id' => new Expression('MAX(id)')])
                    ->where(['contract_id' => $partnerContractId])
                    ->groupBy('usage_type')
            ], 'groupped.id = ' . ClientContractReward::tableName() . '.id')
            ->indexBy('usage_type')
            ->asArray()
            ->all();
    }
}
