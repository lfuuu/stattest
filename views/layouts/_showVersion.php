<?php
/**
 * Показать ссылку для загрузки истории версий
 *
 * @var HistoryActiveRecord|HistoryActiveRecord[] $model
 * @var app\classes\BaseView $this
 */
use app\classes\Html;
use app\classes\model\HistoryActiveRecord;

?>
<div class="showVersionDiv" onclick="showVersion(this, <?= HistoryActiveRecord::getHistoryIds($model) ?>)">
    <?= Html::button('∨', ['class' => 'btn btn-default showVersionButton']); ?>
    <a>История версий</a>
</div>
