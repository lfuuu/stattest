<?php

use yii\widgets\Breadcrumbs;
use app\classes\Html;
use app\classes\grid\GridView;
use app\classes\grid\account\AccountGridFolder;

/** @var AccountGridFolder $activeFolder */
/** @var array $summary */

$urlParams = Yii::$app->request->get();

$this->registerJsFile('js/client_commentary.js',  ['position' => yii\web\View::POS_END]);

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
                <li class="<?= $isActive ? 'active' : '' ?>">
                    <a href="<?= \yii\helpers\Url::toRoute([
                            'client/grid', 'folderId' => $folder->getId(), 'businessProcessId' => $urlParams['businessProcessId']
                    ]) ?>">
                        <?php
                            echo $folder->getName();

                            if ($isActive) {
                                $count = $folder->getCount();
                                $cacheKey = 'grid.folder.' . $activeFolder->getId() . '.count';
                                Yii::$app->cache->set($cacheKey, $count,null,(new \yii\caching\TagDependency(['tags' => \app\classes\helpers\DependecyHelper::TAG_GRID_FOLDER])));
                            } else {
                                $cacheKey = 'grid.folder.' . $folder->getId() . '.count';
                                $count = Yii::$app->cache->get($cacheKey);

                                if ($count === false) {
                                    $count = $folder->getCount();
                                    Yii::$app->cache->set($cacheKey, $count,null,(new \yii\caching\TagDependency(['tags' => \app\classes\helpers\DependecyHelper::TAG_GRID_FOLDER])));
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

        <hr size="1" />

        <?php
            $widgetConfig= [
                'dataProvider' => $dataProvider,
                'filterModel' => $activeFolder,
                'columns' => $activeFolder->getPreparedColumns(),
                'toolbar' => [],
                'panel'=>[
                    'type' => GridView::TYPE_DEFAULT,
                ],
            ];

            if ($summary = $activeFolder->getSummary()) {
                $amountColumns = [['content' => Yii::t('common', 'Summary')]];
                $colspan = $activeFolder->getColspan();
                $amountColumns[0] += ['options' => ['colspan' => $colspan]];

                foreach($summary as $key => $value) {
                    $amountColumns[] = ['content' => $value];
                }

                $residualColspan = count($activeFolder->getColumns()) - count($summary) - $colspan;
                if ($residualColspan > 0){
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