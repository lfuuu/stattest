<?php
/**
 * Список универсальных услуг
 *
 * @var \yii\web\View $this
 * @var AccountTariffFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\RegionColumn;
use app\classes\grid\column\universal\TariffPeriodColumn;
use app\classes\Html;
use app\classes\uu\filter\AccountTariffFilter;
use app\classes\uu\model\AccountTariff;
use app\classes\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$serviceType = $filterModel->getServiceType();
?>
<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal services'),
        ['label' => $this->title = ($serviceType ? $serviceType->name : ''), 'url' => Url::to(['uu/accounttariff', 'serviceTypeId' => $serviceType ? $serviceType->id : ''])],
    ],
]) ?>

    <p>
        <?= Html::a(
            Yii::t('common', 'Create'),
            AccountTariff::getUrlNew($serviceType ? $serviceType->id : ''),
            ['class' => 'btn btn-success']
        ) ?>
    </p>

<?= GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => [
        [
            'label' => Yii::t('tariff', 'Universal services'),
            'attribute' => 'tariff_period_id',
            'class' => TariffPeriodColumn::className(),
            'serviceTypeId' => $serviceType->id,
            'format' => 'html',
            'value' => function (AccountTariff $accountTariff) {
                return Html::a(
                    Html::encode($accountTariff->getName(false)),
                    $accountTariff->getUrl()
                );
            }
        ],
        [
            'attribute' => 'client_account_id',
            'class' => IntegerColumn::className(),
            'format' => 'html',
            'value' => function (AccountTariff $accountTariff) {
                return Html::a(
                    Html::encode($accountTariff->clientAccount->client),
                    ['/client/view', 'id' => $accountTariff->client_account_id]
                );
            }
        ],
        [
            'attribute' => 'region_id',
            'class' => RegionColumn::className(),
        ],
    ],
]) ?>