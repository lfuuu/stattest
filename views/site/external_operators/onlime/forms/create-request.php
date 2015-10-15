<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;

/** @var $model \app\forms\external_operators\RequestOnlimeForm */
?>

<div class="well">
    <legend>Создание заявки</legend>

    <?php
    $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'fullname' => ['type' => Form::INPUT_TEXT],
            'address' => ['type' => Form::INPUT_TEXT],
            'phone' => ['type' => Form::INPUT_TEXT],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'empty1' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    $form->field($model, 'account_id')->textInput() .
                    $form->field($model, 'time_interval')->dropDownList($model->getTimeIntervals()),
            ],
            'comment' => [
                'type' => Form::INPUT_TEXTAREA,
                'columnOptions' => [
                    'colspan' => 2,
                ],
                'options' => [
                    'rows' => 5,
                ],
            ],
        ],
    ]);

    ?>

    <div class="row" style="padding: 5px;">
        <label style="padding-left: 12px;">Выберите товары</label>
        <div class="col-sm-12">
            <?php if ($model->hasErrors('products')): ?>
                <div class="alert alert-danger" style="font-weight: bold; text-align: center;"><?= implode('', $model->getErrors('products')); ?></div>
            <?php endif; ?>
            <?php if ($model->hasErrors('1C_order')): ?>
                <div class="alert alert-danger" style="font-weight: bold; text-align: center;"><?= implode('', $model->getErrors('1C_error')); ?></div>
            <?php endif; ?>
            <table class="table table-hover table-condensed table-striped" width="60%">
                <colgroup>
                    <col width="40px" />
                    <col width="*" />
                    <col width="100px" />
                </colgroup>
                <tr>
                    <td style="background-color: #F0F0F0;"></td>
                    <td style="background-color: #F0F0F0;"><b>Наименование</b></td>
                    <td style="background-color: #F0F0F0;"><b>Количество</b></td>
                </tr>
                <?php foreach ($operator->products as $product): ?>
                    <tr>
                        <td valign="middle" align="center">
                            <input type="checkbox" name="<?= $model->formName(); ?>[products][]" value="<?= $product['id']; ?>" />
                        </td>
                        <td valign="middle" class="product-item">
                            <?= $product['nameFull']; ?>
                        </td>
                        <td>
                            <input type="text" name="<?= $model->formName(); ?>[products_counts][]" value="1" size="10" disabled="disabled" class="form-control" style="height: 25px;" />
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

    <?php
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    '<div class="col-md-12" style="text-align: right; padding-right: 0px;">' .
                        Html::submitButton('Создать', ['class' => 'btn btn-primary']) .
                    '</div>'
            ],
        ],
    ]);

    ActiveForm::end();
    ?>

</div>

<style type="text/css">
.product-item {
    color: #808080;
}
</style>

<script type="text/javascript">
jQuery(document).ready(function() {
    $('input[type="checkbox"]')
        .on('click', function() {
            $(this).parent('td').next('td').toggleClass('product-item');
            $(this).parents('tr').find('td:eq(2)').find('input').prop('disabled', !$(this).is(':checked'));
        });
});
</script>