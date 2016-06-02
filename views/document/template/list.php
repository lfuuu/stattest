<?php

use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\widgets\JQTree\JQTree;
use app\classes\Html;
use app\models\document\DocumentFolder;

/** @var $dataProvider ActiveDataProvider */
echo Html::formLabel('Управление шаблонами документов');

echo Breadcrumbs::widget([
    'links' => ['Управление шаблонами документов',],
]);
?>

<div class="well">
    <div class="pull-right">
        <?= $this->render('//layouts/_buttonCreate', [
                'name' => 'Создать раздел',
                'url' => Url::toRoute(['document/folder/edit']),
            ])
        ?>

        <?= $this->render('//layouts/_buttonCreate', [
                'name' => 'Добавить документ',
                'url' => Url::toRoute(['document/template/edit']),
            ])
        ?>
    </div>
    <div style="clear: both;"></div><br />

    <?php
    echo JQTree::widget([
        'data' => new DocumentFolder,
        'htmlOptions' => [
            'id' => 'treeview',
            'class' => 'jqtree'
        ],
    ]);
    ?>
</div>