<?php
/**
 * свойства услуги для транка
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\controllers\uu\VoipController;
use app\models\billing\Trunk;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

$accountTariff = $formModel->accountTariff;
?>

<div class="row">

    <?php // транк ?>
    <div class="col-sm-2">
        <?= $form->field($accountTariff, 'trunk_id')
            ->widget(Select2::className(), [
                'data' => Trunk::getList($accountTariff->region_id, $isWithEmpty = true),
            ]) ?>
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
                        format: '<?= VoipController::FORMAT_OPTIONS ?>'
                    }, function (html) {
                        $('#accounttariff-trunk_id')
                            .html(html)
                            .trigger('change');
                    }
                );
            });
    });
</script>

