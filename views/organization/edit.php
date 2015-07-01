<?php

use app\classes\Html;
use yii\bootstrap\Tabs;
use app\models\Organization;
use app\forms\organization\OrganizationForm;

/** @var $model OrganizationForm */

$items = [];
$history = Organization::find()
    ->byId($model->id)
    ->orderBy('actual_from asc')
    ->all();

foreach ($history as $record):
    if ($record->id == $model->id && $record->actual_from == $model->actual_from):
        $items[] = [
            'label'     => Yii::$app->formatter->asDate($record->actual_from, 'd MMM Y'),
            'active'    => true,
            'content'   => $this->render('form', ['model' => $model, 'mode' => 'edit']),
        ];
    else:
        $items[] = [
            'label' => Yii::$app->formatter->asDate($record->actual_from, 'd MMM Y'),
            'url'   => '/organization/edit/?id=' . $record->id . '&date=' . $record->actual_from,
        ];
    endif;
endforeach;

?>

<div style="float: right;">
    <?= Html::a(
        '<i class="glyphicon glyphicon-plus"></i> Добавить',
        ['duplicate', 'id' => $model->id, 'date' => $model->actual_from],
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