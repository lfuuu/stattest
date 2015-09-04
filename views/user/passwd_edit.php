<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;
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
        <div style="text-align: center;" class="alert alert-success">
            <div style="font-weight: bold;">
                 Письмо с уведомлением отправлено на <?= $userData['email']; ?>
            </div>
        </div>
        <div style="text-align: center;" class="alert alert-info">
            <div style="font-weight: bold;">
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

    <div style="position: fixed; bottom: 0; right: 0;">
        <?php
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'attributes' => [
                'id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'id')],
                'actions' => [
                    'type' => Form::INPUT_RAW,
                    'value' =>
                        '<div class="col-md-12" style="text-align: right; padding-right: 0px;">' .
                        (
                            Yii::$app->session->hasFlash('success')
                                ?
                                    Html::button('Ок', [
                                        'class' => 'btn btn-primary',
                                        'id' => 'dialog-close',
                                        'style' => 'width: 100px;',
                                    ])
                                :
                                    Html::button('Отмена', [
                                        'class' => 'btn btn-link',
                                        'id' => 'dialog-close',
                                        'style' => 'width: 100px; margin-right: 15px;',
                                    ]) .
                                    Html::submitButton('OK', [
                                        'class' => 'btn btn-primary',
                                        'style' => 'width: 100px;',
                                    ])
                        ) .
                        '</div>'
                ],
            ],
        ]);
        ?>
    </div>
</div>

<?php
ActiveForm::end();
?>

<script type="text/javascript">
jQuery(document).ready(function() {
    $('#dialog-close').click(function() {
        window.parent.$dialog.dialog('close');
    });
});
</script>
