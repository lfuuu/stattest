<?php

use app\classes\Html;
use yii\widgets\Breadcrumbs;
use app\classes\grid\GridView;
use yii\helpers\Url;
use kartik\widgets\ActiveForm;

echo Html::formLabel($this->title = 'Изменившиеся счета');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => $this->title, 'url' => '/monitoring/changed-bills/'],
        ['label' => ($month == 'current' ? 'Текущий месяц' : 'Предыдущий месяц'), 'url' => '/monitoring/changed-bills/'],
    ],
]);

$dataProvider = new \yii\data\ArrayDataProvider(
    [
        'allModels' => $data,
    ]
);

$form = ActiveForm::begin([
    'method' => 'get',
]);


?>

    <div class="row">
        <div class="col-md-3">
            <?= Html::a('Текущий месяц', '/monitoring/changed-bills?month=current', ['class' => 'btn btn-' . ($month == 'current' ? 'success' : 'info'), 'name' => 'current']) ?>
        </div>
        <div class="col-md-3">
            <?= Html::a('Предыдущий месяц', '/monitoring/changed-bills?month=previous', ['class' => 'btn btn-' . ($month != 'current' ? 'success' : 'info'), 'name' => 'previous']) ?>
        </div>
    </div>

<?php
ActiveForm::end();
?>
    <div style="clear: both;"></div>
<?php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'bill_no',
            'label' => 'номер счета',
            'format' => 'raw',
            'value' => function ($row) {
                return Html::a(
                    $row['bill_no'],
                    Url::toRoute([
                        '/',
                        'module' => 'newaccounts',
                        'action' => 'bill_view',
                        'bill' => $row['bill_no']
                    ]),
                    ['target' => '_blank']
                );
            }
        ],
        [
            'attribute' => 'client_id',
            'label' => '(У)ЛС',
            'format' => 'raw',
            'value' => function ($row) {
                return Html::a(
                    $row['client_id'],
                    Url::toRoute(['client/view', 'id' => $row['client_id']]),
                    ['target' => '_blank']
                );
            }
        ],
        [
            'attribute' => 'c1',
            'label' => 'до  2-го числа'
        ],
        [
            'attribute' => 'c2',
            'label' => 'после 2-го числа'
        ],
        [
            'label' => 'Разница',
            'value' => function ($row) {
                return round($row['c1'] - $row['c2'], 2);
            }
        ]

    ],
    'toolbar' => [],
    'panel' => false,
]);
