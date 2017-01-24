<?php
/**
 * свойства услуги для транка
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\classes\ReturnFormatted;
use app\models\billing\Trunk;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

$accountTariff = $formModel->accountTariff;
if (!$accountTariff->isNewRecord) {
    // Здесь нечего делать. Можно только отредактировать "логический транк" в другом интерфейсе
    return;
}
?>

<div class="row">

    <?php // транк ?>
    <div class="col-sm-2">
        <label class="control-label" for="accounttariff-trunk_id">Транк</label>
        <?= Select2::widget(
            [
                'id' => 'accounttariff-trunk_id',
                'name' => 'trunkId',
                'data' => Trunk::dao()->getList($accountTariff->region_id, $isWithEmpty = true),
            ]
        ) ?>
        <div class="help-block"></div>
    </div>

</div>

<?php // при смене точки подключение обновить список транков ?>
<script type='text/javascript'>
    $(function () {
        $("#accounttariff-region_id")
            .on("change", function () {
                $.get(
                    '/uu/voip/get-trunks', {
                        regionId: $(this).val(),
                        format: '<?= ReturnFormatted::FORMAT_OPTIONS ?>'
                    }, function (html) {
                        $('#accounttariff-trunk_id')
                            .html(html)
                            .trigger('change');
                    }
                );
            });
    });
</script>

