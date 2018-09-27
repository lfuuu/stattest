<?php

use app\classes\Html;
use app\forms\contragent\ContragentTransferForm;
use app\models\ClientAccount;
use app\models\ClientContragent;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var ContragentTransferForm $model */
/** @var ClientContragent $contragent */
/** @var ClientAccount $account */

$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
]);
?>

<table border="0" align="center" width="95%">
    <thead>
    <tr>
        <th>
            <h2>Контрагент - <?= $contragent->name; ?></h2>
            <hr size="1" />
        </th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td valign="middle">

            <div class="transfer-block">
                <div class="col-sm-12">

                    <div class="row contragent-wrap">

                        <table border="0" align="center" width="95%">
                            <colgroup>
                                <col width="*"/>
                                <col width="20%"/>
                                <col width="20%"/>
                            </colgroup>
                            <?php
                            $contracts = $contragent->contracts;
                            foreach ($contracts as $contract): ?>
                                <tr>
                                    <td>
                                        <input type="hidden" name="<?= $model->formName(); ?>[contracts][]" value="<?= $contract->id; ?>"/>
                                        <a href="<?= Url::toRoute(['contract/edit', 'id' => $contract->id, 'childId' => $account->id]) ?>" target="_blank">
                                            <span class="c-blue-color">
                                                Договор № <?= ($contract->number ?: 'Без номера'); ?>
                                                (<?= $contract->organization->name; ?>)
                                            </span>
                                        </a>
                                    </td>
                                    <td>
                                        <?= $contract->business; ?> / <?= $contract->businessProcessStatus->name; ?>
                                    </td>
                                    <td>
                                        <?php if ($contract->managerName) : ?>
                                            <span class="pull-left" style="background-color: <?= $contract->managerColor ?>;">
                                                М: <?= $contract->managerName ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($contract->accountManagerName) : ?>
                                            <span class="pull-right" style="background-color: <?= $contract->accountManagerColor ?>;">
                                                Ак.М: <?= $contract->accountManagerName ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <?php foreach ($contract->accounts as $ck => $contractAccount): ?>
                                            <div class="row row-ls">
                                                <span class="col-sm-2 account<?= ($contractAccount->is_active) ? ' active' : '' ?>">
                                                    <input type="hidden" name="<?= $model->formName(); ?>[clients][]" value="<?= $contractAccount->id; ?>"/>
                                                    <?= Html::a($contractAccount->getAccountTypeAndId(), ['/client/view', 'id' => $contractAccount->id], ['target' => '_blank']) ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>

                    </div>

                </div>
            </div>

            <br/>
            <?php
            echo $form->field($model, 'targetClientAccount')->widget(Select2::class, [
                'options' => [
                    'placeholder' => 'Выберите супер клиента',
                ],
                'pluginOptions' => [
                    'allowClear' => false,
                    'minimumInputLength' => 1,
                    'ajax' => [
                        'url' => '/account/super-client-search',
                        'dataType' => 'json',
                        'delay' => 250,
                        'data' => new JsExpression('function(params) { return {query: params.term}; }'),
                        'processResults' => new JsExpression('function (data, params) { return {results: data}; }'),
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
    <div class="alert alert-danger">
        <?php foreach ($model->getErrors('transfer-error') as $error): ?>
            <b><?= $error; ?></b>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="buttons-block">
    <button type="button" id="dialog-close" class="btn btn-link">Отмена</button>
    <button type="submit" class="btn btn-primary">OK</button>
</div>

<?php ActiveForm::end();