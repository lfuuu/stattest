<?php

use app\classes\grid\column\important_events\EventNameColumn;
use app\classes\grid\column\important_events\GroupColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\Language;
use app\modules\notifier\components\decorators\WhiteListEventDecorator;
use app\modules\notifier\forms\SchemesForm;
use app\modules\notifier\models\Schemes;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var \app\classes\BaseView $this */
/** @var \app\modules\notifier\forms\PersonalSchemeForm $dataForm */
/** @var \app\models\ClientAccountOptions $mailDeliveryLanguageOption */

echo Html::formLabel('Персональная схема оповещения');

echo Breadcrumbs::widget([
    'links' => [
        'Mailer',
        ['label' => 'Персональная схема оповещения', 'url' => Url::toRoute(['/notifier/personal-scheme'])],

    ],
]);

$baseView = $this;
$form = ActiveForm::begin();
$globalSchemeForm = new SchemesForm;
$personalScheme = $dataForm->loadScheme();
$globalScheme = $globalSchemeForm->getCountryNotificationScheme($dataForm->clientAccount->contragent->country->code);

echo GridView::widget([
    'dataProvider' => $dataForm->getAvailableEvents()->dataProvider,
    'columns' => [
        [
            'attribute' => 'code',
            'class' => EventNameColumn::class,
        ],
        [
            'attribute' => 'group_id',
            'label' => 'Группа',
            'class' => GroupColumn::class,
        ],
        [
            'label' => 'Email (официальный)',
            'format' => 'raw',
            'value' => function (WhiteListEventDecorator $data) use ($baseView, $personalScheme, $globalSchemeForm, $globalScheme) {
                return $baseView->render('_wtf-radio-group', [
                    'inputName' => 'FormData[events][' . $data->code . '][' . Schemes::NOTIFICATION_TYPE_EMAIL . ']',
                    'value' => isset($personalScheme[$data->code][Schemes::NOTIFICATION_TYPE_EMAIL]) ?
                        $personalScheme[$data->code][Schemes::NOTIFICATION_TYPE_EMAIL] :
                        null,
                    'globalValue' => $globalSchemeForm->isNotificationUsed($globalScheme, Schemes::NOTIFICATION_TYPE_EMAIL, $data->code),
                ]);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '8%',
        ],
        [
            'label' => 'Email (персональный)',
            'format' => 'raw',
            'value' => function (WhiteListEventDecorator $data) use ($personalScheme) {
                return Html::tag('span', '', [
                    'class' => 'glyphicon ' .
                        (
                            isset($personalScheme[$data->code][Schemes::NOTIFICATION_TYPE_EMAIL_PERSONAL])
                            && $personalScheme[$data->code][Schemes::NOTIFICATION_TYPE_EMAIL_PERSONAL] ?
                                'glyphicon-ok text-success' :
                                'glyphicon-remove text-danger'
                        ),
                ]);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'vAlign' => GridView::ALIGN_MIDDLE,
            'width' => '8%',
        ],
        [
            'label' => 'Sms (официальный)',
            'format' => 'raw',
            'value' => function (WhiteListEventDecorator $data) use ($baseView, $personalScheme, $globalSchemeForm, $globalScheme) {
                return $baseView->render('_wtf-radio-group', [
                    'inputName' => 'FormData[events][' . $data->code . '][' . Schemes::NOTIFICATION_TYPE_SMS . ']',
                    'value' => isset($personalScheme[$data->code][Schemes::NOTIFICATION_TYPE_SMS]) ?
                        $personalScheme[$data->code][Schemes::NOTIFICATION_TYPE_SMS] :
                        null,
                    'globalValue' => $globalSchemeForm->isNotificationUsed($globalScheme, Schemes::NOTIFICATION_TYPE_SMS, $data->code),
                ]);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '8%',
        ],
        [
            'label' => 'Sms (персональный)',
            'format' => 'raw',
            'value' => function (WhiteListEventDecorator $data) use ($personalScheme) {
                return Html::tag('span', '', [
                    'class' => 'glyphicon ' .
                        (
                            isset($personalScheme[$data->code][Schemes::NOTIFICATION_TYPE_SMS_PERSONAL])
                            && $personalScheme[$data->code][Schemes::NOTIFICATION_TYPE_SMS_PERSONAL] ?
                                'glyphicon-ok text-success' :
                                'glyphicon-remove text-danger'
                        ),
                ]);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'vAlign' => GridView::ALIGN_MIDDLE,
            'width' => '8%',
        ],
        [
            'label' => 'ЛК',
            'format' => 'raw',
            'value' => function (WhiteListEventDecorator $data) use ($personalScheme) {
                return Html::tag('span', '', [
                    'class' => 'glyphicon ' .
                        (
                            isset($personalScheme[$data->code][Schemes::NOTIFICATION_TYPE_LK])
                            && $personalScheme[$data->code][Schemes::NOTIFICATION_TYPE_LK] ?
                                'glyphicon-ok text-success' :
                                'glyphicon-remove text-danger'
                        ),
                ]);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'vAlign' => GridView::ALIGN_MIDDLE,
            'width' => '8%',
        ],
    ],
    'isFilterButton' => false,
    'floatHeader' => false,
    'panel' => [
        'type' => GridView::TYPE_DEFAULT,
        'before' =>
            Html::tag('div', 'Язык уведомлений', ['class' => 'col-sm-1 text-bold text-nowrap', 'style' => 'font-weight: bold; margin-right: 20px; min-width: 120px; margin-top: 7px;']) .
            Html::beginTag('div', ['class' => 'col-sm-2']) .
                Select2::widget([
                    'name' => 'FormData[language]',
                    'data' => Language::getList($isWithEmpty = true),
                    'value' => $mailDeliveryLanguageOption,
                ]) .
            Html::endTag('div') .
            $this->render('//layouts/_submitButtonSave', ['class' => 'pull-right']),
        'after' =>
            $this->render('//layouts/_submitButtonSave', ['class' => 'pull-right']) .
            Html::tag('div', '', ['class' => 'clearfix']),
    ],
    'export' => false,
]);

ActiveForm::end();
