<?php
/**
 * свойства услуги для транка
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\classes\Html;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\ServiceType;
use app\models\billing\Trunk;
use app\models\City;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

$accountTariff = $formModel->accountTariff;
?>

<div class="row">

    <?php // транк ?>
    <div class="col-sm-2">
        <?= $form->field($accountTariff, 'trunk_id')
            ->widget(Select2::className(), [
                'data' => Trunk::getList($serverId = null, $isWithEmpty = true),
            ]) ?>
    </div>

</div>

