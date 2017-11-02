<?php
/**
 * Показать ссылку для загрузки истории изменений
 *
 * @var ActiveRecord|ActiveRecord[] $model Одна или массив моделей, которые надо искать
 * @var array $parentModel list($model, $fieldName, $fieldValue). Исходная модель (можно свежесозданную и несохраненную), поле и значение, которые надо искать среди удаленных моделей
 * @var string $title
 * @var string $idField
 * @var app\classes\BaseView $this
 */

use app\classes\Html;
use app\classes\model\ActiveRecord;

if (!Yii::$app->user->can('logs.history_changes')) {
    return '';
}

if (!isset($model)) {
    $model = [];
}

if (!isset($parentModel)) {
    $parentModel = [];
}

if (!isset($idField)) {
    $idField = 'id';
}

if (!isset($title)) {
    $title = 'История изменений';
}

?>
<div class="showHistoryDiv"
     onclick="showHistory(this, <?= ActiveRecord::getHistoryIds($model, $parentModel, $idField) ?>)">
    <?= Html::button('∨', ['class' => 'btn btn-default showHistoryButton']); ?>
    <a><?= $title ?></a>
</div>
