<?php
/**
 * Счет-фактура
 *
 * @var \yii\web\View $this
 * @var int $clientAccountId
 * @var AccountEntry[] $accountEntries
 */

use app\classes\uu\model\AccountEntry;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        $this->title = Yii::t('tariff', 'Invoice'),
    ],
]) ?>

<?php
if (!$clientAccountId) {
    Yii::$app->session->setFlash('error', Yii::t('tariff', 'You should {a_start}select a client first{a_finish}', ['a_start' => '<a href="/">', 'a_finish' => '</a>']));
    return;
}
?>

<?php $attributeLabels = (new AccountEntry)->attributeLabels() ?>
<div class="row">
    <div class="col-sm-4"><label><?= $attributeLabels['account_tariff_id'] ?></label></div>
    <div class="col-sm-2"><label><?= $attributeLabels['price_without_vat'] ?></label></div>
    <div class="col-sm-2"><label><?= $attributeLabels['vat_rate'] ?></label></div>
    <div class="col-sm-2"><label><?= $attributeLabels['vat'] ?></label></div>
    <div class="col-sm-2"><label><?= $attributeLabels['price_with_vat'] ?></label></div>
</div>

<?php foreach ($accountEntries as $accountEntry): ?>
    <div class="row">
        <div class="col-sm-4"><?= $accountEntry->accountTariff->getName(false) ?></div>
        <div class="col-sm-2"><?= sprintf('%.2f', $accountEntry->price_without_vat) ?></div>
        <div class="col-sm-2"><?= sprintf('%.2f', $accountEntry->vat_rate) ?>%</div>
        <div class="col-sm-2"><?= sprintf('%.2f', $accountEntry->vat) ?></div>
        <div class="col-sm-2"><?= sprintf('%.2f', $accountEntry->price_with_vat) ?></div>
    </div>
<?php endforeach ?>

