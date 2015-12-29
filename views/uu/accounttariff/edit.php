<?php
/**
 * Создание/редактирование универсальной услуги
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 */

use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$accountTariff = $formModel->accountTariff;
$isReadOnly = !($accountTariff->isNewRecord || $accountTariff->tariff_period_id);
$serviceType = $formModel->getServiceType();

if (!$accountTariff->isNewRecord) {
    $this->title = $accountTariff->getName();
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal services'),
        ['label' => $serviceType ? $serviceType->name : '', 'url' => Url::to(['uu/accounttariff', 'serviceTypeId' => $serviceType ? $serviceType->id : ''])],
        $this->title
    ],
]) ?>

<?php
if ($formModel->IsNeedToSelectClient) {
    echo $this->render('//layouts/_alert', ['type' => 'danger', 'message' => Yii::t('tariff', 'You should {a_start}select a client first{a_finish}', ['a_start' => '<a href="/">', 'a_finish' => '</a>'])]);
    return;
}
?>

<?php
// сообщение об ошибке
if ($formModel->validateErrors) {
    echo $this->render('//layouts/_alert', ['type' => 'danger', 'message' => $formModel->validateErrors]);
}
?>

<?php
$viewParams = [
    'formModel' => $formModel,
    'isReadOnly' => $isReadOnly
];
?>

<?php // основная форма ?>
<?= $this->render($isReadOnly ? '_viewMain' : '_editMain', $viewParams) ?>

<?php // лог тарифов ?>
<?= $accountTariff->isNewRecord ? '' : $this->render('_editLogGrid', $viewParams) ?>
