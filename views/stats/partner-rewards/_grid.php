<?php
/**
 * Вознаграждения партнеров. Грид
 *
 * @var \app\classes\BaseView $this
 * @var PartnerRewardsFilter $filterModel
 */

use app\classes\grid\GridView;
use app\classes\Html;
use app\models\filter\PartnerRewardsFilter;

$baseView = $this;

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'beforeHeader' => [
        [
            'columns' => [
                ['content' => '', 'options' => ['rowspan' => 2]],
                ['content' => 'Наименование клиента', 'options' => ['rowspan' => 2]],
                ['content' => 'Дата регистрации клиента', 'options' => ['rowspan' => 2]],
                ['content' => 'Сумма оплаченных услуг', 'options' => ['rowspan' => 2]],
                ['content' => 'Сумма вознаграждения', 'options' => ['colspan' => 5]],
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
                return $row['paid_summary'];
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
            ],
        ]
    ],
    'resizableColumns' => false,
]);
