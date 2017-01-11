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

echo Html::formLabel('Отчет: Вознаграждения партнерам');

echo Breadcrumbs::widget([
    'links' => [
        'Статистика',
        ['label' => 'Отчет: Вознаграждения партнерам', 'url' => $baseUrl = Url::toRoute('stats/partner-rewards')]
    ],
]);
?>

<div class="well" style="overflow-x: auto;">

    <?= $this->render('incorrect-data', ['filterModel' => $filterModel]) ?>

    <div class="col-sm-12">
        <?php
        $form = ActiveForm::begin(['method' => 'get', 'action' => $baseUrl])
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
                                    'name' => 'filter[month]',
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
                                    'data' => $filterModel->partnersList,
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
    echo $this->render(($filterModel->isExtendsMode ? '_grid_extends' : '_grid'),
        [
            'filterModel' => $filterModel,
        ]
    );
}