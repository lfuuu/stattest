<?php
/**
 * свойства тарифа из основной таблицы
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\classes\DateTimeWithUserTimezone;
use app\models\Country;
use app\models\Currency;
use app\modules\uu\controllers\TariffController;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPerson;
use app\modules\uu\models\TariffStatus;
use app\modules\uu\models\TariffTag;
use kartik\select2\Select2;

$tariff = $formModel->tariff;

if ($editableType <= TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
} else {
    $options = [];
}

$viewParams = [
    'formModel' => $formModel,
    'form' => $form,
    'clientAccount' => $clientAccount,
];

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
                <div><?= ($tariff->insert_time && is_string($tariff->insert_time) && $tariff->insert_time[0] != '0') ?
                        (new DateTimeWithUserTimezone($tariff->insert_time))->getDateTime() :
                        Yii::t('common', '(not set)') ?></div>
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
                <div><?= ($tariff->update_time && is_string($tariff->update_time) && $tariff->update_time[0] != '0') ?
                        (new DateTimeWithUserTimezone($tariff->update_time))->getDateTime() :
                        Yii::t('common', '(not set)') ?></div>
            </div>

        </div>
        <?php
    }
    ?>

    <div class="row">
        <div class="col-sm-2">
            <?= $form->field($tariff, 'country_id')
                ->widget(Select2::className(), [
                    'data' => Country::getList($tariff->isNewRecord),
                    'options' => $options,
                ]) ?>
        </div>

        <div class="col-sm-2">
            <?= $form->field($tariff, 'currency_id')
                ->widget(Select2::className(), [
                    'data' => Currency::getList($tariff->isNewRecord),
                    'options' => $options,
                ]) ?>
        </div>

        <div class="col-sm-4">
            <?= $form->field($tariff, 'name')->textInput(($editableType == TariffController::EDITABLE_LIGHT) ? [] : $options) ?>
        </div>

        <div class="col-sm-2">
            <?= $form->field($tariff, 'is_include_vat')->checkbox($options) ?>
            <?= $form->field($tariff, 'is_autoprolongation')->checkbox($options) ?>
        </div>

        <div class="col-sm-2">
            <?= $form->field($tariff, 'is_default')->checkbox(($editableType == TariffController::EDITABLE_LIGHT) ? [] : $options) ?>
            <?php
            if (!array_key_exists($tariff->service_type_id, ServiceType::$packages)) {
                echo $form->field($tariff, 'is_postpaid')->checkbox($options);
            }
            ?>
        </div>

    </div>

    <div class="row">
        <div class="col-sm-2"><?= $form->field($tariff, 'tariff_status_id')
                ->widget(Select2::className(), [
                    'data' => TariffStatus::getList(false, $tariff->service_type_id),
                ]) ?>
        </div>

        <div class="col-sm-2"><?= $form->field($tariff, 'tag_id')->widget(Select2::className(), [
                'data' => TariffTag::getList(true),
            ]) ?>
        </div>

        <div class="col-sm-2"><?= $form->field($tariff, 'tariff_person_id')->widget(Select2::className(), [
                'data' => TariffPerson::getList(false),
                'options' => $options,
            ]) ?>
        </div>

        <div class="col-sm-2">
            <?= $form->field($tariff, 'count_of_validity_period')->textInput($options) ?>
        </div>

        <div class="col-sm-4">
            <?= $form->field($tariff, 'is_charge_after_blocking')->checkbox(($editableType == TariffController::EDITABLE_LIGHT) ? [] : $options) ?>
        </div>
    </div>

    <?php if (!$tariff->isNewRecord) : ?>
        <?= $this->render('//layouts/_showHistory', ['model' => $tariff]) ?>
    <?php endif; ?>

    <?= $this->render('_editMainOrganization', $viewParams) ?>
</div>