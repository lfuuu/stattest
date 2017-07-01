<?php

use app\classes\Html;
use app\classes\monitoring\MonitoringInterface;
use yii\bootstrap\Tabs;

echo Html::formLabel('Мониторинг ключевых событий');

$tabs = [];
/** @var array $monitors */
/** @var MonitoringInterface $monitor */
foreach ($monitors as $monitor) {
    $tab = [
        'label' => $monitor->title,
    ];

    if ($monitor->key == $current->key) {
        $tab['active'] = true;
        $tab['content'] = $this->render('_indexGrid', ['monitor' => $monitor]);
    } else {
        $tab['url'] = ['/monitoring', 'monitor' => $monitor->key];
    }

    $tabs[] = $tab;
}
?>

<div class="well" style="overflow-x: auto;">
    <?= Tabs::widget([
        'id' => 'tabs-monitoring',
        'items' => $tabs,
    ]);
    ?>
</div>
