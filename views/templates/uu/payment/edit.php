<?php

use app\assets\AppAsset;
use app\classes\Html;
use app\forms\templates\uu\PaymentForm;
use app\models\dictionary\PublicSite;
use app\models\document\PaymentTemplate;
use app\models\document\PaymentTemplateType;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use kartik\tabs\TabsX;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;

/**
 * @var PaymentTemplate $model
 * @var PaymentForm $formModel
 */

$this->registerJsFile('@web/js/jquery.multifile.min.js', ['depends' => [AppAsset::class]]);
$this->registerCssFile('@web/css/behaviors/media-manager.css', ['depends' => [AppAsset::class]]);
$this->registerCssFile('@web/css/behaviors/message-templates.css', ['depends' => [\kartik\tabs\TabsXAsset::class]]);

echo Html::formLabel('Шаблоны для документов');
echo Breadcrumbs::widget([
    'links' => [
        'Шаблоны',
        ['label' => 'Шаблоны для документов', 'url' => Url::toRoute(['/templates/uu/payment'])],
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
    ?>
    <div class="col-xs-4">
        <?php
            echo $form->field($model, 'type_id')->widget(Select2::class, [
                'data' => PaymentTemplateType::getList(),
                'options' => [
                    'placeholder' => 'Выберите тип',
                ],
                'pluginEvents' => [
                    'change' => 'function() { 
                        var typeId = $(this).val();
                        location.href = "' . '/templates/uu/payment' . '?PaymentTemplate[type_id]=" + typeId;
                    }',
                ],
            ]);
        ?>
    </div>
    <div class="col-xs-4">
        <br />
        <br />
        <a href="<?= Url::toRoute(['/dictionary/payment-template-type']) ?>" class="btn-link">
            Управлять типами
        </a>
    </div>
    <div style="clear: both;"></div>
    <?php

        $activeCountry = $model->country_code ? : $formModel->getCountryCode();

        $tabs = [];
        foreach (PublicSite::getAllWithCountries() as $publicSite) {
            /** @var Country $country */
            $country = $publicSite->getCountryFirst();
            $isActive = false;
            $formTemplateModel = new PaymentForm($model->type_id, $country->code);
            if ($country->code == $activeCountry) {
                $isActive = true;
                $formTemplateModel->id = $model->id;
            }
            $tabs[] = [
                'label' =>
                    Html::tag(
                        'div', '',
                        ['title' => $country->name, 'class' => 'flag flag-' . $country->getFlagCode()]
                    ) .
                    $country->name,
                'content' => $this->render('form', [
                    'formModel' => $formTemplateModel,
                    'form' => $form,
                ]),
                'headerOptions' => [],
                'options' => ['style' => 'white-space: nowrap;'],
                'active' => $isActive,
            ];
        }

        echo TabsX::widget([
            'id' => 'tabs-uu-payment-templates',
            'items' => $tabs,
            'position' => TabsX::POS_ABOVE,
            'bordered' => true,
            'encodeLabels' => false,
        ]);

    ?>

    <br />
    <?php
        // сообщение об ошибке
        if ($formModel->validateErrors) {
            Yii::$app->session->setFlash('error', $formModel->validateErrors);
        }
    ?>

    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => Yii::$app->request->url]) ?>
        <?= $this->render('//layouts/_submitButtonSave') ?>
    </div>

    <?php ActiveForm::end() ?>

    <?= $this->render('help') ?>
</div>