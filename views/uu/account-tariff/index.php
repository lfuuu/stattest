<?php
/**
 * Список универсальных услуг
 *
 * @var \app\classes\BaseView $this
 * @var AccountTariffFilter $filterModel
 */

use app\classes\uu\filter\AccountTariffFilter;
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

<?= $this->render(
    ($serviceType && $filterModel->client_account_id) ? '_indexVoip' : '_indexMain',
    [
        'filterModel' => $filterModel,
    ]
);
