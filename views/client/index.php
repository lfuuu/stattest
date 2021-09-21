<?php

use app\classes\grid\account\operator\operators\GenericFolder;
use app\classes\helpers\DependecyHelper;
use yii\caching\TagDependency;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Breadcrumbs;
use app\classes\Html;
use app\classes\grid\GridView;
use app\classes\grid\account\AccountGridFolder;

/** @var AccountGridFolder $activeFolder */
/** @var array $summary */

$urlParams = Yii::$app->request->get();

$this->registerJs("var gve_targetElementName = 'comment';\n", View::POS_HEAD);
$this->registerJs("var gve_targetUrl = 'client/save-comment';\n", View::POS_HEAD);
$this->registerJsFile('js/grid_view_edit.js', ['position' => yii\web\View::POS_END]);


if ($activeFolder->isGenericFolder()) {
    $this->registerJs(
        new \yii\web\JsExpression(
            '$("body").on("click", "#is_group_by_contract", function() {
                    var name = "Form' . $activeFolder->formName() . 'Data";
                    var data = Cookies.get(name);
                    data = data ? $.parseJSON(data) : {};
                    data["is_group_by_contract"] = $(this).is(\':checked\');
                    Cookies.set(name, data, { path: "/" });
                    $("#submitButtonFilter").click();
                });'
        )
    );
}

echo Html::formLabel($activeFolder->grid->getBusinessTitle());
echo Breadcrumbs::widget([
    'links' => [
        $activeFolder->grid->getBusinessTitle(),
        $activeFolder->grid->getBusinessProcessTitle(),
    ],
]);
?>

<div class="row">
    <div class="col-sm-12">
        <ul class="nav nav-pills">
            <?php foreach ($activeFolder->grid->getFolders() as $folder): ?>
                <?php $isActive = $activeFolder->getId() === $folder->getId(); ?>
                <li class="<?= ($isActive ? 'active' : '') . (!$folder->isGenericFolder() ? 'handed' : '') ?>">
                    <a href="<?= Url::toRoute([
                        'client/grid', 'folderId' => $folder->getId(), 'businessProcessId' => $urlParams['businessProcessId']
                    ]) ?>">
                        <?php
                        echo $folder->getName();

                        if ($isActive) {
                            $count = $folder->getCount();
                            $cacheKey = 'grid.folder.' . $activeFolder->getId() . '.count';
                            Yii::$app->cache->set($cacheKey, $count, DependecyHelper::DEFAULT_TIMELIFE, (new TagDependency(['tags' => DependecyHelper::TAG_GRID_FOLDER])));
                        } else {
                            $cacheKey = 'grid.folder.' . $folder->getId() . '.count';
                            $count = Yii::$app->cache->get($cacheKey);

                            if ($count === false) {
                                $count = $folder->getCount();
                                Yii::$app->cache->set($cacheKey, $count, DependecyHelper::DEFAULT_TIMELIFE, (new TagDependency(['tags' => DependecyHelper::TAG_GRID_FOLDER])));
                            }
                        }
                        if (is_numeric($count)) {
                            echo " ($count)";
                        }
                        ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <hr size="1"/>

        <?php
        $isGroupByContract = '';
        if ($activeFolder->isGenericFolder()) {
            $isGroupByContract = Html::beginTag('label') .
                'Группировать по договорам: ' .
                Html::checkbox(
                    $activeFolder->formName() . '[is_group_by_contract]',
                    $activeFolder->is_group_by_contract,
                    ['id' => 'is_group_by_contract']
                ) .
                Html::endTag('label');
        }

        $widgetConfig = [
            'dataProvider' => $dataProvider,
            'filterModel' => $activeFolder,
            'columns' => $activeFolder->getPreparedColumns(),
            'extraButtons' => $isGroupByContract,
            'toolbar' => [],
            'panel' => [
                'type' => GridView::TYPE_DEFAULT,
            ],
        ];

        if ($summary = $activeFolder->getSummary()) {
            $amountColumns = [['content' => Yii::t('common', 'Summary')]];
            $colspan = $activeFolder->getColspan();
            $amountColumns[0] += ['options' => ['colspan' => $colspan]];

            foreach ($summary as $key => $value) {
                $amountColumns[] = ['content' => $value];
            }

            $residualColspan = count($activeFolder->getColumns()) - count($summary) - $colspan;
            if ($residualColspan > 0) {
                $amountColumns[] = ['options' => ['colspan' => $residualColspan]];
            }

            $widgetConfig['afterHeader'] = [
                [
                    'options' => ['class' => \kartik\grid\GridView::TYPE_WARNING],
                    'columns' => $amountColumns,
                ]
            ];
        }
        echo GridView::widget($widgetConfig);
        ?>
    </div>
</div>