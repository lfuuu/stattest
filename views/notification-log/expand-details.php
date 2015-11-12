<?php

use app\classes\Html;
use yii\helpers\Url;
use app\models\ClientContact;

/** @var \app\models\ClientAccount $client */
/** @var \app\models\notifications\NotificationLog $notification */

$notifications = [];
foreach ($notification->contactLog as $record) {
    if (!($record->contact instanceof ClientContact)) {
        continue;
    }
    $notifications[] = Html::tag('a', $record->contact->data, ['href' => 'mailto:' . $record->contact->data]);
}

echo
    Html::beginTag('div', ['style' => 'float: left; width: 450px;']) .

        Html::tag('a', $client->contragent->name, [
            'href' => Url::toRoute(['/client/view', 'id' => $client->id]),
            'target' => '_blank',
            'style' => 'font-weight: bold;'
        ]) .

    Html::endTag('div') .
    Html::beginTag('div', ['style' => 'width: 100%;']) .
        (
            count($notifications)
                ?
                    Html::tag('b', 'Уведомление отправлено: ') .
                    Html::tag('span', implode(', ', $notifications))
                : ''
        ) .
    Html::endTag('div');