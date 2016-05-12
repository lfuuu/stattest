<?php
/**
 * Создание/редактирование универсальной услуги
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 * @var bool $isReadOnly
 */


use app\classes\DateTimeWithUserTimezone;
use app\classes\Html;
use app\classes\uu\model\ServiceType;
use app\models\Region;
use kartik\select2\Select2;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$accountTariff = $formModel->accountTariff;

$serviceType = $formModel->getServiceType();
if (!$serviceType) {
    Yii::$app->session->setFlash('error', \Yii::t('common', 'Wrong ID'));
    return;
}
?>

<div class="resource-tariff-form well">
    <?php $form = ActiveForm::begin(); ?>

    <?php // добавить тариф (только для новых записей) ?>
    <?= $accountTariff->isNewRecord ?
        $this->render('_editLogInput', [
            'formModel' => $formModel,
            'form' => $form,
        ]) :
        ''
    ?>

    <?php
    if (!$accountTariff->isNewRecord) {
        ?>
        <div class="row">

            <?php // кто создал ?>
            <div class="col-sm-2">
                <label><?= $accountTariff->getAttributeLabel('insert_user_id') ?></label>
                <div><?= $accountTariff->insertUser ?
                        $accountTariff->insertUser->name :
                        Yii::t('common', '(not set)')
                    ?></div>
            </div>

            <?php // когда создал ?>
            <div class="col-sm-2">
                <label><?= $accountTariff->getAttributeLabel('insert_time') ?></label>
                <div><?= ($accountTariff->insert_time && $accountTariff->insert_time[0] != '0') ?
                        (new DateTimeWithUserTimezone($accountTariff->insert_time))->getDateTime() :
                        Yii::t('common', '(not set)') ?></div>
            </div>


            <?php // кто редактировал ?>
            <div class="col-sm-2">
                <label><?= $accountTariff->getAttributeLabel('update_user_id') ?></label>
                <div><?= $accountTariff->updateUser ?
                        $accountTariff->updateUser->name :
                        Yii::t('common', '(not set)')
                    ?></div>
            </div>

            <?php // когда редактировал ?>
            <div class="col-sm-2">
                <label><?= $accountTariff->getAttributeLabel('update_time') ?></label>
                <div><?= ($accountTariff->update_time && $accountTariff->update_time[0] != '0') ?
                        (new DateTimeWithUserTimezone($accountTariff->update_time))->getDateTime() :
                        Yii::t('common', '(not set)') ?></div>
            </div>

            <?php // аккаунт ?>
            <div class="col-sm-2">
                <label><?= $accountTariff->getAttributeLabel('client_account_id') ?></label>
                <div><?= $accountTariff->clientAccount->getLink() ?></div>
            </div>

            <?php // неуниверсальная услуга ?>
            <div class="col-sm-2">
                <label><?= Yii::t('tariff', 'Non-universal service') ?></label>
                <div><?= $accountTariff->getNonUniversalUrl() ?></div>
            </div>

        </div>
        <br/>
        <?php
    }
    ?>

    <div class="row">

        <?php // регион ?>
        <div class="col-sm-2">
            <?= $form->field($accountTariff, 'region_id')
                ->widget(Select2::className(), [
                    'data' => Region::getList(true),
                ]) ?>
        </div>

        <?php // комментарий ?>
        <div class="col-sm-4">
            <?= $form->field($accountTariff, 'comment')
                ->textarea()
                ->render()
            ?>
        </div>

        <?php // основная услуга ?>
        <div class="col-sm-3">
            <label><?= $accountTariff->getAttributeLabel('prev_account_tariff_id') ?></label>
            <div><?= $accountTariff->prevAccountTariff ?
                    Html::a(
                        Html::encode($accountTariff->prevAccountTariff->getName()),
                        $accountTariff->prevAccountTariff->getUrl()
                    ) :
                    Yii::t('common', '(not set)') ?></div>
        </div>

        <?php // пакеты ?>
        <div class="col-sm-3">
            <label><?= Yii::t('tariff', 'Packages') ?></label>
            <div><?= $accountTariff->getNextAccountTariffsAsString() ?></div>
        </div>

    </div>

    <?php
    // свойства тарифа конкретного типа услуги (ВАТС, телефония и пр.)
    $viewParams = [
        'formModel' => $formModel,
        'form' => $form,
    ];
    switch ($serviceType->id) {
        case ServiceType::ID_VOIP:
            echo $this->render('_editMainVoip', $viewParams);
            break;
        case ServiceType::ID_VOIP_PACKAGE:
            echo $this->render('_editMainVoipPackage', $viewParams);
            break;
    }
    ?>

    <div class="form-group">

        <?= Html::submitButton(
            Html::tag('i', '', [
                'class' => 'glyphicon glyphicon-save',
                'aria-hidden' => 'true',
            ]) . ' ' .
            Yii::t('common', $accountTariff->isNewRecord ? 'Create' : 'Save'),
            [
                'class' => 'btn btn-primary',
            ]
        ) ?>

        <?= Html::a(
            Html::tag('i', '', [
                'class' => 'glyphicon glyphicon-level-up',
                'aria-hidden' => 'true',
            ]) . ' ' .
            Yii::t('common', 'Cancel'),
            Url::to(['uu/account-tariff', 'serviceTypeId' => $serviceType->id]),
            [
                'class' => 'btn btn-link btn-cancel',
            ]
        ) ?>

    </div>

    <?php ActiveForm::end(); ?>
</div>
