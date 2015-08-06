<?php

use yii\helpers\Html;
use app\models\Region;
use app\models\Metro;
use app\models\SaleChannel;
use app\models\Bank;
use app\models\PriceType;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use kartik\builder\Form;
use kartik\widgets\DatePicker;

?>
<div class="row">
    <div class="col-sm-12">
        <h2><?= ($model->isNewRecord) ? 'Создание' : 'Редактирование' ?> суперклиента</h2>

        <?php
        $f = ActiveForm::begin();
        ?>

        <div class="row" style="width: 1100px;">
            <?php

            echo '<div>';
            echo Form::widget([
                'model' => $model,
                'form' => $f,
                'columns' => 4,
                'attributeDefaults' => [
                    'container' => ['class' => 'col-sm-12'],
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'name' => [],
                    'financial_manager_id' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => \app\models\User::getUserListByDepart(28)],
                ],
            ]);

            echo '</div>';
            ?>


            <div class="col-sm-12 form-group">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'id' => 'buttonSave']); ?>
            </div>
            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>