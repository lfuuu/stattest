<?php
/**
 * Пакеты
 *
 * @var app\classes\BaseView $this
 * @var PackageFilter $filterModel
 */

use app\classes\grid\column\universal\TariffColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\uu\model\ServiceType;
use app\modules\nnp\filter\PackageFilter;
use app\modules\nnp\models\Package;
use app\modules\nnp\models\PackageMinute;
use app\modules\nnp\models\PackagePrice;
use app\modules\nnp\models\PackagePricelist;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Пакеты') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title, 'url' => '/nnp/package/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'attribute' => 'tariff_id',
        'class' => TariffColumn::className(),
        'serviceTypeId' => ServiceType::ID_VOIP_PACKAGE,
        'filterOptions' => [
            'class' => 'nnp-package-tariff-column',
        ],
    ],

    [
        'label' => 'Предоплаченные минуты',
        'format' => 'html',
        'value' => function (Package $package) {
            $packageMinutes = $package->packageMinutes;
            $echoArray = array_map(function (PackageMinute $packageMinute) {
                ob_start();
                $destination = $packageMinute->destination;
                ?>
                <div class="row">
                    <div class="col-sm-10"><?= Html::a($destination->name, $destination->getUrl()) ?></div>
                    <div class="col-sm-2"><?= $packageMinute->minute ?></div>
                </div>
                <?php
                return ob_get_clean();
            }, $packageMinutes);
            return implode(PHP_EOL, $echoArray);
        }
    ],

    [
        'label' => 'Цена по направлениям',
        'format' => 'html',
        'value' => function (Package $package) {
            $packagePrices = $package->packagePrices;
            $echoArray = array_map(function (PackagePrice $packagePrice) {
                $destination = $packagePrice->destination;
                ob_start();
                ?>
                <div class="row">
                    <div class="col-sm-10"><?= Html::a($destination->name, $destination->getUrl()) ?></div>
                    <div class="col-sm-2"><?= $packagePrice->price ?></div>
                </div>
                <?php
                return ob_get_clean();
            }, $packagePrices);
            return implode(PHP_EOL, $echoArray);
        }
    ],

    [
        'label' => 'Прайслист с МГП',
        'format' => 'html',
        'value' => function (Package $package) {
            $packagePricelists = $package->packagePricelists;
            $echoArray = array_map(function (PackagePricelist $packagePricelist) {
                ob_start();
                $pricelist = $packagePricelist->pricelist;
                ?>
                <div class="row">
                    <div class="col-sm-12"><?= Html::a($pricelist->name, $pricelist->getUrl()) ?></div>
                </div>
                <?php
                return ob_get_clean();
            }, $packagePricelists);
            return implode(PHP_EOL, $echoArray);
        }
    ],

    [
        'class' => ActionColumn::className(),
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, Package $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, Package $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionDrop', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/nnp/package/new/']),
    'columns' => $columns,
]);