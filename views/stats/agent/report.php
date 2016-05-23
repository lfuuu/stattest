<?php

use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use kartik\daterange\DateRangePicker;
use kartik\widgets\Select2;
use app\classes\Html;
use app\classes\grid\GridView;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;

/** @var \app\models\filter\ClientAccountAgentFilter  $filterModel */

echo Html::formLabel('Отчет по партнерам (агентам)');

echo Breadcrumbs::widget([
    'links' => [
        'Статистика',
        ['label' => 'Отчет по партнерам (агентам)', 'url' => Url::toRoute('stats/agent/report')]
    ],
]);
?>

<div class="well" style="overflow-x: auto;">
    <div class="col-sm-12">
        <form method="GET">
            <table border="0" align="center" width="50%" cellpadding="5" cellspacing="5">
                <colgroup>
                    <col width="50%" />
                    <col width="50%" />
                </colgroup>
                <thead>
                    <tr>
                        <th><div style="margin-left: 15px; font-size: 12px;">Период</div></th>
                        <th><div style="margin-left: 15px; font-size: 12px;">Партнер</div></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="col-sm-12">
                                <?= DateRangePicker::widget([
                                    'name' => 'filter[date]',
                                    'value' =>
                                        $filterModel->date
                                            ?:
                                            (new DateTime('first day of previous month'))->format('Y-m-d') .
                                            ' - ' .
                                            (new DateTime('last day of previous month'))->format('Y-m-d'),
                                    'pluginOptions' => [
                                        'locale' => [
                                            'format' => 'YYYY-MM-DD',
                                            'separator'=>' - ',
                                        ],
                                    ],
                                    'containerOptions' => [
                                        'style' => 'overflow: hidden;',
                                        'class' => 'drp-container input-group',
                                    ],
                                ]);
                                ?>
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
        </form>
    </div>
</div>

<div class="col-sm-12" style="padding-bottom: 20px;">
    <?php if (count($filterModel->contractsWithoutReward)) { ?>
        <div class="col-sm-6 bg-danger">
            <fieldset style="padding: 5px;">
                <label>Отсутствуют настройки вознаграждений для договоров:</label>
                <ul>
                    <?php foreach($filterModel->contractsWithoutReward as $contract) { ?>
                        <li>
                            <a href="<?= Url::to(['contract/edit', 'id' => $contract['contract_id']]); ?>">
                                <?= $contract['contragent_name']; ?> (#<?= $contract['contract_id']; ?>)
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </fieldset>
        </div>
    <?php } ?>

    <?php if (count($filterModel->contractsWithIncorrectBP)) { ?>
        <div class="col-sm-6 bg-danger">
            <fieldset style="padding: 5px;">
                <label>Договора с неправильным бизнес-процессом:</label>
                <ul>
                    <?php foreach($filterModel->contractsWithIncorrectBP as $contract) { ?>
                        <li>
                            <a href="<?= Url::to(['contract/edit', 'id' => $contract['contract_id']]); ?>">
                                <?= $contract['contragent_name']; ?> (#<?= $contract['contract_id']; ?>)
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </fieldset>
        </div>
    <?php } ?>
</div>

<?= GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'beforeHeader' => [
        [
            'columns' => [
                ['content' => '', 'options' => ['rowspan' => 2],],
                ['content' => 'Наименование клиента', 'options' => ['rowspan' => 2],],
                ['content' => 'Дата регистрации клиента', 'options' => ['rowspan' => 2],],
                ['content' => 'Сумма оказанных услуг', 'options' => ['rowspan' => 2],],
                ['content' => 'Сумма оплаченных услуг', 'options' => ['rowspan' => 2],],
                ['content' => 'Сумма вознаграждения', 'options' => ['colspan' => 3],],
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

                foreach ($row['details'] as $transaction) {
                    /**  @var \app\models\usages\UsageInterface $usage */
                    $usage = null;

                    switch ($transaction['usage_type']) {
                        case 'voip':
                            $usage = UsageVoip::findOne($transaction['usage_id']);
                            break;
                        case 'vpbx':
                            $usage = UsageVirtpbx::findOne($transaction['usage_id']);
                            break;
                    }

                    $tableContent .=
                        Html::beginTag('tr') .
                            Html::tag('td',
                                Html::a(
                                    $transaction['bill_no'],
                                    Url::toRoute([
                                        '/index.php',
                                        'module' => 'newaccounts',
                                        'action' => 'bill_view',
                                        'bill' => $transaction['bill_no']]
                                    ), ['target' => '_blank']
                                ) .
                                Html::tag('span', ($transaction['is_payed'] == 1 ? ' (Оплачен)' : ''))
                            ) .
                            Html::tag('td', Html::a($transaction['name'], $usage->helper->editLink, ['target' => '_blank'])) .
                            Html::tag('td', Html::a($usage->tariff->helper->title, $usage->tariff->helper->editLink, ['target' => '_blank'])) .
                            Html::tag('td', $usage->activation_dt, ['class' => 'text-center']) .
                            Html::tag('td', $transaction['sum'], ['class' => 'text-center']) .
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
                return Html::a($row['contragent_name'], Url::toRoute(['client/view', 'id' => $row['client_id']]), ['target' => '_blank']);
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
                return $row['amount'];
            },
        ],
        [
            'headerOptions' => ['class' => 'hidden'],
            'format' => 'raw',
            'value' => function ($row) {
                return $row['amount_payed'];
            },
        ],
        [
            'label' => 'Разовое',
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['once'], 2);
            },
            'contentOptions' => ['class' => 'text-center',],
        ],
        [
            'label' => '% от абонентской платы',
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['fee'], 2);
            },
            'contentOptions' => ['class' => 'text-center',],
        ],
        [
            'label' => '% от превышения',
            'format' => 'raw',
            'value' => function ($row) {
                return number_format($row['excess'], 2);
            },
            'contentOptions' => ['class' => 'text-center',],
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
                    'content' => $filterModel->summary['amount'],
                ],
                [
                    'content' => $filterModel->summary['amount_payed'],
                ],
                [
                    'content' => number_format($filterModel->summary['once'], 2),
                ],
                [
                    'content' => number_format($filterModel->summary['fee'], 2),
                ],
                [
                    'content' => number_format($filterModel->summary['excess'], 2),
                ],
            ],
        ]
    ],
    'resizableColumns' => false,
]);