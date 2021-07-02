<?php
/**
 * Создание/редактирование универсальной услуги
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var bool $isReadOnly
 */


use app\classes\DateTimeWithUserTimezone;
use app\classes\Html;
use app\models\Region;
use app\modules\uu\forms\AccountTariffAddForm;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
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

<div class="account-tariff-edit-main well">
    <h2>Услуга <?= $helpConfluence = $this->render('//layouts/_helpConfluence', AccountTariff::getHelpConfluence()) ?></h2>

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
                <div><?= ($accountTariff->insert_time && is_string($accountTariff->insert_time) && $accountTariff->insert_time[0] != '0') ?
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
                <div><?= ($accountTariff->update_time && is_string($accountTariff->update_time) && $accountTariff->update_time[0] != '0') ?
                        (new DateTimeWithUserTimezone($accountTariff->update_time))->getDateTime() :
                        Yii::t('common', '(not set)') ?></div>
            </div>

            <?php // ЛС ?>
            <div class="col-sm-2">
                <label><?= $accountTariff->getAttributeLabel('client_account_id') ?></label>
                <div><?= $accountTariff->clientAccount->getLink() ?></div>
            </div>

            <?php // Очередь ?>
            <div class="col-sm-2">
                <label>Очередь событий</label>
                <div><?= Html::a('Показать', ['/monitoring/event-queue', 'EventQueueFilter[account_tariff_id]' => $accountTariff->id]) ?></div>
            </div>

        </div>
        <br/>
        <?php
    }
    ?>

    <div class="row">

        <?php // регион
        $regionList = Region::getList(true, null, $accountTariff->isNewRecord && $serviceType->id == ServiceType::ID_VPBX ? Region::TYPE_NODE : null, $serviceType->id == ServiceType::ID_SIPTRUNK ? 1 : null);
        ?>
        <div class="col-sm-2">
            <?= $form->field($accountTariff, 'region_id')
                ->widget(Select2::class, [
                    'data' => $regionList,
                ])
                ->label($accountTariff->getAttributeLabel('region_id') . $helpConfluence)
            ?>
        </div>

        <?php // основная услуга ?>
        <div class="col-sm-2">
            <label><?= $accountTariff->getAttributeLabel('prev_account_tariff_id') . $helpConfluence ?></label>
            <div><?= $accountTariff->prevAccountTariff ?
                    Html::a(
                        Html::encode($accountTariff->prevAccountTariff->getName()),
                        $accountTariff->prevAccountTariff->getUrl()
                    ) :
                    Yii::t('common', '(not set)') ?></div>
        </div>

        <?php // пакеты ?>
        <div class="col-sm-4">
            <label><?= Yii::t('tariff', 'Packages') . $helpConfluence ?></label>
            <div><?= $accountTariff->getNextAccountTariffsAsString() ?></div>
        </div>

        <?php // Стаститика ?>
        <div class="col-sm-4">
            <?= $this->render('_editMainStatistic', [
                'accountTariff' => $accountTariff,
            ])?>
        </div>

    </div>

    <div class="row">
        <?php // комментарий ?>
        <div class="col-sm-12">
            <?= $form->field($accountTariff, 'comment')
                ->textarea(['style' => 'height: 175px'])
                ->label($accountTariff->getAttributeLabel('comment') . $helpConfluence)
            ?>
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

        case ServiceType::ID_VOIP_PACKAGE_CALLS:
            echo $this->render('_editMainVoipPackage', $viewParams);
            break;

        case ServiceType::ID_TRUNK:
            echo $this->render('_editMainTrunk', $viewParams);
            break;

        case ServiceType::ID_ONE_TIME:
            echo $this->render('_editMainOneTime', $viewParams);
            break;

        case ServiceType::ID_INFRASTRUCTURE:
            echo $this->render('_editMainInfrastructure', $viewParams);
            break;

        case ServiceType::ID_VPS:
            echo $this->render('_editMainVps', $viewParams);
            break;

        case ServiceType::ID_A2P:
            echo $this->render('_editMainA2p', $viewParams);
            break;

        case ServiceType::ID_CALLTRACKING:
            echo $this->render('_editMainCalltrackingParams', $viewParams);
            break;
    }
    ?>

    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => Url::to(['/uu/account-tariff', 'serviceTypeId' => $serviceType->id])]) ?>
        <?= $this->render('//layouts/_submitButton', [
            'text' => Yii::t('common', ($accountTariff->isNewRecord ? 'Create' : 'Save')),
            'glyphicon' => 'glyphicon-save',
            'params' => [
                    'class' => 'btn btn-primary',
                    'id' => 'submit-button'
            ] + ((($formModel instanceof AccountTariffAddForm) && $formModel->isShowRoistatVisit() && $accountTariff->isNewRecord) ? ['disabled' => ''] : []),
        ]); ?>
    </div>

    <?php ActiveForm::end(); ?>

    <?php if (!$accountTariff->isNewRecord) : ?>
        <?= $this->render('//layouts/_showHistory', ['model' => $accountTariff]) ?>
    <?php endif; ?>

</div>
