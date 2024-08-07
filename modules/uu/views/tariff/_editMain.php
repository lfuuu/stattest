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
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffCountry;
use app\modules\uu\models\TariffPerson;
use app\modules\uu\models\TariffStatus;
use app\modules\uu\models\Tag;
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
    <h2>Тариф <?= $helpConfluence = $this->render('//layouts/_helpConfluence', Tariff::getHelpConfluence()) ?></h2>

    <div class="row">

        <div class="col-sm-4">
            <?= $form->field($tariff, 'name')
                ->textInput(($editableType == TariffController::EDITABLE_LIGHT) ? [] : $options)
                ->label($tariff->getAttributeLabel('name') . $helpConfluence)
            ?>
        </div>

        <?php
        if (!$tariff->isNewRecord) {
            ?>
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
            <?php
        }
        ?>
    </div>

    <div class="row">

        <div class="col-sm-2">
            <?= $form->field($tariff, 'currency_id')
                ->widget(Select2::class, [
                    'data' => Currency::getList($tariff->isNewRecord),
                    'options' => $options,
                ])
                ->label($tariff->getAttributeLabel('currency_id') . $helpConfluence)
            ?>
        </div>

        <div class="col-sm-6">
            <label><?= Yii::t('models/' . TariffCountry::tableName(), 'country_id') . $helpConfluence ?></label>
            <?= Select2::widget([
                'name' => 'TariffCountry[]',
                'value' => array_keys($formModel->tariffCountries),
                'data' => Country::getList($isWithEmpty = false),
                'options' => [
                    'multiple' => true,
                ],
            ]) ?>
        </div>

        <div class="col-sm-2">
            <?= $form->field($tariff, 'is_include_vat')
                ->checkbox($options + ['label' => $tariff->getAttributeLabel('is_include_vat') . $helpConfluence])
            ?>
            <?= $form->field($tariff, 'is_autoprolongation')
                ->checkbox($options + ['label' => $tariff->getAttributeLabel('is_autoprolongation') . $helpConfluence])
            ?>
        </div>

        <div class="col-sm-2">
            <?= $form->field($tariff, 'is_default')
                ->checkbox((($editableType == TariffController::EDITABLE_LIGHT) ? [] : $options) +
                    ['label' => $tariff->getAttributeLabel('is_default') . $helpConfluence])
            ?>
            <?php
            if (
                isset(ServiceType::$packages[$tariff->service_type_id])
                || array_search($tariff->service_type_id, ServiceType::$packages)
            ) {
                echo $form->field($tariff, 'is_bundle')
                    ->checkbox($options + ['label' => $tariff->getAttributeLabel('is_bundle')]);
            }
            ?>
        </div>

    </div>

    <div class="row">
        <div class="col-sm-2"><?= $form->field($tariff, 'tariff_status_id')
                ->widget(Select2::class, [
                    'data' => TariffStatus::getList(false, $tariff->service_type_id),
                ])
                ->label($tariff->getAttributeLabel('tariff_status_id') . $helpConfluence)
            ?>
        </div>

        <div class="col-sm-2"><?= $form->field($tariff, 'tag_id')->widget(Select2::class, [
                'data' => Tag::getList(true),
            ])
                ->label($tariff->getAttributeLabel('tag_id') . $helpConfluence)
            ?>
        </div>

        <div class="col-sm-1"><?= $form->field($tariff, 'payment_template_type_id')->widget(Select2::class, [
                'data' => \app\models\document\PaymentTemplateType::getList(true, false, \app\models\document\PaymentTemplateType::DATA_SOURCE_TARIFF),
            ])
                ->label($tariff->getAttributeLabel('payment_template_type_id') . $helpConfluence)
            ?>
        </div>

        <div class="col-sm-2"><?= $form->field($tariff, 'tariff_person_id')->widget(Select2::class, [
                'data' => TariffPerson::getList(false),
                'options' => $options,
            ])
                ->label($tariff->getAttributeLabel('tariff_person_id') . $helpConfluence)
            ?>
        </div>

        <div class="col-sm-1">
            <?= $form->field($tariff, 'count_of_validity_period')
                ->textInput($options)
                ->label($tariff->getAttributeLabel('count_of_validity_period') . $helpConfluence)
            ?>
        </div>

        <div class="col-sm-2">
            <?= $form->field($tariff, 'is_charge_after_blocking')
                ->checkbox((($editableType == TariffController::EDITABLE_LIGHT) ? [] : $options) +
                    ['label' =>
                        (isset(ServiceType::$packages[$tariff->service_type_id])
                            ? \Yii::t('models/' . Tariff::tableName(), 'is_charge_after_blocking_package')
                            : $tariff->getAttributeLabel('is_charge_after_blocking')) . $helpConfluence])

            ?>
            <?= $form->field($tariff, 'is_proportionately')
                ->checkbox((($editableType == TariffController::EDITABLE_LIGHT) ? [] : $options) +
                    ['label' => $tariff->getAttributeLabel('is_proportionately') . $helpConfluence])
            ?>
            <?= $form->field($tariff, 'tax_rate')
                ->textInput($options + [
                        'label' => $tariff->getAttributeLabel('tax_rate') . $helpConfluence,
                        'placeholder' => \Yii::t('common', '(not set)')
                    ])
            ?>

        </div>

        <div class="col-sm-2">
            <?= $form->field($tariff, 'is_one_active')
                ->checkbox((($editableType == TariffController::EDITABLE_LIGHT) ? [] : $options) +
                    ['label' => $tariff->getAttributeLabel('is_one_active') . $helpConfluence])
            ?>
            <?php
            if (isset(ServiceType::$packages[$tariff->service_type_id])) {
                echo $form->field($tariff, 'is_one_alt')
                    ->checkbox($options +
                        ['label' => $tariff->getAttributeLabel('is_one_alt') . $helpConfluence]
                    );
            }
            ?>
        </div>


    </div>

    <?php if (!$tariff->isNewRecord) : ?>
        <?= $this->render('//layouts/_showHistory', ['model' => $tariff]) ?>
    <?php endif; ?>


    <?= $this->render('_editMainTags', $viewParams) ?>
    <?= $this->render('_editMainOrganization', $viewParams) ?>

    <?= $tariff->is_bundle && isset(ServiceType::$packages[$tariff->service_type_id]) ? $this->render('_editMainBundleTariffEdit', $viewParams) : '' ?>
    <?= $tariff->is_bundle && array_search($tariff->service_type_id, ServiceType::$packages) ? $this->render('_editMainBundleTariffView', $viewParams) : '' ?>

</div>