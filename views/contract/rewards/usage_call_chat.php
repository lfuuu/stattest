<?php

use yii\helpers\Url;
use kartik\widgets\ActiveForm;
use app\classes\Html;
use app\widgets\MonthPicker;
use app\models\ClientContractReward;
use app\models\Currency;
use app\forms\client\ContractEditForm;

/** @var ContractEditForm $contract */
/** @var ClientContractReward $model */
/** @var string $usageType */

$form = ActiveForm::begin([
    'action' => Url::toRoute(['contract/edit-rewards', 'contractId' => $contract->id, 'usageType' => $usageType]),
    'type' => ActiveForm::TYPE_VERTICAL,
]);

$rewards = $contract->getModel()->getRewards($usageType);
?>

<table class="table table-hover" width="100%">
    <colgroup>
        <col width="180" />
        <col width="180" />
        <col width="180" />
        <col width="200" />
        <col width="50" />
        <col width="510" />
    </colgroup>
    <thead>
        <tr>
            <th colspan="6" class="text-center"><?= ClientContractReward::$usages[$usageType] ?></th>
        </tr>
        <tr>
            <td>
                <?= $form
                    ->field($model, 'actual_from', [
                        'addon' => ['prepend' => ['content' => '<i class="glyphicon glyphicon-calendar"></i>']],
                    ])
                    ->widget(MonthPicker::class, [
                        'options' => [
                            'id' => $form->getId() . '-actual_from',
                            'class' => 'form-control',
                        ],
                        'widgetOptions' => [
                            'ShowIcon' => false,
                            'MonthFormat' => 'yy-mm',
                            'MinMonth' => '+1m',
                        ],
                    ])
                ?>
            </td>
            <td>
                <?= $form
                    ->field($model, 'once_only', [
                        'addon' => ['append' => ['content' => Currency::symbol(Currency::RUB)]],
                    ])
                ?>
            </td>
            <td>
                <?= $form
                    ->field($model, 'percentage_of_fee', [
                        'addon' => ['append' => ['content' => '%']],
                    ])
                ?>
            </td>
            <td>
                <?= $form
                    ->field($model, 'period_type')
                    ->dropDownList(ClientContractReward::$period)
                ?>
            </td>
            <td>
                <?= $form
                    ->field($model, 'period_month', ['options' => ['style' => 'width: 50px;']])
                    ->label('&nbsp;')
                ?>
            </td>
            <td class="text-right" style="vertical-align: middle;">
                <?= $this->render('//layouts/_submitButton', [
                    'text' => 'Зарегистрировать',
                    'params' => [
                        'class' => 'btn btn-primary',
                    ],
                ]) ?>
            </td>
        </tr>
        <?php if(count($rewards) > ClientContractReward::SHOW_LAST_REWARDS): ?>
            <tr>
                <td colspan="6" class="text-left">
                    <?= $this->render('//layouts/_link', [
                        'text' => 'Показать все (' . count($rewards) . ')',
                        'url' => 'javascript:void(0)',
                        'params' => [
                            'class' => 'label label-primary show-all',
                        ],
                    ]) ?>
                </td>
            </tr>
        <?php endif; ?>
    </thead>
    <tbody>
        <?php
        /** @var ClientContractReward $reward */
        $i = 0;
        foreach($rewards as $reward):
            $isShowed = ClientContractReward::SHOW_LAST_REWARDS >= ++$i;
            $actualFrom = substr($reward->actual_from, 0, 7);
            ?>
            <tr class="<?= ($reward->isEditable() ? 'editable' : '') ?><?= (!$isShowed ? ' show-all hidden' : '')?>">
                <td data-field="actual_from" data-value="<?= $actualFrom ?>">
                    <?= $actualFrom ?>
                    <?= ($reward->isEditable() ? $this->render('//layouts/_actionEdit', ['url' => 'javascript:void(0)']) : '') ?>
                </td>
                <td data-field="once_only"><?= $reward->once_only ?></td>
                <td data-field="percentage_of_fee"><?= $reward->percentage_of_fee ?></td>
                <td data-field="period_type" data-value="<?= $reward->period_type ?>">
                    <?= ClientContractReward::$period[$reward->period_type] ?>
                </td>
                <td data-field="period_month"><?= $reward->period_month ?></td>
                <td class="text-right">
                    <?php
                    if ($reward->user_id) {
                        echo
                            $reward->user->name .
                            Html::tag('br') .
                            Html::tag('small', '(' . $reward->insert_time . ')');
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach ;?>
    </tbody>
</table>

<?php ActiveForm::end() ?>