<?php
/**
 * Создание/редактирование универсальной услуги
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 */

use app\dao\UsageDao;
use app\models\ClientAccount;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$accountTariff = $formModel->accountTariff;

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
        [
            'label' => Yii::t('tariff', 'Universal services') .
                $this->render('//layouts/_helpConfluence', AccountTariff::getHelpConfluence()),
            'encode' => false,
        ],

        ['label' => $serviceType->name, 'url' => Url::to(['/uu/account-tariff', 'serviceTypeId' => $serviceType->id])],
        [
            'label' => $this->render('//layouts/_helpConfluence', $serviceType->getHelpConfluence()),
            'encode' => false,
        ],
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
$isLogReadOnly = !($accountTariff->isNewRecord || $accountTariff->isLogEditable());
$viewParams = [
    'formModel' => $formModel,
    'isReadOnly' => $isLogReadOnly,
];

if ($serviceType->id == ServiceType::ID_VOIP && $accountTariff->isNewRecord) {

    // персональная форма
    echo $this->render('_editVoip', $viewParams);

} else {

    // типовая форма
    echo $this->render(($accountTariff->isNewRecord || $accountTariff->isEditable()) ? '_editMain' : '_viewMain', $viewParams);

    // лог тарифов
    echo $accountTariff->isNewRecord ? '' : $this->render('_editLogGrid', $viewParams);

    // лог ресурсов
    echo $accountTariff->isNewRecord ? '' : $this->render('_editResourceLogForm', [
        'formModel' => $formModel,
        'isReadOnly' => !$accountTariff->isEditable(),
    ]);
}
