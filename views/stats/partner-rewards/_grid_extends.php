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
?>
    <div class="row">
        <div class="col-sm-12 text-left">
            <?= $this->render('_client_contract_rewards', [
                'filterModel' => $filterModel,
            ]); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 text-left">
            <div style="padding-left:15px;">
                <h2>Итого по начисленному вознаграждению: <?= PartnerRewardsFilter::getTotalSummary($filterModel); ?></h2>
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
            'hiddenFromExport' => false,
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
                return PartnerRewardsFilter::getNumberFormat($row['paid_summary']);
            },
        ],
        [
            'headerOptions' => ['class' => 'hidden'],
            'format' => 'raw',
            'value' => function ($row) {
                return PartnerRewardsFilter::getNumberFormat($row['paid_summary_reward']);
            },
        ],
        [
            'label' => 'Разовое',
            'format' => 'raw',
            'value' => function ($row) {
                return PartnerRewardsFilter::getNumberFormat($row['once']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'label' => '% от подключения',
            'format' => 'raw',
            'value' => function ($row) {
                return PartnerRewardsFilter::getNumberFormat($row['percentage_once']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'label' => '% от абонентской платы',
            'format' => 'raw',
            'value' => function ($row) {
                return PartnerRewardsFilter::getNumberFormat($row['percentage_of_fee']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'label' => '% от превышения',
            'format' => 'raw',
            'value' => function ($row) {
                return PartnerRewardsFilter::getNumberFormat($row['percentage_of_over']);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'label' => '% от маржи',
            'format' => 'raw',
            'value' => function ($row) {
                return PartnerRewardsFilter::getNumberFormat($row['percentage_of_margin']);
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
                    'content' => PartnerRewardsFilter::getNumberFormat($filterModel->summary['paid_summary']),
                ],
                [
                    'content' => PartnerRewardsFilter::getNumberFormat($filterModel->summary['paid_summary_reward']),
                ],
                [
                    'content' => PartnerRewardsFilter::getNumberFormat($filterModel->summary['once']),
                ],
                [
                    'content' => PartnerRewardsFilter::getNumberFormat($filterModel->summary['percentage_once']),
                ],
                [
                    'content' => PartnerRewardsFilter::getNumberFormat($filterModel->summary['percentage_of_fee']),
                ],
                [
                    'content' => PartnerRewardsFilter::getNumberFormat($filterModel->summary['percentage_of_over']),
                ],
                [
                    'content' => PartnerRewardsFilter::getNumberFormat($filterModel->summary['percentage_of_margin']),
                ],
            ],
        ]
    ],
    'resizableColumns' => false,
]);