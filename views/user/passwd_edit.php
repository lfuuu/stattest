<?php

use app\classes\Html;
use kartik\builder\Form;
use kartik\widgets\ActiveForm;
use yii\helpers\Json;

$session = Yii::$app->session;
$userData = Json::decode($session->get('user_data'));

/** @var User $model */
$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
]);
?>

<legend>
    Изменение пароля
</legend>

<div class="well">
    <?php if ($session->hasFlash('success')): ?>
        <div class="alert alert-success text-center">
            <div>
                 Письмо с уведомлением отправлено на <?= $userData['email']; ?>
            </div>
        </div>
        <div class="alert alert-info text-center">
            <div>
                В случае возникновения проблем обращайтесь в службу поддержки
            </div>
        </div>
    <?php else: ?>
        <?php
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 3,
            'attributes' => [
                'password' => ['type' => Form::INPUT_PASSWORD],
                'passwordRepeat' => ['type' => Form::INPUT_PASSWORD],
                'passwordCurrent' => ['type' => ($model->scenario == 'profile' ? Form::INPUT_PASSWORD : Form::INPUT_RAW)],
            ],
        ]);
        ?>
    <?php endif; ?>

    <div class="buttons-block">
        <?php
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'attributes' => [
                'id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'id')],
                'actions' => [
                    'type' => Form::INPUT_RAW,
                    'value' =>
                        Html::beginTag('div', ['class' => 'col-md-12 text-right no-right-indent']) .
                        (
                            Yii::$app->session->hasFlash('success')
                                ?
                                    Html::button('Ок', [
                                        'class' => 'btn btn-primary save',
                                        'id' => 'dialog-close',
                                    ])
                                :
                                    Html::button('Отмена', [
                                        'class' => 'btn btn-link cancel',
                                        'id' => 'dialog-close',
                                    ]) .
                                    Html::submitButton('OK', [
                                        'class' => 'btn btn-primary save',
                                    ])
                        ) .
                        Html::endTag('div')
                ],
            ],
        ]);
        ?>
    </div>
</div>

<?php ActiveForm::end();