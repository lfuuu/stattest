<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use yii\widgets\MaskedInput;

/** @var $model \app\forms\external_operators\RequestOnlimeStbForm */
?>

<div class="well">
    <legend>Создание заявки</legend>

    <?php
    $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_VERTICAL,
        'options' => [
            'data-name' => $model->formName(),
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'fullname' => ['type' => Form::INPUT_TEXT],
            'address' => ['type' => Form::INPUT_TEXT],
            'phone' => [
                'type' => Form::INPUT_WIDGET,
                'widgetClass' => MaskedInput::className(),
                'options' => [
                    'mask' => '8 (999) 999-99-99',
                ],
            ],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'operator_name' => ['type' => Form::INPUT_TEXT],
            'partner' => ['type' => Form::INPUT_TEXT],
            'time_interval' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => $model->getTimeIntervals(),
            ],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 1,
        'attributes' => [
            'comment' => ['type' => Form::INPUT_TEXTAREA],
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
                    <?php if (array_key_exists('type', $product)): ?>
                        <?php if ($product['type'] == 'required_one'): ?>
                            <tr>
                                <td valign="middle" align="center">
                                    <input type="radio" name="required_one" value="<?= $product['id']; ?>"<?= (array_key_exists('is_default', $product) && $product['is_default'] === true ? ' checked="checked"' : '')?> />
                                </td>
                                <td valign="middle" class="product-item">
                                    <?= $product['nameFull']; ?>
                                </td>
                                <td></td>
                            </tr>
                        <?php endif; ?>
                    <?php else: ?>
                        <tr>
                            <td valign="middle" align="center">
                                <input type="checkbox" name="<?= $model->formName(); ?>[products][]" value="<?= $product['id']; ?>" class="request-product" />
                            </td>
                            <td valign="middle" class="product-item">
                                <?= $product['nameFull']; ?>
                            </td>
                            <td>
                                <input type="text" name="<?= $model->formName(); ?>[products_counts][]" value="1" size="10" disabled="disabled" class="form-control" style="height: 25px;" />
                            </td>
                        </tr>
                    <?php endif; ?>
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
    var products = $('input.request-product');

    if (products.length == 1) {
        var product = products.eq(0);
        product.prop('checked', true).on('click', function() { return false; });
        product.parent('td').next('td').addClass('product-item');
        product.parents('tr').find('td:eq(2)').find('input').prop('disabled', false);
    }
    else {
        $('input[type="checkbox"]')
            .on('click', function () {
                $(this).parent('td').next('td').toggleClass('product-item');
                $(this).parents('tr').find('td:eq(2)').find('input').prop('disabled', !$(this).is(':checked'));
            });
    }

    $('input[type="radio"][name="required_one"]')
        .on('change', function() {
            var form = $(this).parents('form'),
                product = $(form).find('div.' + $(this).attr('name'));

            if (!product.length) {
                product = $('<div />')
                    .addClass($(this).attr('name'))
                    .appendTo(form);
            }

            product.children().remove();

            $('<input />')
                .attr('type', 'hidden')
                .attr('name', form.data('name') + '[products][]')
                .val($(this).val())
                .appendTo(product);

            $('<input />')
                .attr('type', 'hidden')
                .attr('name', form.data('name') + '[products_counts][]')
                .val(1)
                .appendTo(product);
        })
        .filter(':checked')
        .trigger('change');
});
</script>