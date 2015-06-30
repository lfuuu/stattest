<?php

use app\classes\Html;
use yii\bootstrap\Tabs;
use app\models\Organization;
use app\forms\organization\OrganizationForm;

/** @var $model OrganizationForm */

$items = [];
$history = Organization::find()
    ->where([
        'firma' => $model->firma
    ])
    ->orderBy('actual_from asc')
    ->all();

foreach ($history as $record):
    if ($record->id == $model->id):
        $items[] = [
            'label'     => Yii::$app->formatter->asDate($record->actual_from, 'd MMM Y'),
            'active'    => true,
            'content'   => $this->render('form', ['model' => $model]),
        ];
    else:
        $items[] = [
            'label' => Yii::$app->formatter->asDate($record->actual_from, 'd MMM Y'),
            'url'   => '/organization/edit/?id=' . $record->id,
        ];
    endif;
endforeach;

?>

<div style="float: right;">
    <?= Html::a(
        '<i class="glyphicon glyphicon-plus"></i> Добавить',
        ['duplicate', 'firma' => $model->firma],
        [
            'data-pjax' => 0,
            'class' => 'btn btn-success btn-sm form-lnk',
        ]
    );
    ?>
</div>

<?= Tabs::widget([
    'id' => 'tabs-' . $model->id,
    'items' => $items
]);
?>