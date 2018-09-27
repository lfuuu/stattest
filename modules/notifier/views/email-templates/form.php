<?php

use app\assets\AppAsset;
use app\assets\TinymceAsset;
use app\classes\Html;
use app\classes\important_events\ImportantEventsDetailsFactory;
use app\models\Country;
use app\models\important_events\ImportantEventsNames;
use app\models\Language;
use app\modules\notifier\models\templates\Template;
use kartik\tabs\TabsX;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var app\classes\BaseView $this */
/** @var Template $model */

TinymceAsset::register(Yii::$app->view);

$this->registerJsFile('@web/js/jquery.multifile.min.js', ['depends' => [AppAsset::class]]);
$this->registerCssFile('@web/css/behaviors/media-manager.css', ['depends' => [AppAsset::class]]);
$this->registerCssFile('@web/css/behaviors/message-templates.css', ['depends' => [\kartik\tabs\TabsXAsset::class]]);

echo Html::formLabel($model->name ? 'Редактирование шаблона' : 'Новый шаблон');
echo Breadcrumbs::widget([
    'links' => [
        'Шаблоны',
        ['label' => 'Шаблоны почтовых оповещений', 'url' => $cancelUrl = Url::toRoute(['/notifier/email-templates'])],
        $model->name ? 'Редактирование шаблона' : 'Новый шаблон'
    ],
]);
?>

<div class="well">
    <?php
    $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_VERTICAL,
        'options' => [
            'enctype' => 'multipart/form-data',
        ],
    ]);

    $model->event = $model->getEvent()->event->code;

    $form->field($model, 'id')->hiddenInput()->label(null);
    ?>

    <div class="row">
        <div class="col-sm-6">
            <?= $form
                ->field($model, 'name')
                ->textInput()
            ?>
        </div>
        <div class="col-sm-6">
            <?= $form
                ->field($model, 'event')
                ->dropDownList(ImportantEventsNames::getList(true),
                    [
                        'class' => 'select2',
                    ]
                ) ?>
        </div>
    </div>

    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . (!$model->id ? 'Create' : 'Save')) ?>
    </div>

    <?php
    if ($model->id) {
        $tabs = [];

        $mainTabs = [];
        foreach (Country::getList() as $countryCode => $countryName) {
            $tabs = [];
            foreach (Language::getList() as $languageCode => $languageTitle) {
                foreach (Template::$types as $type => $descr) {
                    $templateContentModel = $model->getTemplateContent($countryCode, $languageCode, $type);

                    $tabs[] = [
                        'label' =>
                            $languageCode .
                            Html::tag('div',
                                '',
                                ['title' => $languageTitle, 'class' => 'flag flag-' . explode('-', $languageCode)[0]]
                            ) .
                            Html::tag('i',
                                '',
                                ['class' => 'glyphicon glyphicon-' . $descr['icon'], 'style' => 'margin: 2px;']
                            ) .
                            $descr['title'] .
                            (
                            $templateContentModel->isEmpty()
                                ? Html::tag('br') .
                                Html::tag('span', 'Не заполненно', ['class' => 'label label-danger'])
                                : ''
                            )
                        ,
                        'content' => $this->render('content-form/' . $descr['format'],
                            [
                                'model' => $templateContentModel,
                            ]
                        ),
                        'headerOptions' => [],
                        'options' => ['style' => 'white-space: nowrap;'],
                    ];
                }
            }

            $mainTabs[] = [
                'label' => $countryName,
                'content' => TabsX::widget([
                    'id' => 'tabs-message-template-content-' . $countryCode,
                    'items' => $tabs,
                    'position' => TabsX::POS_LEFT,
                    'bordered' => true,
                    'encodeLabels' => false,
                ]),
                'headerOptions' => [],
                'options' => ['style' => 'white-space: nowrap;'],
            ];
        }

        echo Html::tag('br');

        echo TabsX::widget([
            'id' => 'tabs-message-template',
            'items' => $mainTabs,
            'position' => TabsX::POS_ABOVE,
            'bordered' => true,
            'encodeLabels' => false,
        ]);
    }

    ActiveForm::end();
    ?>

    <br/>
    <table class="table table-bordered ">
        <colgroup>
            <col width="20%"/>
            <col width="*"/>
        </colgroup>
        <thead>
        <tr>
            <th colspan="2" class="info">Глобальные свойства</th>
        </tr>
        <tr class="info">
            <th>Переменная</th>
            <th>Значение</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach (\app\helpers\RenderParams::getListOfVariables() as $variable => $descr): ?>
            <tr>
                <td>{<?= $variable ?>}</td>
                <td><?= $descr ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($model->getEvent()->event->code) : ?>
        <br/>
        <table class="table table-bordered ">
            <colgroup>
                <col width="20%"/>
                <col width="*"/>
            </colgroup>
            <thead>
            <tr>
                <th colspan="2" class="info">Свойства события</th>
            </tr>
            <tr class="info">
                <th>Переменная</th>
                <th>Значение</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (
                ImportantEventsDetailsFactory::get($model->getEvent()->event->code)->getProperties() as
                $variable => $descr
            ): ?>
                <tr>
                    <td>{event.<?= $variable ?>}</td>
                    <td><?= $descr ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var stopUnload = false;

        tinymce.init({
            selector: '.editor',
            relative_urls: false,
            height: 350,
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
            afterFileSelect: function (element, value, master_element) {
                var $block = master_element.list.find('div.MultiFile-label:last');

                stopUnload = true;

                $block
                    .find('.MultiFile-label')
                    .each(function () {
                        var
                            originalRemove = $(this).parents('div').find('a.MultiFile-remove');
                        remove =
                            $('<a />')
                                .attr('href', 'javascript:void(0)')
                                .text('Открепить')
                                .on('click', function (e) {
                                    e.preventDefault();
                                    originalRemove.trigger('click');
                                });

                        $(this)
                            .append(
                                $('<div />')
                                    .css({'margin-left': '25px'})
                                    .append(remove)
                            )
                    });
            }
        });
    });
</script>