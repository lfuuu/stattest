<?php

namespace app\modules\uu\models;

use app\classes\behaviors\CreatedAt;
use app\classes\model\ActiveRecord;
use app\classes\Utils;
use app\exceptions\ModelValidationException;
use app\models\User;
use yii\behaviors\BlameableBehavior;

/**
 * @property int $id
 * @property string $created_at
 * @property int $account_tariff_id
 * @property int $client_account_id
 * @property string $message
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

        return $change;
    }

    public static function getUnsaveChanges($accountTariffId)
    {
        return array_map(function (self $msg) {
            return $msg->message
                + ['created_at_utc' => $msg->created_at]
                + ($msg->user_id && $msg->user_id != User::SYSTEM_USER_ID ? ['user_id' => $msg->user_id] : [])
                ;
        }, self::find()
            ->where([
                'account_tariff_id' => $accountTariffId,
                'is_published' => 0,
            ])
            ->orderBy([
                'id' => SORT_ASC
            ])
            ->all()
        );
    }

    public static function setAsPublished($accountTariffId)
    {
        return self::updateAll(
            ['is_published' => 1],
            ['account_tariff_id' => $accountTariffId]
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
