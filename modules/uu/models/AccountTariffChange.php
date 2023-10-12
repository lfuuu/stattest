<?php

namespace app\modules\uu\models;

use app\classes\behaviors\CreatedAt;
use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\models\User;
use yii\behaviors\BlameableBehavior;

/**
 * @property int $id
 * @property string $created_at
 * @property int $account_tariff_id
 * @property int $client_account_id
 * @property array $message // json
 * @property int $is_published
 * @property int $user_id
 */
class AccountTariffChange extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_account_tariff_change';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['message', 'account_tariff_id'], 'required'],
            [['account_tariff_id', 'is_published'], 'integer'],
        ];
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'CreatedAt' => CreatedAt::class,
            'leaving a trace of the creator' => [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'user_id',
                'updatedByAttribute' => null
            ]
        ]);
    }

    // DAO
    public static function add($clientAccountId, $accountTariffId, array $changes)
    {
        unset($changes['account_tariff_id']);

        $change = new static();
        $change->client_account_id = $clientAccountId;
        $change->account_tariff_id = $accountTariffId;
        $change->message = $changes;

        if (!$change->save()) {
            throw new ModelValidationException($change);
        }

        EventQueue::go(\app\modules\uu\Module::EVENT_UU_ANONCE, [
            'account_tariff_id' => $accountTariffId,
            'client_account_id' => $clientAccountId,
        ],
            $isForceAdd = false,
            $nextStart = DateTimeZoneHelper::getUtcDateTime()
                ->modify('+2 second')
                ->format(DateTimeZoneHelper::DATETIME_FORMAT)

        );


        return $change;
    }

    /**
     * @param $accountTariffId
     * @return array[changeIds, changes]
     */
    public static function getUnsaveChanges($accountTariffId): array
    {
        $changes = self::find()
            ->where([
                'account_tariff_id' => $accountTariffId,
                'is_published' => 0,
            ])
            ->orderBy([
                'id' => SORT_ASC
            ])
            ->all();

        $ids = array_map(fn(self $change) => $change->id, $changes);
        $data = array_map(fn(self $msg) => $msg->message
            + ['created_at_utc' => $msg->created_at]
            + ($msg->user_id && $msg->user_id != User::SYSTEM_USER_ID ? ['user_id' => $msg->user_id] : []),
            $changes
        );

        return [$ids, $data];
    }

    /**
     * @param int $accountTariffId
     * @param array[int] $changeIds
     * @return int
     */
    public static function setAsPublished($accountTariffId, $changeIds = [])
    {
        return self::updateAll(
            ['is_published' => 1],
            ['account_tariff_id' => $accountTariffId]
            + ($changeIds ? ['id' => $changeIds] : [])
        );
    }

    public static function isAddedService($changes)
    {
        return count(
                array_filter($changes, function ($msg) {
                    return $msg['action'] == 'service_on';
                })
            ) > 0;
    }
}
