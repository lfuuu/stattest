<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\classes\Html;
use yii\helpers\Json;
use app\models\UserGroups;

echo Html::formLabel('Создание оператора');

/** @var User $model */
$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
]);
?>

<div class="well">
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <?php
        $data = Json::decode(Yii::$app->session->get('user_created'));
        ?>
        <div style="text-align: center;" class="alert alert-success">
            <div style="font-weight: bold;">
                Оператор
                <a href="/user/control/edit/?id=<?= $data['id']; ?>" onClick="window.parent.location.href = this.href; return false;">
                    <?= $data['name']; ?> (<?= $data['user']; ?>)
                </a><br /><br />
                успешно создан<br />
                <br />
                Пароль: <?= $data['pass']; ?>
            </div>
        </div>
    <?php else: ?>
        <?php
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 3,
            'attributes' => [
                'user' => ['type' => Form::INPUT_TEXT],
                'usergroup' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => UserGroups::dao()->getList(true),
                    'options' => [
                        'class' => 'select2',
                    ],
                ],
                'name' => ['type' => Form::INPUT_TEXT],
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
            window.parent.location.reload(true);
        });
    });
</script>