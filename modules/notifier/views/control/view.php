<?php

use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\notifier\components\decorators\WhiteListEventDecorator;
use kartik\tabs\TabsX;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

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

        foreach ($dataForm->getAvailableCountries() as $countryCode => $countryName) {
            $tabs[] = [
                'label' => $countryName,
                'content' => $this->render('_country',
                    [
                        'dataForm' => $dataForm,
                        'countryCode' => $countryCode,
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

$whiteList = $dataForm->getWhiteList();

if (!count($whiteList->log)) {
    $whiteListUpdated = Html::tag('b', 'Данные об обновлении отсутствуют');
} else {
    $firstRecord = reset($whiteList->prettyLog);
    $whiteListUpdated =
        Html::tag('b', 'Обновлено: ') .
        $firstRecord->userName . ' (' . $firstRecord->updated . ')' . Html::tag('br') .
        Html::tag('div', $firstRecord->message, ['class' => 'label label-danger']);
}

echo GridView::widget([
    'dataProvider' => $whiteList->dataProvider,
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
            'format' => 'raw',
            'label' => 'Использование',
            'value' => function (WhiteListEventDecorator $data) use ($dataForm) {
                return Html::checkbox(
                    $dataForm->formName() . '[whitelist][' . $data->code . ']',
                    $data->isActive
                );
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'format' => 'raw',
            'label' => 'Установлено',
            'value' => function (WhiteListEventDecorator $data) {
                return $data->isActive ?: 'Не установлено';
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
            $whiteListUpdated .
            Html::endTag('div'),
    ],
]);

ActiveForm::end();