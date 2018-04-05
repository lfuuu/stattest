<?php
/**
 * Вознаграждения партнеров. Подробный грид
 *
 * @var \app\classes\BaseView $this
 * @var PartnerRewardsFilter $filterModel
 */

use app\classes\grid\GridView;
use app\classes\Html;
use app\models\filter\PartnerRewardsFilter;

$baseView = $this;
// Создаем dataProvider раньше, потому что уже необходимо отображать первую статистику
$dataProvider = $filterModel->search();
// Анонимная функция расчета итоговых значений по вознаграждениям
$totalCountingFunction = function ($type = '') use ($filterModel){
    $total = 0;
    $attribute = $type === '' ? 'summary' : 'possibleSummary';
    foreach ([
        'once', 'percentage_once', 'percentage_of_fee', 'percentage_of_over' , 'percentage_of_margin'
    ] as $key) {
        # Диалектика языка по подсчету итогового значения корректно работает на PHPv5
        # http://php.net/manual/en/migration70.incompatible.php#migration70.incompatible.variable-handling.indirect
        $total += $filterModel->{$attribute}[$key];
    }
    return number_format($total, 2);
}
?>
<div class="row">
    <div class="col-sm-6 text-left">
        <div style="padding-left:15px;">
            <h2>Итого по начисленному вознаграждению: <?=  $totalCountingFunction(); ?></h2>
            <h3>Рассчетная сумма вознаграждения по неоплаченным счетам: <?= $totalCountingFunction('possible') ?></h3>
        </div>
    </div>
</div>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'beforeHeader' => [
        [
            'columns' => [
                ['content' => '', 'options' => ['rowspan' => 2],],
                ['content' => 'Наименование клиента', 'options' => ['rowspan' => 2],],
                ['content' => 'Дата регистрации клиента', 'options' => ['rowspan' => 2],],
                ['content' => 'Сумма оплаченных счетов', 'options' => ['rowspan' => 2],],
                ['content' => 'Сумма оплаченных услуг, за которые начисленно вознаграждение', 'options' => ['rowspan' => 2],],
                ['content' => 'Сумма вознаграждения', 'options' => ['colspan' => 5],],
                ['content' => 'Сумма неоплаченных счетов', 'options' => ['rowspan' => 2],],
                ['content' => 'Сумма неоплаченных услуг, за которые начисленно вознаграждение', 'options' => ['rowspan' => 2],],
                ['content' => 'Расчетная сумма вознаграждения (по неоплаченным счетам)', 'options' => ['colspan' => 5],],
            ],
            'options' => [
                'class' => GridView::DEFAULT_HEADER_CLASS,
            ],
        ],
    ],
    'columns' => [
        [
            'class' => 'kartik\grid\ExpandRowColumn',
            'width' => '50px',
            'value' => function () {
                return GridView::ROW_COLLAPSED;
            },
            'detail' => function ($row) use ($baseView, $filterModel) {
                return $baseView->render('_details', [
                    'isExtendsMode' => $filterModel->isExtendsMode,
                    'details' => $row['details'],
                ]);
            },
            'headerOptions' => ['class' => 'hidden kartik-sheet-style'],
        ],
        [
            'headerOptions' => ['class' => 'hidden'],
            'format' => 'raw',
            'value' => function ($row) {
                return Html::a(
                    $row['contragent_name'],
                    ['client/view', 'id' => $row['client_id']],
                    ['target' => '_blank']
                );
            },
        ],
        [
            'headerOptions' => ['class' => 'hidden'],
            'format' => 'raw',
            'value' => function ($row) {
                return $row['client_created'];
            },
        ],
        [
            'headerOptions' => ['class' => 'hidden'],
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['paid_summary'], 2);
            },
        ],
        [
            'headerOptions' => ['class' => 'hidden'],
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['paid_summary_reward'], 2);
            },
        ],
        [
            'label' => 'Разовое',
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['once'], 2);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'label' => '% от подключения',
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['percentage_once'], 2);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'label' => '% от абонентской платы',
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['percentage_of_fee'], 2);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'label' => '% от превышения',
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['percentage_of_over'], 2);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'label' => '% от маржи',
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['percentage_of_margin'], 2);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'headerOptions' => ['class' => 'hidden'],
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['possible_paid_summary'], 2);
            },
        ],
        [
            'headerOptions' => ['class' => 'hidden'],
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['possible_paid_summary_reward'], 2);
            },
        ],
        [
            'label' => 'Разовое',
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['possible_once'], 2);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'label' => '% от подключения',
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['possible_percentage_once'], 2);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'label' => '% от абонентской платы',
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['possible_percentage_of_fee'], 2);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'label' => '% от превышения',
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['possible_percentage_of_over'], 2);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'label' => '% от маржи',
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['possible_percentage_of_margin'], 2);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
    ],
    'afterHeader' => [
        [
            'options' => ['class' => \kartik\grid\GridView::TYPE_WARNING],
            'columns' => [
                [
                    'content' => '',
                ],
                [
                    'content' => Yii::t('common', 'Summary'),
                    'options' => ['colspan' => 2, 'class' => 'text-left'],
                ],
                [
                    'content' => number_format($filterModel->summary['paid_summary'], 2),
                ],
                [
                    'content' => number_format($filterModel->summary['paid_summary_reward'], 2),
                ],
                [
                    'content' => number_format($filterModel->summary['once'], 2),
                ],
                [
                    'content' => number_format($filterModel->summary['percentage_once'], 2),
                ],
                [
                    'content' => number_format($filterModel->summary['percentage_of_fee'], 2),
                ],
                [
                    'content' => number_format($filterModel->summary['percentage_of_over'], 2),
                ],
                [
                    'content' => number_format($filterModel->summary['percentage_of_margin'], 2),
                ],
                [
                    'content' => number_format($filterModel->possibleSummary['paid_summary'], 2),
                ],
                [
                    'content' => number_format($filterModel->possibleSummary['paid_summary_reward'], 2),
                ],
                [
                    'content' => number_format($filterModel->possibleSummary['once'], 2),
                ],
                [
                    'content' => number_format($filterModel->possibleSummary['percentage_once'], 2),
                ],
                [
                    'content' => number_format($filterModel->possibleSummary['percentage_of_fee'], 2),
                ],
                [
                    'content' => number_format($filterModel->possibleSummary['percentage_of_over'], 2),
                ],
                [
                    'content' => number_format($filterModel->possibleSummary['percentage_of_margin'], 2),
                ],
            ],
        ]
    ],
    'resizableColumns' => false,
]);