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
?>
<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal services'),
        [
            'label' => $this->title = $serviceType ? $serviceType->name : 'Все услуги клиента',
            'url' => Url::to(['uu/account-tariff', 'serviceTypeId' => $serviceType ? $serviceType->id : null])
        ],
    ],
]) ?>

<?php
$viewParams = [
    'filterModel' => $filterModel,
    'isShowAddButton' => true,
];

if ($serviceType && $serviceType->id == ServiceType::ID_VOIP && $filterModel->client_account_id) {

    // персональная форма телефонии
    echo $this->render('_indexVoip', $viewParams + ['packageServiceTypeIds' => [ServiceType::ID_VOIP_PACKAGE]]);

} elseif ($serviceType && $serviceType->id == ServiceType::ID_TRUNK && $filterModel->client_account_id) {

    // персональная форма транка
    echo $this->render('_indexVoip', $viewParams + ['packageServiceTypeIds' => [ServiceType::ID_TRUNK_PACKAGE_ORIG, ServiceType::ID_TRUNK_PACKAGE_TERM]]);

} else {

    // типовая форма
    echo $this->render('_indexMain', $viewParams);

}
