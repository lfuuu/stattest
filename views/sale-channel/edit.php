<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;

?>

<div class="row">
    <div class="col-sm-12">
        <h2><?= ($model->isNewRecord) ? 'Создание' : 'Редактирование' ?> канала продаж</h2>

        <?php $f = ActiveForm::begin(); ?>

        <div class="row" style="width: 1100px;">
            <?php

            echo Form::widget([
                'model' => $model,
                'form' => $f,
                'columns' => 3,
                'attributeDefaults' => [
                    'container' => ['class' => 'col-sm-12'],
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'name' => [],
                    'dealer_id' => [],
                    'is_agent' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => [0 => 'Нет', 1 => 'Да']],
                    'interest' => [],
                    'courier_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => \app\models\Courier::dao()->getList(false, true)],
                ],
            ]);

            ?>



            <div class="col-sm-12 form-group">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'id' => 'buttonSave']); ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>