<?php
/**
 * Создание/редактирование универсальной услуги
 *
 * @var \app\classes\BaseView $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 */

use app\classes\uu\model\ServiceType;
use app\dao\UsageDao;
use app\models\ClientAccount;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$accountTariff = $formModel->accountTariff;
$isReadOnly = !($accountTariff->isNewRecord || $accountTariff->isEditable());

$serviceType = $formModel->getServiceType();
if (!$serviceType) {
    Yii::$app->session->setFlash('error', \Yii::t('common', 'Wrong ID'));
    return;
}

if (!$accountTariff->isNewRecord) {
    $this->title = $accountTariff->getName();
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal services'),
        ['label' => $serviceType->name, 'url' => Url::to(['uu/account-tariff', 'serviceTypeId' => $serviceType->id])],
        $this->title
    ],
]) ?>

<?php
$clientAccount = $accountTariff->clientAccount;
if ($formModel->getIsNeedToSelectClient() || !$clientAccount) {
    Yii::$app->session->setFlash('error', Yii::t('tariff', 'You should {a_start}select a client first{a_finish}', ['a_start' => '<a href="/">', 'a_finish' => '</a>']));
    return;
}

if ($clientAccount->account_version != ClientAccount::VERSION_BILLER_UNIVERSAL) {
    Yii::$app->session->setFlash('error', 'Универсальную услугу можно добавить только ЛС, тарифицируемому универсально.');
    return;
}

if ($accountTariff->isNewRecord && !UsageDao::me()->isPossibleAddService($clientAccount, $accountTariff->service_type_id)) {
    Yii::$app->session->setFlash('error', UsageDao::me()->lastErrorMessage);
    return;
}
?>

<?php
// сообщение об ошибке
if ($formModel->validateErrors) {
    Yii::$app->session->setFlash('error', $formModel->validateErrors);
}
?>

<?php
$viewParams = [
    'formModel' => $formModel,
    'isReadOnly' => $isReadOnly,
];

if ($serviceType->id == ServiceType::ID_VOIP && $accountTariff->isNewRecord) {
    // персональная форма
    echo $this->render('_editVoip', $viewParams);
} else {
    // типовая форма
    echo $this->render($isReadOnly ? '_viewMain' : '_editMain', $viewParams);
    // лог тарифов
    echo $accountTariff->isNewRecord ? '' : $this->render('_editLogGrid', $viewParams);
    // лог ресурсов
    echo $accountTariff->isNewRecord ? '' : $this->render('_editResourceLogForm', $viewParams);
}
