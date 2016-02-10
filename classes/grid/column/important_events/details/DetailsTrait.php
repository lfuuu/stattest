<?php

namespace app\classes\grid\column\important_events\details;

use yii\helpers\Url;
use app\classes\Html;
use app\models\ClientAccount;
use app\models\User;

trait DetailsTrait
{

    public static function renderClientAccount($clientId)
    {
        $clientAccount = ClientAccount::findOne($clientId);

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

    public static function renderUser($userId)
    {
        $user = User::findOne($userId);

        if ($user === null) {
            return false;

        }

        return Html::tag('b', 'Создал: ') . $user->name;
    }

}