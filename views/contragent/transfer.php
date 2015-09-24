<?php

use yii\helpers\Url;
use kartik\widgets\ActiveForm;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use app\forms\contragent\ContragentTransferForm;

/** @var $model ContragentTransferForm */

$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
]);
?>

    <table border="0" align="center" width="95%">
        <thead>
            <tr>
                <th>
                    <h2>Контрагент - <?= $contragent->name; ?></h2>
                    <hr size="1" style="margin: 5px;" />
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td valign="middle">

                    <div style="overflow-y: scroll; height: 230px; max-height: 230px; background-color: #F0F0F0;">
                        <div class="col-sm-12">

                            <div class="row contragent-wrap" style="padding-top: 10px; padding-bottom: 10px;">
                                <div class="col-sm-12">
                                    <?php
                                    $contracts = $contragent->contracts;
                                    foreach ($contracts as $contract): ?>
                                        <div class="row" style="margin-left: 0px;">
                                            <div class="col-sm-6">
                                                <input type="hidden" name="<?= $model->formName(); ?>[contracts][]" value="<?= $contract->id; ?>" />
                                                <a href="<?= Url::toRoute(['contract/edit', 'id' => $contract->id, 'childId' => $account->id]) ?>" target="_blank">
                                                    <span class="c-blue-color">
                                                        Договор № <?= ($contract->number ? $contract->number : 'Без номера'); ?>
                                                        (<?= $contract->organization->name; ?>)
                                                    </span>
                                                </a>
                                            </div>
                                            <div class="col-sm-2" style="white-space: nowrap;">
                                                <span><?= $contract->business; ?> / <?= $contract->businessProcessStatus->name; ?></span>
                                            </div>
                                            <div class="col-sm-4">
                                                <?php if ($contract->managerName) : ?>
                                                    <span style="float:left;background: <?= $contract->managerColor ?>;">
                                                        М: <?= $contract->managerName ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($contract->accountManagerName) : ?>
                                                    <span style="float:right;background: <?= $contract->accountManagerColor ?>;">
                                                        Ак.М: <?= $contract->accountManagerName ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-sm-12">
                                                <div style="padding-left: 10px; float: left;">&nbsp;</div>
                                                <?php foreach ($contract->accounts as $ck => $contractAccount): ?>
                                                    <div class="row row-ls">
                                                        <span class="col-sm-2" style="font-weight: bold; color:<?= ($contractAccount->is_active) ? 'green' : 'black' ?>;">
                                                            <input type="hidden" name="<?= $model->formName(); ?>[clients][]" value="<?= $contractAccount->id; ?>" />
                                                            <a href="/client/view?id=<?= $contractAccount->id; ?>" target="_blank">ЛС № <?= $contractAccount->id; ?></a>
                                                        </span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                        </div>
                    </div>

                    <br />
                    <?php
                    echo $form->field($model, 'targetClientAccount')->widget(Select2::className(), [
                        'options' => [
                            'placeholder' => 'Выберите супер клиента',
                        ],
                        'pluginOptions' => [
                            'allowClear' => false,
                            'minimumInputLength' => 1,
                            'ajax' => [
                                'url' => '/account/super-client-search',
                                'dataType' => 'json',
                                'data' => new JsExpression('function(params) { return {query:params}; }'),
                                'results' => new JsExpression('function (data) { console.log(data); return {results: data}; }'),
                            ],
                        ],
                    ])->label('Переместить к');

                    echo $form->field($model, 'sourceClientAccount')->hiddenInput(['value' => $contragent->id])->label('');
                    ?>

                </td>
            </tr>
        </tbody>
    </table>

    <?php if ($model->hasErrors('transfer-error')): ?>
        <div class="alert alert-danger" style="position: fixed; bottom: 0; left: 20px; margin-bottom: 0px; width: 50%;">
            <?php foreach ($model->getErrors('transfer-error') as $error): ?>
                <b><?= $error; ?></b>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div style="position: fixed; bottom: 8px; right: 15px;">
        <button type="button" id="dialog-close" style="width: 100px; margin-right: 15px;" class="btn btn-link">Отмена</button>
        <button type="submit" style="width: 100px;" class="btn btn-primary">OK</button>
    </div>

<?php
ActiveForm::end();
?>

<script type="text/javascript">
jQuery(document).ready(function() {
    $('#dialog-close').click(function() {
        window.parent.$dialog.dialog('close');
    });
});
</script>