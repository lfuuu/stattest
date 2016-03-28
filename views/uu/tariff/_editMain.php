<?php
/**
 * свойства тарифа из основной таблицы
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 */

use app\classes\uu\model\TariffPerson;
use app\classes\uu\model\TariffStatus;
use app\models\Country;
use app\models\Currency;
use kartik\select2\Select2;

$tariff = $formModel->tariff;
?>

<div class="well">

    <?php
    if (!$tariff->isNewRecord) {
        ?>
        <div class="row">

            <div class="col-sm-2">
                <label><?= $tariff->getAttributeLabel('insert_user_id') ?></label>
                <div><?= $tariff->insertUser ?
                        $tariff->insertUser->name :
                        Yii::t('common', '(not set)')
                    ?></div>
            </div>

            <div class="col-sm-2">
                <label><?= $tariff->getAttributeLabel('insert_time') ?></label>
                <div><?= $tariff->insert_time ?></div>
            </div>


            <div class="col-sm-2">
                <label><?= $tariff->getAttributeLabel('update_user_id') ?></label>
                <div><?= $tariff->updateUser ?
                        $tariff->updateUser->name :
                        Yii::t('common', '(not set)')
                    ?></div>
            </div>

            <div class="col-sm-2">
                <label><?= $tariff->getAttributeLabel('update_time') ?></label>
                <div><?= $tariff->update_time ?: Yii::t('common', '(not set)') ?></div>
            </div>

            <div class="col-sm-2">
                <label><?= Yii::t('tariff', 'Non-universal tariff') ?></label>
                <div><?= $tariff->getNonUniversalUrl() ?></div>
            </div>

        </div>
        <br/>
        <?php
    }
    ?>

    <div class="row">
        <div class="col-sm-2"><?= $form->field($tariff, 'country_id')
                ->widget(Select2::className(), [
                    'data' => Country::getList($tariff->isNewRecord),
                ]) ?></div>

        <div class="col-sm-2"><?= $form->field($tariff, 'currency_id')
                ->widget(Select2::className(), [
                    'data' => Currency::getList($tariff->isNewRecord),
                ]) ?></div>

        <div class="col-sm-4"><?= $form->field($tariff, 'name')->textInput() ?></div>
        <div class="col-sm-4">
            <?= $form->field($tariff, 'is_charge_after_blocking')->checkbox() ?>
            <?= $form->field($tariff, 'is_include_vat')->checkbox() ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-2"><?= $form->field($tariff, 'tariff_status_id')
                ->widget(Select2::className(), [
                    'data' => TariffStatus::getList(false, $tariff->service_type_id),
                ]) ?></div>

        <div class="col-sm-2"><?= $form->field($tariff, 'tariff_person_id')->widget(Select2::className(), [
                'data' => TariffPerson::getList(false),
            ]) ?></div>

        <div class="col-sm-4"><?= $form->field($tariff, 'count_of_validity_period')->textInput() ?></div>

        <div class="col-sm-2">
            <?php //$form->field($tariff, 'is_charge_after_period')->checkbox() ?>
            <?= $form->field($tariff, 'is_autoprolongation')->checkbox() ?>
        </div>
        <div class="col-sm-2"></div>
    </div>
</div>

<?php // если ресурс может быть выключен/включен, то при его включении цену указывать нет смысла, потому что она входит в абонентку ?>
<script type='text/javascript'>
    $(function () {
        $('#tariff-is_autoprolongation')
            .on('change', function () {
                var $checkbox = $(this);
                var $input = $('#tariff-count_of_validity_period');
                if ($checkbox.is(':checked')) {
                    $input.attr('readonly', 'readonly');
                } else {
                    $input.removeAttr('readonly');
                }
            })
            .trigger('change');
    });
</script>

