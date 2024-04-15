<?php
/**
 * Просмотр закрывающих документов перед печатью.
 * (для клиентов, которые оплачивают доставку).
 */

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

echo app\classes\Html::formLabel($this->title);
echo Breadcrumbs::widget([
    'links' => [
        'Бухгалтерия',
        ['label' => 'Печать закрывающих документов', 'url' => '/report/closing-statements-print/?organization_id=' . $organizationId],
        ['label' => $this->title],
    ],
]);

?>

<table>
<?php foreach($pdfList as $pdfItem): ?>

<tr>
    <td><a target="_blank" href="<?= $pdfItem['link'] ?>">⬇️ <?= $pdfItem['doc_type'] ?> <?= $pdfItem['name'] ?></a>&nbsp;&nbsp;</td>
    <td><?= $pdfItem['client_name'] ?></td>
</tr>

<?php endforeach; ?>
</table>

