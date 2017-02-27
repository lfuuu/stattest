<?php
/**
 * Создание/редактирование универсальной услуги
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 */

use app\classes\Html;
use yii\widgets\ActiveForm;

ob_start();
$form = ActiveForm::begin();
ob_end_clean(); // форма уже есть на странице, а здесь надо вывести лишь ее содержимое

?>

<div class="resource-tariff-form well">

    <?= $this->render('_editLogInput', [
        'formModel' => $formModel,
        'form' => $form,
    ])
    ?>

    <?= Html::hiddenInput('accountTariffId', $formModel->id ?: 0) ?>
    <?= Html::activeHiddenInput($formModel->accountTariff, 'tariff_period_id') ?>

    <div class="row">
        <div class="col-sm-6">
            <?php if ($formModel->accountTariff->isNewRecord) : ?>
                <?= Html::submitButton(
                    Html::tag('i', '', [
                        'class' => 'glyphicon glyphicon-edit',
                        'aria-hidden' => 'true',
                    ]) .
                    ' Добавить пакет',
                    [
                        'class' => 'btn btn-primary',
                        'data-old-tariff-period-id' => $formModel->accountTariff->tariff_period_id,
                    ]
                ) ?>
            <?php endif ?>

            <?php // $this->render('//layouts/_buttonCancel', ['url' => '#']) ?>

        </div>
    </div>


</div>
