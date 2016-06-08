<?php

namespace app\classes\grid\column\important_events\details;

use yii\helpers\Url;
use app\classes\Html;
use app\classes\Utils;
use app\models\ClientAccount;
use app\models\User;

abstract class DetailsHelper
{

    /**
     * @param int $clientId
     * @return bool|string
     */
    public static function renderClientAccount($clientId)
    {
        $clientAccount = ClientAccount::findOne(['id' => $clientId]);

        if ($clientAccount === null) {
            return false;
        }

        return
            Html::tag('b', 'Клиент: ') .
            Html::a(
                $clientAccount->contragent->name,
                Url::toRoute(['/client/view', 'id' => $clientAccount->id]),
                ['target' => '_blank']
            );
    }

    /**
     * @param int $userId
     * @return bool|string
     */
    public static function renderUser($userId)
    {
        $user = User::findOne(['id' => $userId]);

        if ($user === null) {
            return false;

        }

        return Html::tag('b', 'Создал: ') . $user->name;
    }

    /**
     * @param string $balance
     * @return string
     */
    public static function renderBalance($balance)
    {
        return Html::tag('b', 'Баланс: ') . $balance;
    }

    /**
     * @param string $value
     * @return string
     */
    public static function renderValue($value)
    {
        return Html::tag('b', 'Значение на момент события: ') . $value;
    }

}