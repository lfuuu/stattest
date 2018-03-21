<?php
/**
 * Список статистики по sms
 *
 * @var \app\classes\BaseView $this
 * @var MttRawFilter $filterModel
 */

use app\models\filter\mtt_raw\MttRawFilter;
?>

<?php
$indexView = $filterModel->group_time !== '' ?
    '_indexGroupedGrid' : '_indexDefaultGrid';

echo $this->render($indexView, [
    'filterModel' => $filterModel
]);
?>