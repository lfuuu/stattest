<?php

use app\assets\AppAsset;
use app\classes\Html;
use app\models\Language;
use kartik\widgets\ActiveForm;
use kartik\tabs\TabsX;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use app\forms\templates\uu\InvoiceForm;

$this->registerJsFile('@web/js/jquery.multifile.min.js', ['depends' => [AppAsset::className()]]);
$this->registerCssFile('@web/css/behaviors/media-manager.css', ['depends' => [AppAsset::className()]]);
$this->registerCssFile('@web/css/behaviors/message-templates.css', ['depends' => [\kartik\tabs\TabsXAsset::className()]]);

echo Html::formLabel('Универсальные счет-фактуры');
echo Breadcrumbs::widget([
    'links' => [
        'Шаблоны',
        ['label' => 'Универсальные счет-фактуры', 'url' => Url::toRoute(['/templates/uu/invoice'])],
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

    $tabs = [];

    foreach (Language::getList() as $languageCode => $languageTitle) {
        $tabs[] = [
            'label' =>
                Html::tag(
                    'div', '',
                    ['title' => $languageTitle, 'class' => 'flag flag-' . explode('-', $languageCode)[0]]
                ) .
                $languageTitle,
            'content' => $this->render('form', [
                'model' => new InvoiceForm($languageCode),
            ]),
            'headerOptions' => [],
            'options' => ['style' => 'white-space: nowrap;'],
        ];
    }

    echo TabsX::widget([
        'id' => 'tabs-uu-invoice-templates',
        'items' => $tabs,
        'position' => TabsX::POS_ABOVE,
        'bordered' => true,
        'encodeLabels' => false,
    ]);

    ?>

    <br />

    <div class="form-group">
        <?= $this->render('//layouts/_submitButtonSave') ?>
    </div>

    <?php ActiveForm::end() ?>
</div>