<?php

use app\classes\Html;
use app\models\UserGroups;
use kartik\builder\Form;
use kartik\widgets\ActiveForm;
use yii\helpers\Json;
use yii\helpers\Url;

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
        <div class="alert alert-success text-center">
            <div>
                Оператор
                <a href="<?= Url::toRoute(['/user/control/edit', 'id' => $data['id']]) ?>" onClick="window.parent.location.href = this.href; return false;">
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
                    'items' => UserGroups::getList($isWithEmpty = true),
                    'options' => [
                        'class' => 'select2',
                    ],
                ],
                'name' => ['type' => Form::INPUT_TEXT],
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
