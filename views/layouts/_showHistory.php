<?php
/**
 * Показать ссылку для загрузки истории изменений
 *
 * @var HistoryActiveRecord|HistoryActiveRecord[] $model Одна или массив моделей, которые надо искать
 * @var array $deleteModel list($model, $fieldName, $fieldValue). Исходная модель (можно свежесозданную и несохраненную), поле и значение, которые надо искать среди удаленных моделей
 * @var app\classes\BaseView $this
 */

use app\classes\Html;
use app\classes\model\HistoryActiveRecord;

if (!isset($deleteModel)) {
    $deleteModel = [];
}

if (!Yii::$app->user->can('logs.history_changes')) {
    return '';
}
?>
<div class="showHistoryDiv"
     onclick="showHistory(this, <?= HistoryActiveRecord::getHistoryIds($model, $deleteModel) ?>)">
    <?= Html::button('∨', ['class' => 'btn btn-default showHistoryButton']); ?>
    <a>История изменений</a>
</div>
