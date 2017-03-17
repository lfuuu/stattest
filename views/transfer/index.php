<?php

use app\classes\Html;
use app\forms\transfer\ServiceTransferForm;
use app\models\ClientAccount;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var ClientAccount $clientAccount */
/** @var $model ServiceTransferForm */
/** @var \app\classes\BaseView $this */

echo Html::formLabel('Перенос услуг');
echo Breadcrumbs::widget([
    'links' => [
        'Лицевой счет',
        ['label' => $clientAccount->contract->contragent->name, 'url' => $cancelUrl = Url::toRoute(['/client/view', 'id' => $clientAccount->id])],
        'Перенос услуг'
    ],
]);
?>

<div class="well">
    <?php
    $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_VERTICAL,
    ]);

    $this->registerJsVariables([
        'formId' => $form->getId(),
        'formName' => $model->formName(),
        'clientAccountId' => $clientAccount->id,
    ]);

    echo Html::activeHiddenInput($model, 'source_account_id');
    echo Html::activeHiddenInput($model, 'target_account_id_custom');
    ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="col-sm-4">
                <label>Услуги</label> <a href="javascript:void(0)" id="transfer-select-all" class="label label-primary" style="margin-left: 20px;">Выбрать все</a>

                <?php foreach ($model->availableUsages as $serviceKey => $serviceData): ?>
                    <fieldset>
                        <label class="label label-default"><?= $serviceData['title'] ?></label>
                        <?= $form
                            ->field($model, 'usages[' . $serviceKey . ']')
                            ->checkboxList(
                                $serviceData['usages'],
                                [
                                    'item' => function ($index, $label, $name, $checked, $value) {
                                        list($fulltext, $description) = (array)$label->description;

                                        return
                                            Html::beginTag('div', ['class' => 'checkbox']) .
                                                Html::beginTag('label') .
                                                    Html::checkbox($name, $checked, ['value' => $value]) .
                                                    Html::a(
                                                        Html::tag('small', $value) . ': ' . $fulltext,
                                                        $label->editLink,
                                                        ['target' => '_blank']
                                                    ) .
                                                    (
                                                        !empty($description) ?
                                                            Html::tag('div', $description, ['class' => 'help-block']) :
                                                            ''
                                                    ) .
                                                Html::endTag('label') .
                                            Html::endTag('div');
                                    },
                                ]
                            )
                            ->label(false)
                        ?>
                    </fieldset>
                <?php endforeach; ?>

                <?php if (!count($model->availableUsages)) : ?>
                    <div class="row" style="margin: 10px;">
                        <div class="col-sm-12 label label-danger">
                            Услуг для переноса не найдено
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'target_account_id')
                    ->label('Лицевой счет')
                    ->radioList(
                        $model->availableAccounts + [
                            'any' => Html::input('text', 'target_account_search', '', [
                                'class' => 'form-control',
                                'style' => 'padding: 0 0 0 1; height: auto; font-size: 12px;',
                                'placeholder' => 'Другой клиент',
                            ])
                        ],
                        [
                            'item' => function ($index, $label, $name, $checked, $value) {
                                return
                                    Html::beginTag('div', ['class' => 'radio']) .
                                        Html::beginTag('label', ['class' => 'col-sm-12']) .
                                            Html::radio($name, $checked, ['value' => $value]) . $label .
                                        Html::endTag('label') .
                                    Html::endTag('div');
                            },
                        ]
                    )
                ?>
            </div>
            <div class="col-sm-4">
                <?= $form
                    ->field($model, 'actual_from')
                    ->label('Дата переноса')
                    ->radioList($model->availableDates + [
                        'other' => DatePicker::widget([
                            'type' => DatePicker::TYPE_INPUT,
                            'name' => $model->formName() . '[actual_custom]',
                            'language' => 'ru',
                            'options' => [
                                'placeholder' => 'Другая дата',
                                'style' => 'padding: 0 0 0 1; height: auto; font-size: 12px;',
                            ],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'orientation' => 'bottom left',
                                'startDate' => 'today',
                            ],
                        ])
                    ]);
                ?>
            </div>
        </div>
    </div>

    <div class="form-group text-right">
        <?php
        $submitBtnParams = [
            'class' => 'btn btn-primary',
            'id' => 'transfer-btn',
        ];
        if (!count($model->availableUsages)) {
            $submitBtnParams['disabled'] = 'disabled';
        }
        ?>

        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton', [
            'text' => 'Начать перенос',
            'glyphicon' => 'glyphicon-transfer',
            'params' => $submitBtnParams,
        ]) ?>
    </div>

    <?php ActiveForm::end() ?>
</div>