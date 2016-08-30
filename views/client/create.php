<?php

/** @var $this \app\classes\BaseView */

use app\assets\AppAsset;
use app\classes\Html;
use kartik\widgets\ActiveForm;
use app\classes\Language;

$this->registerJsFile('@web/js/behaviors/managers_by_contract_type.js', ['depends' => [AppAsset::className()]]);
$this->registerJsFile('@web/js/behaviors/organization_by_legal_type.js', ['depends' => [AppAsset::className()]]);
$this->registerJsFile('@web/js/behaviors/find-bik.js', ['depends' => [AppAsset::className()]]);

$language = Language::getLanguageByCountryId($contragent->country_id?: \app\models\Country::RUSSIA);
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
<?php

foreach (['contragent', 'contract', 'account'] as $formName) {
    echo $this->render($this->getFormPath($formName, $language), ['model' => $$formName, 'f' => $f]);
}

?>
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