<?php

use kartik\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use app\classes\Html;
use app\classes\grid\GridView;
use app\models\important_events\ImportantEventsNames;

echo Html::formLabel('Персональная схема оповещения');

echo Breadcrumbs::widget([
    'links' => [
        'Mailer',
        ['label' => 'Персональная схема оповещения', 'url' => Url::toRoute(['/notifier/personal-scheme'])],

    ],
]);

/** @var \app\modules\notifier\forms\PersonalSchemeForm $formData */
/** @var \app\modules\notifier\filters\SchemeFilter $formFilterModel */
/** @var \app\models\ClientAccountOptions $mailDeliveryLanguageOption */
?>

<div class="row">
    <div class="col-sm-12">
        <form method="GET">

            <div style="float: right;">
                <?php
                $eventsList = [];

                foreach (ImportantEventsNames::find()->each() as $event) {
                    $eventsList[$event->group->title][$event->code] = $event->value;
                }

                echo \app\widgets\multiselect\MultiSelect::widget([
                    'model' => $formFilterModel,
                    'attribute' => 'event',
                    'data' => $eventsList,
                    'nonSelectedText' => '-- Событие --',
                    'options' => [
                        'multiple' => 'multiple',
                    ],
                    'clientOptions' => [
                        'buttonWidth' => '400px',
                        'enableCollapsibleOptGroups' => true,
                        'enableClickableOptGroups' => true,
                    ],
                ]);
                ?>

                <?= $this->render('//layouts/_submitButtonFilter') ?>
                <?php
                if (count($formFilterModel->event)) {
                    echo $this->render('//layouts/_buttonCancel', ['url' => Url::toRoute('/notifier/personal-scheme')]);
                }
                ?>
            </div>

        </form>
    </div>
</div>

<?php
$form = ActiveForm::begin();

echo GridView::widget([
    'dataProvider' => $formFilterModel->search($formData),
    'columns' => [
        [
            'class' => \app\classes\grid\column\important_events\EventNameColumn::class,
        ],
        [
            'attribute' => 'group_id',
            'label' => 'Группа',
            'class' => \app\classes\grid\column\important_events\GroupColumn::class,
        ],
        [
            'label' => 'Email (Мониторинг)',
            'format' => 'raw',
            'value' => function ($data) {
                $fieldName = 'FormData[events][' . $data['event'] . '][do_email_monitoring]';

                return
                    Html::hiddenInput($fieldName, 0) .
                    Html::checkbox($fieldName, isset($data['do_email_monitoring']) && $data['do_email_monitoring']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '5%',
        ],
        [
            'label' => 'Email (Оператор)',
            'format' => 'raw',
            'value' => function ($data) {
                $fieldName = 'FormData[events][' . $data['event'] . '][do_email_operator]';

                return
                    Html::hiddenInput($fieldName, 0) .
                    Html::checkbox($fieldName, isset($data['do_email_operator']) && $data['do_email_operator']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '5%',
        ],
        [
            'label' => 'Email (Официальный)',
            'format' => 'raw',
            'value' => function ($data) {
                $fieldName = 'FormData[events][' . $data['event'] . '][do_email]';

                return
                    Html::hiddenInput($fieldName, 0) .
                    Html::checkbox($fieldName, isset($data['do_email']) && $data['do_email']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '5%',
        ],
        [
            'label' => 'Email (Персональный)',
            'format' => 'raw',
            'value' => function ($data) {
                $fieldName = 'FormData[events][' . $data['event'] . '][do_email_personal]';

                return
                    Html::hiddenInput($fieldName, isset($data['do_email_personal']) && $data['do_email_personal']) .
                    Html::checkbox($fieldName, isset($data['do_email_personal']) && $data['do_email_personal'], ['disabled' => true]);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '5%',
        ],
        [
            'label' => 'Sms (Официальный)',
            'format' => 'raw',
            'value' => function ($data) {
                $fieldName = 'FormData[events][' . $data['event'] . '][do_sms]';

                return
                    Html::hiddenInput($fieldName, 0) .
                    Html::checkbox($fieldName, isset($data['do_sms']) && $data['do_sms']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '5%',
        ],
        [
            'label' => 'Sms (Персональный)',
            'format' => 'raw',
            'value' => function ($data) {
                $fieldName = 'FormData[events][' . $data['event'] . '][do_sms_personal]';

                return
                    Html::hiddenInput($fieldName, isset($data['do_sms_personal']) && $data['do_sms_personal']) .
                    Html::checkbox($fieldName, isset($data['do_sms_personal']) && $data['do_sms_personal'], ['disabled' => true]);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '5%',
        ],
        [
            'label' => 'ЛК',
            'format' => 'raw',
            'value' => function ($data) {
                $fieldName = 'FormData[events][' . $data['event'] . '][do_lk]';

                return
                    Html::hiddenInput($fieldName, 0) .
                    Html::checkbox($fieldName, isset($data['do_lk']) && $data['do_lk']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '5%',
        ],
    ],
    'isFilterButton' => false,
    'panel' => [
        'type' => GridView::TYPE_DEFAULT,
        'before' =>
            Html::tag('div', 'Язык уведомлений', ['class' => 'col-sm-1 text-bold text-nowrap', 'style' => 'font-weight: bold; margin-right: 20px; min-width: 120px; margin-top: 7px;']) .
            Html::beginTag('div', ['class' => 'col-sm-2']) .
                \kartik\widgets\Select2::widget([
                    'name' => 'FormData[language]',
                    'data' => \app\models\Language::getList($isWithEmpty = true),
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
