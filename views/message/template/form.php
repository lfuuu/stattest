<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use yii\bootstrap\Tabs;
use app\assets\TinymceAsset;
use app\classes\Html;
use app\models\message\Template;
use app\models\message\TemplateContent;
use app\models\Language;

TinymceAsset::register(Yii::$app->view);

/** @var Template $model */

echo Html::formLabel($model->name ? 'Редактирование шаблона' : 'Новый шаблон');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Сообщения', 'url' => Url::toRoute(['message/template'])],
        $model->name ? 'Редактирование шаблона' : 'Новый шаблон'
    ],
]);
?>

<div class="well">
    <?php
    $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_VERTICAL,
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 1,
        'attributes' => [
            'name' => [
                'type' => Form::INPUT_TEXT,
            ],
        ]
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'id')],
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    Html::tag(
                        'div',
                        Html::button('Отменить', [
                            'class' => 'btn btn-link',
                            'style' => 'margin-right: 15px;',
                            'onClick' => 'self.location = "' . Url::toRoute(['message/template']) . '";',
                        ]) .
                        Html::submitButton('Сохранить', ['class' => 'btn btn-primary']),
                        ['style' => 'text-align: right; padding-right: 0px;']
                    )
            ],
        ],
    ]);
    ActiveForm::end();

    if ($model->id) {
        $form = ActiveForm::begin([
            'type' => ActiveForm::TYPE_VERTICAL,
            'action' => Url::toRoute(['/message/template/edit-template-content', 'template_id' => $model->id]),
        ]);

        $tabs = [];
        $languages = Language::find()->orderBy('code desc')->all();
        $types = Template::$types;
        foreach ($types as $type => $descr) {
            foreach ($languages as $language) {
                $content =
                    TemplateContent::findOne([
                        'template_id' => $model->id,
                        'lang_code' => $language->code,
                        'type' => $type,
                    ]);

                $tabs[] = [
                    'label'     => $descr['title'] . ' ' . $language->name,
                    'content'   => $this->render('content-form', [
                        'type' => $type,
                        'type_descr' => $descr,
                        'language' => $language,
                        'model' => $content instanceof TemplateContent ? $content : new TemplateContent,
                        'form' => $form,
                    ]),
                ];
            }
        }

        echo Tabs::widget([
            'id' => 'tabs-message-template',
            'items' => $tabs,
        ]);

        ActiveForm::end();
    }
    ?>
</div>

<script type="text/javascript">
$(document).ready(function () {
    tinymce.init({
        selector: '.editor',
        height : 350,
        plugins: [
            "advlist autolink lists link image charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table contextmenu paste"
        ],
        toolbar: "insertfile undo redo | styleselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
    });
});
</script>