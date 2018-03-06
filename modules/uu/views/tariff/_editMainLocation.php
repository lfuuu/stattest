<?php
/**
 * Свойства местоположения (для мобильной телефонии)
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
        <?= $form->field($package, 'location_id')
            ->widget(Select2::className(), [
                'data' => Package::getListLocation(),
                'options' => $options,
            ]) ?>
    </div>

</div>
