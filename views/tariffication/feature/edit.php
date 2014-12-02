<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\tariffication\ServiceType;
/** @var $item \app\models\tariffication\Feature */
/** @var $model \app\forms\tariffication\FeatureForm */
?>

<div class="well">
    <?php if ($item->id): ?>
        <legend>Редактирование параметра тариффикатора "<?=Html::encode($item->name)?>"</legend>
    <?php else: ?>
        <legend>Новый параметр тариффикатора</legend>
    <?php endif; ?>
    <?php
    $form = ActiveForm::begin(['type' => ActiveForm::TYPE_HORIZONTAL]);
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'id')],
            'name' => ['type' => Form::INPUT_TEXT],
            'service_type_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> ServiceType::dao()->getList(), 'options' => ['class' => 'select2'] ],
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    '<div class="col-md-offset-2 col-md-10">' .
                    Html::submitButton('Сохранить', ['class'=>'btn btn-primary']) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .
                    Html::a('Отмена', ['index'], ['class'=>'btn btn-default btn-sm']) .
                    '</div>'
            ],
        ],
    ]);
    ActiveForm::end();
    ?>
</div>
