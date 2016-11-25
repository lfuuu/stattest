<?php

use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use kartik\widgets\Select2;
use app\widgets\MonthPicker;
use app\classes\Html;
use app\classes\grid\GridView;
use app\classes\partners\RewardCalculate;
use app\classes\partners\RewardsInterface;
use app\models\filter\PartnerRewardsFilter;

/** @var PartnerRewardsFilter $filterModel */
/** @var array $partnerList */

echo Html::formLabel('Отчет: Вознаграждения партнерам');

echo Breadcrumbs::widget([
    'links' => [
        'Статистика',
        ['label' => 'Отчет: Вознаграждения партнерам', 'url' => Url::toRoute('stats/partner-rewards')]
    ],
]);
?>

<div class="well" style="overflow-x: auto;">

    <?= $this->render('incorrect-data', ['filterModel' => $filterModel]) ?>

    <div class="col-sm-12">
        <?php
        $form = ActiveForm::begin(['method' => 'get',])
        ?>
            <table border="0" align="center" width="50%" cellpadding="5" cellspacing="5">
                <colgroup>
                    <col width="50%" />
                    <col width="50%" />
                </colgroup>
                <thead>
                    <tr>
                        <th><div style="margin-left: 15px; font-size: 12px;">Отчетный месяц</div></th>
                        <th><div style="margin-left: 15px; font-size: 12px;">Партнер</div></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="col-sm-12">
                                <?= MonthPicker::widget([
                                    'name' => 'month',
                                    'value' => $filterModel->month,
                                    'options' => [
                                        'class' => 'form-control input-sm',
                                    ],
                                ]) ?>
                            </div>
                        </td>
                        <td>
                            <div class="col-sm-12">
                                <?= Select2::widget([
                                    'name' => 'filter[partner_contract_id]',
                                    'data' => $partnerList,
                                    'value' => $filterModel->partner_contract_id,
                                    'options' => [
                                        'placeholder' => '-- Выбрать партнера --',
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                    ],
                                ]);
                                ?>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" align="center">
                            <br />
                            <?php
                            echo Html::submitButton('Сформировать', ['class' => 'btn btn-primary',]);
                            ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?
if ($filterModel->partner_contract_id) {
    echo GridView::widget([
        'dataProvider' => $filterModel->search(),
        'filterModel' => $filterModel,
        'beforeHeader' => [
            [
                'columns' => [
                    ['content' => '', 'options' => ['rowspan' => 2],],
                    ['content' => 'Наименование клиента', 'options' => ['rowspan' => 2],],
                    ['content' => 'Дата регистрации клиента', 'options' => ['rowspan' => 2],],
                    ['content' => 'Сумма оплаченных услуг', 'options' => ['rowspan' => 2],],
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
                'value' => function () {
                    return GridView::ROW_COLLAPSED;
                },
                'detail' => function ($row) {
                    $tableContent = '';

                    foreach ($row['details'] as $record) {
                        /**  @var RewardsInterface $usageClass */
                        $usageClass = RewardCalculate::$services[$record['usage_type']];
                        $usage = $usageClass::getUsage($record['usage_id'], $record['account_version']);
                        if (!$usage) {
                            return '';
                        }

                        $tableContent .=
                            Html::beginTag('tr') .
                            Html::tag('td',
                                Html::a(
                                    $record['bill_no'],
                                    Url::toRoute([
                                            '/index.php',
                                            'module' => 'newaccounts',
                                            'action' => 'bill_view',
                                            'bill' => $record['bill_no']
                                        ]
                                    ), ['target' => '_blank']
                                )
                            ) .
                            Html::tag(
                                'td',
                                Html::a($usage->helper->description[0], $usage->helper->editLink, ['target' => '_blank'])
                            ) .
                            Html::tag(
                                'td',
                                Html::a(
                                    $usage->tariff->helper->title,
                                    $usage->tariff->helper->editLink,
                                    ['target' => '_blank']
                                )
                            ) .
                            Html::tag('td', $usage->activation_dt, ['class' => 'text-center']) .
                            Html::tag('td', $record['paid_summary'], ['class' => 'text-center']) .
                            Html::endTag('tr');
                    }

                    return
                        Html::beginTag('table', ['class' => 'table table-hover table-bordered table-striped']) .
                            Html::beginTag('colgroup') .
                                Html::tag('col', '', ['width' => '200']) .
                                Html::tag('col', '', ['width' => '*']) .
                                Html::tag('col', '', ['width' => '20%']) .
                                Html::tag('col', '', ['width' => '200']) .
                                Html::tag('col', '', ['width' => '200']) .
                            Html::endTag('colgroup') .

                            Html::beginTag('thead') .
                                Html::tag('th', 'Счет') .
                                Html::tag('th', 'Услуга') .
                                Html::tag('th', 'Тариф') .
                                Html::tag('th', 'Дата включения услуги') .
                                Html::tag('th', 'Сумма оказанных услуг') .
                            Html::endTag('thead') .

                            Html::beginTag('tbody') .
                                $tableContent .
                            Html::endTag('tbody') .
                        Html::endTag('table');
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
                'columns' => $summaryColumns = [
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
}