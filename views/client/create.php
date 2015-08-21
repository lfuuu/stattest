<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use app\classes\Language;

$language = Language::getLanguageByCountryId($contragent->country_id?:643);
$formFolderName = Language::getLanguageExtension($language);
$contragent->formLang = $language;
?>
<div class="row">
    <div class="col-sm-12">
        <h2>Новый клиент</h2>

        <?php $f = ActiveForm::begin(); ?>

        <div class="row" style="width: 1100px;">
            <div class="col-sm-6">
                <?= $f->field($account, 'admin_email', ['options' => ['style' => 'width: 100%']]); ?>
            </div>
        </div>

        <?= $this->render('../contragent/' . $formFolderName . '/form', ['model' => $contragent, 'f' => $f]); ?>
        <?= $this->render('../contract/' . $formFolderName . '/form', ['model' => $contract, 'f' => $f]); ?>
        <?= $this->render('../account/' . $formFolderName . '/form', ['model' => $account, 'f' => $f]); ?>

        <div class="row" style="width: 1100px;">
            <div class="col-sm-12 form-group">
                <?= Html::button('Сохранить', ['class' => 'btn btn-primary', 'id' => 'buttonSave', 'name' => 'save']); ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>


        <script>
            $('#buttonSave').closest('form').on('submit', function (e) {
                $('#type-select .btn').not('.btn-primary').each(function () {
                    $($(this).data('tab')).remove();
                });
                return true;
            });
        </script>
    </div>
</div>

<script type="text/javascript" src="/js/behaviors/managers_by_contract_type.js"></script>
<script type="text/javascript" src="/js/behaviors/organization_by_legal_type.js"></script>