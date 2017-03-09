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

if (!$object && (!$indicator || !($indicator instanceof EventQueueIndicator))) {
    return;
}

if ($object) {
    $indicator = EventQueueIndicator::findOne(['object' => $object, 'object_id' => $objectId]);
}

if (!$indicator) {
    return;
}

$color = $titleInfo = "";

$titleInfo = ": " . \app\classes\Event::$names[$indicator->event->event] . " ";
switch($indicator->event->status) {
    case EventQueue::STATUS_PLAN:
        $color = "#f0ad4e"; // warning
        break;

    case EventQueue::STATUS_OK:
        $color = "#5cb85c"; // success
        break;

    case EventQueue::STATUS_STOP:
    case EventQueue::STATUS_ERROR:
        $color = "#d9534f"; // danger
        $titleInfo .= htmlspecialchars($indicator->event->log_error);
        break;
}

?>

<div style="height: 1rem; width: 1rem; border-radius: 50%; background-color: <?=$color?>" title="<?= EventQueue::$statuses[$indicator->event->status] . ($titleInfo ?: "")?>"></div>