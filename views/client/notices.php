<?php

use kartik\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use app\classes\Html;
use app\classes\grid\GridView;
use app\models\important_events\ImportantEventsNames;

echo Html::formLabel('Список подключенных событий');

echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Значимые события', 'url' => Url::toRoute(['/important_events/report'])],
        'Уведомления',
        'Список подключенных событий'
    ],
]);

/** @var \app\forms\important_events\filter\ImportantEventsNoticesFilter $formFilterModel */
/** @var \app\forms\important_events\ImportantEventsNoticesForm $formData */
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
                    echo $this->render('//layouts/_buttonCancel', ['url' => Url::toRoute('client/notices')]);
                }
                ?>
            </div>

        </form>
    </div>
</div>

<?php
$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
    'action' => Url::toRoute(['client/notices', 'clientAccountId' => $form->clientAccountId])
]);

echo GridView::widget([
    'dataProvider' => $formFilterModel->search($formData),
    'columns' => [
        [
            'class' => \app\classes\grid\column\important_events\EventNameColumn::class,
            'width' => '*',
        ],
        [
            'attribute' => 'group_id',
            'label' => 'Группа',
            'class' => \app\classes\grid\column\important_events\GroupColumn::class,
        ],
        [
            'attribute' => 'do_email',
            'label' => 'Email',
            'format' => 'raw',
            'value' => function($data) {
                $fieldName = 'FormData[events][' . $data['event'] . '][do_email]';

                return
                    Html::hiddenInput($fieldName, 0) .
                    Html::checkbox($fieldName, isset($data['do_email']) && $data['do_email']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '8%',
        ],
        [
            'label' => 'Sms',
            'format' => 'raw',
            'value' => function($data) {
                $fieldName = 'FormData[events][' . $data['event'] . '][do_sms]';

                return
                    Html::hiddenInput($fieldName, 0) .
                    Html::checkbox($fieldName, isset($data['do_sms']) && $data['do_sms']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '8%',
        ],
        [
            'label' => 'ЛК',
            'format' => 'raw',
            'value' => function($data) {
                $fieldName = 'FormData[events][' . $data['event'] . '][do_lk]';

                return
                    Html::hiddenInput($fieldName, 0) .
                    Html::checkbox($fieldName, isset($data['do_lk']) && $data['do_lk']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '8%',
        ],
    ],
    'pjax' => false,
    'bordered' => true,
    'striped' => true,
    'condensed' => true,
    'hover' => true,
    'isFilterButton' => false,
    'panel' => [
        'type' => GridView::TYPE_DEFAULT,
        'before' =>
            Html::tag('div', 'Язык уведомлений', ['class' => 'col-sm-1 text-bold text-nowrap', 'style' => 'font-weight: bold; margin-right: 20px; margin-top: 7px;']) .
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