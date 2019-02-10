<?php

/** @var $invoice \app\models\Invoice  * */

use app\models\BillCorrection;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Главная', 'utl' => '/'],
        ['label' => 'Аккаунт: ' . $invoice->bill->client_id, 'url' => Url::to(['/client/view', 'id' => $invoice->bill->client_id])],
        ['label' => 'Счет №' . $invoice->bill->bill_no, 'url' => $invoice->bill->getUrl()],
        ['label' => 'Редактировать']
    ],
]);

$form = ActiveForm::begin();
?>

    <h2>Создание корректирующей проводки</h2>
    <div class="well">
        <div class="row">
            <div class="col-sm-6">
                <?= $form->field($invoice, 'number')->textInput(['readonly' => true]) ?>
            </div>

            <div class="col-sm-6">
                <?= $form->field($invoice, 'type_id')->dropDownList(BillCorrection::$typeList, ['disabled' => true]) ?>
            </div>
        </div>
    </div>
    <br>


    <table class="table table-condensed table-striped">
        <tr>
            <th width=1%>&#8470;</th>
            <th width=70%>Наименование</th>
            <th width=14%>Количество</th>
            <th width=15%>Цена</th>
            <th>
                Удаление
                <input type="checkbox" id="mark_del"
                       onchange="if (this.checked) $('input.mark_del').attr('checked','checked'); else $('input.mark_del').removeAttr('checked');"
                />
            </th>
        </tr>
        <?php
        /**
         * @var \app\models\InvoiceLine $line
         */
        foreach ($invoice->lines as $idx => $line) : ?>
            <tr>
                <td><?= $idx + 1 ?>.</td>
                <td><input class="form-control input-sm" value="<?= htmlspecialchars($line->item) ?>"
                           name=InvoiceLine[<?= $idx ?>][item]></td>
                <td><input class="form-control input-sm" value="<?= $line->amount ?>"
                           name=InvoiceLine[<?= $idx ?>][amount]></td>
                <td><input class="form-control input-sm" value="<?= $line->price ?>"
                           name=InvoiceLine[<?= $idx ?>][price]></td>
                <td><input type="checkbox" class="mark_del" name="delete[<?= $idx ?>]" value="<?= $idx ?>"/>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td>&nbsp;</td>
            <td>
                <input class="form-control input-sm" value="<?= htmlspecialchars($lineAdd->item) ?>"
                       name=InvoiceLineAdd[item]></td>
            <td>
                <input class="form-control input-sm" value="<?= $lineAdd->amount ?>" name=InvoiceLineAdd[amount]>
            </td>
            <td>
                <input class="form-control input-sm" value="<?= $lineAdd->price ?>" name=InvoiceLineAdd[price]>
            </td>
            <td>&nbsp;</td>
        </tr>

    </table>
    <div style="text-align: center">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $invoice->bill->getUrl()]) ?>
        <?= $this->render('//layouts/_submitButtonSave') ?>
    </div>

<?php ActiveForm::end() ?>