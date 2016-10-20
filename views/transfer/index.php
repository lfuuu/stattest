<?php

use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use app\classes\Html;
use app\forms\transfer\ServiceTransferForm;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;

/** @var ClientAccount $clientAccount */
/** @var $model ServiceTransferForm */

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
                                    'item' => function($index, $label, $name, $checked, $value) {
                                        list($fulltext, $description) = (array)$label->description;

                                        return
                                            Html::beginTag('div', ['class' => 'checkbox']) .
                                                Html::beginTag('label') .
                                                    Html::checkbox($name, $checked, ['value' => $value]) .
                                                    Html::a(Html::tag('small', $value) . ': ' . $fulltext, $label->editLink,['target' => '_blank']) .
                                                    (
                                                        !empty($description)
                                                            ? Html::tag('div', $description, ['class' => 'help-block'])
                                                            : ''
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

                <?php if (!count($model->availableUsages)): ?>
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
                            'item' => function($index, $label, $name, $checked, $value) {
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
        <?= $this->render('//layouts/_submitButton', [
            'text' => 'Начать перенос',
            'glyphicon' => 'glyphicon-transfer',
            'params' => [
                'class' => 'btn btn-primary',
                'id' => 'transfer-btn',
                'disabled' => (!count($model->availableUsages) ? 'disabled' : ''),
            ]
        ]) ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
    </div>

    <?php ActiveForm::end() ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
    var
        $form = $('#<?= $form->id ?>');
        formName = '<?= $model->formName() ?>';

    $('a#transfer-select-all').on('click', function() {
        var $usages = $form.find('input[type="checkbox"]');
        $usages.prop('checked', !$usages.prop('checked'));
        $(this).toggleClass('label-success');
        return false;
    });

    $('input[name="' + formName + '[actual_custom]"]').on('focus', function() {
        $(this).prev('input').prop('checked', true);
    });

    $('input[name="target_account_search"]')
        .on('keydown', function(e) {
            if (e.keyCode === $.ui.keyCode.TAB && $(this).autocomplete('instance').menu.active) {
                e.preventDefault();
            }
            if (e.keyCode === $.ui.keyCode.ENTER) {
                $(this).blur();
            }
        })
        .on('focus', function() {
            $(this).prev('input').prop('checked', true);
        })
        .on('blur', function() {
            var value = $(this).val();
            if (value.length && value.test(/^[0-9]+$/)) {
                $('input[name="<?= $model->formName() ?>[target_account_id_custom]"]').val(value);
            }
        })
        .autocomplete({
            source: '/transfer/account-search?client_id=<?php echo $clientAccount->id ?>',
            minLength: 2,
            focus: function() {
                return false;
            },
            select: function(event, ui) {
                $('input[name="<?= $model->formName() ?>[target_account_id_custom]"]').val(ui.item.value);
                $(this).val(ui.item.label);
                return false;
            }
        })
        .data('autocomplete')._renderItem = function(ul, item) {
            return $('<li />')
                .data('item.autocomplete', item)
                .append('<a title="' + item.full + '">' + item.label + '</a>')
                .appendTo(ul);
        };
});
</script>

<style type="text/css">
.ui-autocomplete-loading {
    background: white url('images/ajax-loader-small.gif') right center no-repeat;
}
.ui-autocomplete {
    max-height: 145px;
    overflow-y: auto;
    overflow-x: hidden;
}
.ui-menu-item {
    white-space: nowrap;
}
</style>
