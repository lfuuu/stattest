<?php
/**
 * Счет-фактура
 *
 * @var \yii\web\View $this
 * @var ClientAccount $clientAccount
 * @var AccountEntry[] $accountEntries
 */

use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use app\classes\uu\model\AccountEntry;
use app\models\ClientAccount;

echo Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        $this->title = Yii::t('tariff', 'Invoice'),
    ],
]);

$attributeLabels = (new AccountEntry)->attributeLabels();
?>

<div class="row">
    <div class="col-sm-12">
        <div class="pull-right">
            <?= $this->render('//layouts/_link', [
                'text' => 'Печать',
                'url' => Url::toRoute(['/uu/invoice/view' , 'renderMode' => 'print']),
                'glyphicon' => 'glyphicon glyphicon-print',
                'params' => [
                    'class' => 'btn btn-primary',
                    'target' => '_blank',
                ],
            ]) ?>
            <?= $this->render('//layouts/_link', [
                'text' => 'Скачать в PDF',
                'url' => Url::toRoute(['/uu/invoice/view' , 'renderMode' => 'pdf']),
                'glyphicon' => 'glyphicon glyphicon-download-alt',
                'params' => [
                    'class' => 'btn btn-success',
                ],
            ]) ?>
            <?= $this->render('//layouts/_link', [
                'text' => 'Скачать в Word',
                'url' => Url::toRoute(['/uu/invoice/view' , 'renderMode' => 'mhtml']),
                'glyphicon' => 'glyphicon glyphicon-download-alt',
                'params' => [
                    'class' => 'btn btn-success',
                ],
            ]) ?>
        </div>
    </div>
</div>
<br />

<?= $this->render('invoice', [
    'accountEntries' => $accountEntries,
    'clientAccount' => $clientAccount,
]) ?>