<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\classes\Html;
use yii\helpers\Json;
use app\models\UserGroups;

echo Html::formLabel('Создание группы');

/** @var User $model */
$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
]);
?>

<div class="well">
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <?php
        $data = Json::decode(Yii::$app->session->get('group_created'));
        ?>
        <div style="text-align: center;" class="alert alert-success">
            <div style="font-weight: bold;">
                Группа
                <a href="/user/group/edit?id=<?= $data['usergroup']; ?>" onClick="window.parent.location.href = this.href; return false;">
                    <?= $data['comment']; ?>
                </a>
                успешно создана
            </div>
        </div>
    <?php else: ?>
        <?php
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 3,
            'attributes' => [
                'usergroup' => ['type' => Form::INPUT_TEXT],
                'comment' => ['type' => Form::INPUT_TEXT],
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
            window.parent.location.reload(true);
            window.parent.$dialog.dialog('close');
        });
    });
</script>