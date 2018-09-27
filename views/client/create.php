<?php

/** @var $this \app\classes\BaseView */
/** @var \app\models\ClientContragent $contragent */

use app\assets\AppAsset;
use app\classes\Html;
use kartik\widgets\ActiveForm;
use app\classes\Language;

$this->registerJsFile('@web/js/behaviors/managers_by_contract_type.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/behaviors/organization_by_legal_type.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/behaviors/find-bik.js', ['depends' => [AppAsset::class]]);

$language = Language::getLanguageByCountryId($contragent->country_id?: \app\models\Country::RUSSIA);
$contragent->formLang = $language;
?>
<div class="row">
    <div class="col-sm-12">
        <h2>Новый клиент</h2>

        <?php $f = ActiveForm::begin(); ?>

        <div class="row max-screen">
            <div class="col-sm-6">
                <?= $f->field($account, 'admin_email', ['options' => ['style' => 'width: 100%']]); ?>
            </div>
        </div>

        <?php
        foreach (['contragent', 'contract', 'account'] as $formName) {
            $formModel = $$formName;
            echo $this->render($this->getFormPath($formName, $language), ['model' => $formModel, 'f' => $f]);
        }
        ?>

        <div class="row max-screen">
            <div class="col-sm-12 form-group">
                <?= Html::button('Сохранить', ['class' => 'btn btn-primary', 'id' => 'buttonSave', 'name' => 'save']); ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>