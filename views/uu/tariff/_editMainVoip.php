<?php
/**
 * свойства тарифа для телефонии
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 */

use app\classes\uu\model\TariffVoipTarificate;
use kartik\select2\Select2;

$tariff = $formModel->tariff;
?>

<div class="well">
    <div class="row">
        <div class="col-sm-4">
            <?= $form->field($tariff, 'voip_tarificate_id')
                ->widget(Select2::className(), [
                    'data' => TariffVoipTarificate::getList(),
                ]) ?>
        </div>
    </div>

    <?= $this->render('_editMainVoipCity', [
        'formModel' => $formModel,
        'form' => $form,
    ]) ?>

</div>