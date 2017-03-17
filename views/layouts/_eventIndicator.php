<?php

use app\models\EventQueue;
use app\models\EventQueueIndicator;

if (!isset($object)) {
    $object = null;
}

if (!isset($indicator)) {
    $indicator = null;
}

if (!isset($objectId)) {
    $objectId = 0;
}

if (!isset($section)) {
    $section = null;
}

if (!$object && (!$indicator || !($indicator instanceof EventQueueIndicator))) {
    return;
}

if ($object) {
    $indicator = EventQueueIndicator::find()
        ->where([
            'object' => $object,
            'object_id' => $objectId,
            'section' => $section
        ])->orderBy(['id' => SORT_DESC])
        ->one();
}

if (!$indicator) {
    return;
}

if (!$indicator->event) {
    return;
}

$colorClass = $titleInfo = "";

$titleInfo = ": " . \app\classes\Event::$names[$indicator->event->event] . " ";
switch($indicator->event->status) {
    case EventQueue::STATUS_PLAN:
        $colorClass = "warning";
        break;

    case EventQueue::STATUS_OK:
        $colorClass = "success";
        break;

    case EventQueue::STATUS_STOP:
    case EventQueue::STATUS_ERROR:
        $colorClass = "danger";
        $titleInfo .= htmlspecialchars($indicator->event->log_error);
        break;

    default:
        $colorClass = "warning";
}

?><div class="indicator indicator-<?=$colorClass?>" title="<?= EventQueue::$statuses[$indicator->event->status] . ($titleInfo ?: "")?>"></div>