<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\tabs\TabsX;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use app\assets\TinymceAsset;
use app\classes\Html;
use app\assets\AppAsset;
use app\models\message\Template;
use app\models\important_events\ImportantEventsNames;
use app\models\Language;

/** @var app\classes\BaseView $this */
/** @var Template $model */

TinymceAsset::register(Yii::$app->view);

$this->registerJsFile('@web/js/jquery.multifile.min.js', ['depends' => [AppAsset::className()]]);
$this->registerCssFile('@web/css/behaviors/media-manager.css', ['depends' => [AppAsset::className()]]);
$this->registerCssFile('@web/css/behaviors/message-templates.css', ['depends' => [\kartik\tabs\TabsXAsset::className()]]);

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

    $model->event = $model->getEvent()->event->code;

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 2,
        'attributes' => [
            'name' => ['type' => Form::INPUT_TEXT,],
            'event' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => ImportantEventsNames::getList(true),
                'options' => [
                    'class' => 'select2',
                ],
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
        $tabs = [];
        foreach (Language::getList() as $languageCode => $languageTitle) {
            foreach (Template::$types as $type => $descr) {
                $templateContentModel = $model->getTemplateContent($languageCode, $type);

                $tabs[] = [
                    'label' =>
                            Html::tag(
                                'div', '',
                                ['title' => $languageTitle, 'class' => 'flag flag-' . explode('-', $languageCode)[0]]
                            ) .
                            Html::tag(
                                'i', '',
                                ['class' => 'glyphicon glyphicon-' . $descr['icon'], 'style' => 'margin: 2px;']
                            ) .
                            $descr['title'] .
                            (
                                $templateContentModel->isEmpty()
                                ?
                                    Html::tag('br') .
                                    Html::tag('span', 'Не заполненно', ['class' => 'label label-danger'])
                                : ''
                            )
                            ,
                    'content' => $this->render('content-form/' . $descr['format'], [
                        'templateId' => $model->id,
                        'templateType' => $type,
                        'templateLanguageCode' => $languageCode,
                        'model' => $templateContentModel,
                    ]),
                    'headerOptions' => [],
                    'options' => ['style' => 'white-space: nowrap;'],
                ];
            }
        }

        echo Html::tag('br');

        echo TabsX::widget([
            'id' => 'tabs-message-template',
            'items' => $tabs,
            'position' => TabsX::POS_LEFT,
            'bordered' => true,
            'encodeLabels' => false,
        ]);
    }
    ?>
</div>

<script type="text/javascript">
$(document).ready(function () {
    var stopUnload = false;

    tinymce.init({
        selector: '.editor',
        relative_urls: false,
        height : 350,
        plugins: [
            "advlist autolink lists link image charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table contextmenu paste"
        ],
        toolbar: "insertfile undo redo | styleselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
    });

    $('.media-manager').MultiFile({
        list: 'div.media-manager-block',
        max: 1,
        STRING: {
            remove: '',
            selected: 'Выбран файл: $file',
            toomany: 'Достигнуто максимальнное кол-во файлов',
            duplicate: 'Файл "$file" уже добавлен'
        },
        afterFileSelect: function(element, value, master_element) {
            var $block = master_element.list.find('div.MultiFile-label:last');

            stopUnload = true;

            $block
                .find('.MultiFile-label')
                .each(function() {
                    var
                        originalRemove = $(this).parents('div').find('a.MultiFile-remove');
                    remove =
                        $('<a />')
                            .attr('href', 'javascript:void(0)')
                            .text('Открепить')
                            .on('click', function(e) {
                                e.preventDefault();
                                originalRemove.trigger('click');
                            });

                    $(this)
                        .append(
                        $('<div />')
                            .css({'margin-left':'25px'})
                            .append(remove)
                    )
                });
        }
    });
});
</script>