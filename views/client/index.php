<?php

use yii\widgets\Breadcrumbs;
use app\classes\Html;
use app\classes\grid\GridView;
use app\classes\grid\account\AccountGridFolder;

/** @var AccountGridFolder $activeFolder */

$urlParams = Yii::$app->request->get();

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
                <?php
                $params =
                    array_merge(
                        array_intersect_key($urlParams, get_class_vars(get_class($folder))),
                        ['client/grid', 'folderId' => $folder->getId(), 'businessProcessId' => $urlParams['businessProcessId']]
                    );
                ?>
                <li class="<?= $activeFolder->getId() == $folder->getId() ? 'active' : '' ?>">
                    <a href="<?= \yii\helpers\Url::toRoute($params) ?>">
                        <?php
                            echo $folder->getName();
                            $count = $folder->getCount();
                            if ($count !== null) {
                                echo " ($count)";
                            }
                        ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <hr size="1" />

        <?php
        echo GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $activeFolder,
                'columns' => $activeFolder->getPreparedColumns(),
                'toolbar' => [],
                'panel'=>[
                    'type' => GridView::TYPE_DEFAULT,
                ],
            ]
        );
        ?>
    </div>
</div>