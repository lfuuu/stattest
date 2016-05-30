<?php
/**
 * Диапазон номеров. Добавление/удаление префикса
 *
 * @var app\classes\BaseView $this
 */
use app\classes\Html;
use app\modules\nnp\models\Prefix;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

?>

<div class="well">
    <?php
    $form = ActiveForm::begin();
    $viewParams = [
        'form' => $form,
    ];
    ?>

    <div class="row">

        <div class="col-sm-2">
            Все отфильтрованные записи в существующий префикс
        </div>

        <div class="col-sm-3">
            <?= Select2::widget([
                'name' => 'Prefix[id]',
                'data' => Prefix::getList($isWithEmpty = true, $isWithClosed = false),
            ]) ?>
        </div>

        <div class="col-sm-1">
            или новый
        </div>

        <div class="col-sm-3">
            <?= Html::textInput('Prefix[name]', '', ['class' => 'form-control']) ?>
        </div>

        <div class="col-sm-3">
            <?= $this->render('//layouts/_submitButton', [
                'text' => Yii::t('common', 'Append'),
                'glyphicon' => 'glyphicon-plus',
                'params' => [
                    'class' => 'btn btn-success',
                ],
            ]) ?>

            <?= $this->render('//layouts/_submitButtonDrop') ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
