<?php

/**
 * Редактирование статусов для уровней цен в услуге
 *
 * @var \app\classes\BaseView $this
 * @var DidGroupForm $formModel
 * @var DidGroupPriceLevel $didGroupPriceLevelModel
 */

use app\classes\Html;
use app\forms\tariff\DidGroupForm;
use app\models\DidGroupPriceLevel;
use app\models\City;
use app\models\Country;
use app\models\DidGroup;
use app\modules\nnp\models\NdcType;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$this->title = Yii::t('common', 'Edit');
?>

<?= Breadcrumbs::widget([
    'links' => [
        'Тарифы',
        ['label' => 'Редактирование статусов для уровней цен в услуге', 'url' => $cancelUrl = '/uu/service-folder'],
        $this->title
    ],
]) ?>

<div class="well">
    <?php
    $form = ActiveForm::begin();

    $this->registerJsVariable('formId', $form->getId());

    $viewParams = [
        'formModel' => $formModel,
        'form' => $form,
    ];

    ?>

    <?= Html::hiddenInput('isFake', '0', ['id' => 'isFake']) ?>

    <?php
    // сообщение об ошибке
    if ($formModel->validateErrors) {
        Yii::$app->session->setFlash('error', $formModel->validateErrors);
    }
    ?>

    <div class="row">
        <div class="col-sm-6">
            <h1>Настройки <?= $formModel->serviceType->name ?></h1>
        </div>
    </div>

    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButtonSave', $viewParams) ?>
    </div>

    <?= $this->render('_editServicePriceListStatus', $viewParams) ?>

    <?php // кнопки
    ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButtonSave', $viewParams) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>