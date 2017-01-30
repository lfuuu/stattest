<?php

use kartik\widgets\ActiveForm;
use kartik\tabs\TabsX;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\classes\grid\GridView;
use app\classes\Html;
/**
 * @var \app\modules\notifier\forms\ControlForm $dataForm
 */

echo Html::formLabel('Управление оповещениями');

echo Breadcrumbs::widget([
    'links' => [
        'Mailer',
        ['label' => 'Управление оповещениями', 'url' => Url::toRoute(['/notifier/control'])],
    ],
]);
?>

<div class="well">
    <?php
    $tabs = [];

    foreach ($dataForm->getAvailableCountries() as $country) {
        $tabs[] = [
            'label' => $country->name,
            'content' => $this->render('_country',
                [
                    'dataForm' => $dataForm,
                    'countryCode' => $country->code,
                ]
            ),
        ];
    }

    echo TabsX::widget([
        'items' => $tabs,
        'position' => TabsX::POS_ABOVE,
        'bordered' => true,
        'encodeLabels' => false,
    ]);

    ?>
</div>

<?php
$form = ActiveForm::begin([
    'action' => '/notifier/control/apply-white-list'
]);

echo GridView::widget([
    'dataProvider' => $dataForm->getAvailableEvents(),
    'columns' => [
        [
            'attribute' => 'code',
            'label' => 'Код',
            'format' => 'raw',
            'value' => function ($data) {
                return Html::a($data['title'], ['/important_events/names/edit', 'id' => $data['id']]);
            },
        ],
        [
            'attribute' => 'group_id',
            'label' => 'Группа',
            'class' => \app\classes\grid\column\important_events\GroupColumn::class,
        ],
        [
            'format' => 'raw',
            'label' => 'Использование',
            'value' => function ($data) use ($dataForm) {
                return Html::checkbox(
                    $dataForm->formName() . '[whitelist][' . $data['code'] .']',
                    $data['in_use']
                );
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'format' => 'raw',
            'label' => 'Установлено',
            'value' => function ($data) {
                return $data['in_use'] ?: 'Не установлено';
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
    ],
    'floatHeader' => true,
    'isFilterButton' => false,
    'export' => false,
    'toggleData' => false,
    'extraButtons' => $this->render('//layouts/_submitButtonSave'),
    'panel' => [
        'heading' =>
            Html::beginTag('div', ['class' => 'text-center']) .
            'Обновлено: ' . (string)$dataForm->getWhitelistLastPublishData() .
            Html::endTag('div'),
    ],
]);

ActiveForm::end();