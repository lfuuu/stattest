<?php
/**
 * Список универсальных услуг
 *
 * @var \app\classes\BaseView $this
 * @var AccountTariffFilter $filterModel
 * @var bool $isPersonalForm
 */

use app\modules\uu\filter\AccountTariffFilter;
use app\modules\uu\models\AccountTariff;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$serviceType = $filterModel->getServiceType();

?>
<?= Breadcrumbs::widget([
    'links' => [
        [
            'label' => Yii::t('tariff', 'Universal services') .
                $this->render('//layouts/_helpConfluence', AccountTariff::getHelpConfluence()),
            'encode' => false,
        ],

        [
            'label' => $this->title = $serviceType ? $serviceType->name : 'Все услуги клиента',
            'url' => Url::to(['/uu/account-tariff', 'serviceTypeId' => $serviceType ? $serviceType->id : null])
        ],
        [
            'label' => $serviceType ? $this->render('//layouts/_helpConfluence', $serviceType->getHelpConfluence()) : '',
            'encode' => false,
        ],
    ],
]) ?>

<?= $this->render(
    ($serviceType && $isPersonalForm) ? '_indexVoip' : '_indexMain',
    [
        'filterModel' => $filterModel,
    ]
);
