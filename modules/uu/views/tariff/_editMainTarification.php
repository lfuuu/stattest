<?php
/**
 * Свойства тарификации пакета
 *
 * @var \app\classes\BaseView $this
 * @var \yii\widgets\ActiveForm $form
 * @var Package $package
 * @var array $options
 */

use app\modules\nnp\models\Package;
use kartik\select2\Select2;

?>

<div class="row">
    <div class="col-sm-2">
        <?= $form->field($package, 'tarification_free_seconds')->textInput($options) ?>
    </div>

    <div class="col-sm-2">
        <?= $form->field($package, 'tarification_min_paid_seconds')->textInput($options) ?>
    </div>

    <div class="col-sm-2">
        <?= $form->field($package, 'tarification_interval_seconds')->textInput($options) ?>
    </div>

    <div class="col-sm-2">
        <?= $form->field($package, 'tarification_type')
            ->widget(Select2::className(), [
                'data' => [Package::TARIFICATION_TYPE_CEIL => 'В большую сторону (ceil)', Package::TARIFICATION_TYPE_ROUND => 'Математическое округление (round)'],
                'options' => $options,
            ]) ?>
    </div>

    <div class="col-sm-2">
        <?= $form->field($package, 'location_id')
            ->widget(Select2::className(), [
                'data' => Package::getListLocation(),
                'options' => $options,
            ]) ?>
    </div>

</div>
