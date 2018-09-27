<?php

use app\classes\Html;
use app\models\BillCorrection;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Главная', 'utl' => '/'],
        ['label' => 'Аккаунт: ' . $bill->client_id, 'url' => Url::to(['/client/view', 'id' => $bill->client_id])],
        ['label' => 'Счет №' . $bill->bill_no, 'url' => $bill->getUrl()],
        ['label' => 'Редактировать']
    ],
]);

$form = ActiveForm::begin();
?>

    <h2>Создание корректирующей проводки</h2>
    <div class="well">
        <div class="row">
            <div class="col-sm-2">
                <?= $form->field($billCorrection, 'date')->widget(\kartik\widgets\DatePicker::class,
                    [
                        'removeButton' => false,
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                        ]
                    ]
                ) ?>
            </div>

            <div class="col-sm-5">
                <?= $form->field($billCorrection, 'number')->textInput(['readonly' => true]) ?>
            </div>

            <div class="col-sm-5">
                <?= $form->field($billCorrection, 'type_id')->dropDownList(BillCorrection::$typeList, ['disabled' => true]) ?>
            </div>
        </div>
    </div>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $bill->getUrl()]) ?>
        <?= $this->render('//layouts/_submitButtonSave') ?>
        <?= $billCorrection->id ? $this->render('//layouts/_submitButtonDrop') : '' ?>
    </div>
    <br>

<?php if ($billCorrection->id) { ?>

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
        <?php foreach ($billCorrection->lines as $idx => $line) : ?>
            <tr>
                <td><?= $idx + 1 ?>.</td>
                <td><input class="form-control input-sm" value="<?= htmlspecialchars($line->item) ?>"
                           name=BillLineCorrection[<?= $idx ?>][item]></td>
                <td><input class="form-control input-sm" value="<?= $line->amount ?>"
                           name=BillLineCorrection[<?= $idx ?>][amount]></td>
                <td><input class="form-control input-sm" value="<?= $line->price ?>"
                           name=BillLineCorrection[<?= $idx ?>][price]></td>
                <td><input type="checkbox" class="mark_del" name="delete[<?= $idx ?>]" value="<?= $idx ?>"/>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td>&nbsp;</td>
            <td><input class="form-control input-sm" value="<?= htmlspecialchars($lineAdd->item) ?>"
                       name=BillLineCorrectionAdd[item]></td>
            <td><input class="form-control input-sm" value="<?= $lineAdd->amount ?>" name=BillLineCorrectionAdd[amount]>
            </td>
            <td><input class="form-control input-sm" value="<?= $lineAdd->price ?>" name=BillLineCorrectionAdd[price]>
            </td>
            <td>&nbsp;</td>
        </tr>

    </table>
    <div style="text-align: center">
        <?= $this->render('//layouts/_submitButtonSave') ?>
    </div>

<?php } ?>
<?php ActiveForm::end() ?>