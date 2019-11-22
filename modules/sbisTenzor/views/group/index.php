<?php

use app\classes\Html;
use yii\data\ActiveDataProvider;
use yii\widgets\Breadcrumbs;
use app\classes\grid\GridView;

/**
 * @var ActiveDataProvider $dataProvider
 * @var \app\classes\BaseView $baseView
 * @var string $title
 */

$baseView = $this;

$this->title = $title;

echo Html::formLabel($title);
echo Breadcrumbs::widget([
    'links' => [
        'СБИС',
        ['label' => $this->title = $title,],
    ],
]);

?>

<?php

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'isFilterButton' => false,
    'floatHeader' => false,
]);