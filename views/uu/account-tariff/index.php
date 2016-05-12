<?php
/**
 * Список универсальных услуг
 *
 * @var \yii\web\View $this
 * @var AccountTariffFilter $filterModel
 */

use app\classes\uu\filter\AccountTariffFilter;
use app\classes\uu\model\ServiceType;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;


$serviceType = $filterModel->getServiceType();
if (!$serviceType) {
    Yii::$app->session->setFlash('error', \Yii::t('common', 'Wrong ID'));
    return;
}
?>
<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal services'),
        ['label' => $this->title = $serviceType->name, 'url' => Url::to(['uu/account-tariff', 'serviceTypeId' => $serviceType->id])],
    ],
]) ?>

<?php
$viewParams = [
    'filterModel' => $filterModel,
];

if ($serviceType->id == ServiceType::ID_VOIP && $filterModel->client_account_id) {
    // персональная форма
    echo $this->render('_indexVoip', $viewParams);
} else {
    // типовая форма
    echo $this->render('_indexMain', $viewParams);

}
