<?php

use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\notifier\components\decorators\WhiteListEventDecorator;
use app\modules\notifier\models\Schemes;

/**
 * @var \app\modules\notifier\forms\SchemesForm $dataForm
 * @var int $countryCode
 */

$notificationScheme = $dataForm->getCountryNotificationScheme($countryCode);

echo $this->render('//layouts/_submitButtonSave', ['class' => 'pull-right', 'style' => 'clear: both; margin-bottom: 20px;',]);

echo GridView::widget([
    'dataProvider' => $dataForm->getAvailableEvents()->dataProvider,
    'columns' => [
        [
            'attribute' => 'code',
            'label' => 'Код',
            'format' => 'raw',
            'value' => function (WhiteListEventDecorator $data) {
                return $data->editLink;
            },
        ],
        [
            'attribute' => 'group_id',
            'label' => 'Группа',
            'class' => \app\classes\grid\column\important_events\GroupColumn::class,
        ],
        [
            'label' => 'Email (мониторинг)',
            'format' => 'raw',
            'value' => function (WhiteListEventDecorator $data) use ($dataForm, $countryCode, $notificationScheme) {
                $notificationType = Schemes::NOTIFICATION_TYPE_EMAIL_MONITORING;
                $fieldName = 'formData[' . $countryCode . '][' . $data->code . '][' . $notificationType . ']';

                return
                    Html::hiddenInput($fieldName, 0) .
                    Html::checkbox($fieldName, $dataForm->isNotificationUsed($notificationScheme, $notificationType, $data->code));
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '5%',
        ],
        [
            'label' => 'Email (оператор)',
            'format' => 'raw',
            'value' => function (WhiteListEventDecorator $data) use ($dataForm, $countryCode, $notificationScheme) {
                $notificationType = Schemes::NOTIFICATION_TYPE_EMAIL_OPERATOR;
                $fieldName = 'formData[' . $countryCode . '][' . $data->code . '][' . $notificationType . ']';

                return
                    Html::hiddenInput($fieldName, 0) .
                    Html::checkbox($fieldName, $dataForm->isNotificationUsed($notificationScheme, $notificationType, $data->code));
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '5%',
        ],
        [
            'label' => 'Email (официальный)',
            'format' => 'raw',
            'value' => function (WhiteListEventDecorator $data) use ($dataForm, $countryCode, $notificationScheme) {
                $notificationType = Schemes::NOTIFICATION_TYPE_EMAIL;
                $fieldName = 'formData[' . $countryCode . '][' . $data->code . '][' . $notificationType . ']';

                return
                    Html::hiddenInput($fieldName, 0) .
                    Html::checkbox($fieldName, $dataForm->isNotificationUsed($notificationScheme, $notificationType, $data->code));
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '5%',
        ],
        [
            'label' => 'Sms (официальный)',
            'format' => 'raw',
            'value' => function (WhiteListEventDecorator $data) use ($dataForm, $countryCode, $notificationScheme) {
                $notificationType = Schemes::NOTIFICATION_TYPE_SMS;
                $fieldName = 'formData[' . $countryCode . '][' . $data->code . '][' . $notificationType . ']';

                return
                    Html::hiddenInput($fieldName, 0) .
                    Html::checkbox($fieldName, $dataForm->isNotificationUsed($notificationScheme, $notificationType, $data->code));
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '5%',
        ],
    ],
    'floatHeader' => false,
    'isFilterButton' => false,
    'export' => false,
    'panel' => '',
]);