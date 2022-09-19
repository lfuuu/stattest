<?php

use app\classes\BaseView;
use app\classes\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use app\models\voip\Source;

/** @var Source $model */
/** @var ActiveForm $form */
/** @var BaseView $this */

$form = ActiveForm::begin();

echo Html::formLabel('Редактирование источника телефонии');
echo Breadcrumbs::widget([
    'links' => [
        'Словари',
        ['label' => $this->title = 'Телефония: Источники', 'url' => $cancelUrl = '/dictionary/voip/source'],
        ($model->code ? 'Редактирование' : 'Добавление'),
    ],
]);
?>

<div class="container well col-sm-12">
    <fieldset class="col-sm-12">
        <div class="row">
            <div class="col-sm-3">
                <?= $form
                    ->field($model, 'code')->textInput()
                ?>
            </div>
            <div class="col-sm-3">
                <?= $form
                    ->field($model, 'name')->textInput()
                ?>
            </div>
            <div class="col-sm-3">
                <?= $form
                    ->field($model, 'is_service')->checkbox()
                ?>
            </div>
        </div>
    </fieldset>

    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($model->code ? 'Save' : 'Create')) ?>
    </div>

    <?php ActiveForm::end() ?>
</div> 